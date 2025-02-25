<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\BookingTransaction;
use App\Models\CarService;
use App\Models\CarStore;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StoreBookingPaymentRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class FrontController extends Controller
{
    // Menampilkan halaman awal
    public function index() {
        $cities = City::all();
        $services = CarService::withCount(['storeServices'])->get();
        return view('front.index' , compact('cities', 'services'));
    }


    // Menampilkan halaman daftar toko yang tersedia sesuai dengan service dan kota
    public function search(Request $request) {
        $cityId = $request->input('city_id');
        $serviceTypeId = $request->input('service_type');

        $carService = CarService::where('id', $serviceTypeId)->first();
        if(!$carService) {
            return redirect()->back()->with('error', 'Service type not found!');
        }

        $stores = CarStore::whereHas('storeServices', function($query) use ($carService) {
            $query->where('car_service_id', $carService->id);
        })->where('city_id',$cityId)->get();

        $city = City::find($cityId);

        session()->put('serviceTypeId', $request->input('service_type'));

        return view('front.stores', [
            'stores'=>$stores,
            'carService'=>$carService,
            'cityName'=>$city ? $city->name : 'Unknown City'
        ]);

    }

    // Menampilkan halaman detail store
    public function details(CarStore $carStore) {
        $serviceTypeId = session()->get('serviceTypeId');
        $carService = CarService::where('id', $serviceTypeId)->first();
        return view('front.details', compact('carStore', 'carService'));
    }

    // Menampilkan halaman booking
    public function booking(CarStore $carStore) {
        session()->put('carStoreId', $carStore->id);

        $serviceTypeId = session()->get('serviceTypeId');
        $service = CarService::where('id', $serviceTypeId)->first();

        return view('front.booking', compact('carStore', 'service'));
    }


    // menyimpan data booking ke dalam session
    public function booking_store(StoreBookingRequest $request) {
        $customerName = $request->input('name');
        $customerPhoneNumber = $request->input('phone_number');
        $customerTimeAt = $request->input('time_at');

        session()->put('customerName', $customerName);
        session()->put('customerPhoneNumber', $customerPhoneNumber);
        session()->put('customerTimeAt', $customerTimeAt);

        $serviceTypeId = session()->get('serviceTypeId');
        $carStoreId = session()->get('carStoreId');

        return redirect()->route('front.booking.payment',[$carStoreId, $serviceTypeId]);

    }

    // menampilkan halaman payment
    public function booking_payment(CarStore $carStore, CarService $carService) {
        $ppn = 0.11;
        $totalPpn = $carService->price * $ppn;
        $bookingFee = 5000;
        $totalGrandTotal = $totalPpn + $bookingFee + $carService->price;
        // dd(number_format($totalGrandTotal, 0,',','.'));

        session()->put('total_amount', $totalGrandTotal);
        return view('front.payment', compact('carService', 'carStore', 'totalPpn', 'bookingFee', 'totalGrandTotal'));
    }

    // Menyimpan data transaksi
    public function booking_payment_store(StoreBookingPaymentRequest $request) {
        // menyimpan data ke dalam variabel yang diambil dari session dan form
        $customerName = session()->get('customerName');
        $customerPhoneNumber = session()->get('customerPhoneNumber');
        $totalAmount = session()->get('total_amount');
        $customerTimeAt = session()->get('customerTimeAt');
        $serviceTypeId = session()->get('serviceTypeId');
        $carStoreId = session()->get('carStoreId');

        $bookingTransactionId = null;

        // Closure based database transaction
        DB::transaction(function () use ($request, $totalAmount, $customerName, $customerPhoneNumber, $customerTimeAt, $serviceTypeId, $carStoreId, &$bookingTransactionId) {
            $validated = $request->validated();

            if($request->hasfile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $validated['name'] = $customerName;
            $validated['total_amount'] = $totalAmount;
            $validated['phone_number'] = $customerPhoneNumber;
            $validated['started_at'] = Carbon::tomorrow()->format('Y-m-d');
            $validated['time_at'] = $customerTimeAt;
            $validated['car_service_id'] = $serviceTypeId;
            $validated['car_store_id'] = $carStoreId;
            $validated['is_paid'] = false;
            $validated['trx_id'] = BookingTransaction::generateUniqueTrxId();

            $newBooking = BookingTransaction::create($validated);

            $bookingTransactionId = $newBooking->id;
            
        });

        return redirect()->route('front.success.booking', $bookingTransactionId);
    }

    // menampilkan halaman success booking
    public function success_booking(BookingTransaction $bookingTransaction) {
        return view('front.success_booking', compact('bookingTransaction'));
    }

}
