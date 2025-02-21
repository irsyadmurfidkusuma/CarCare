<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\CarService;

class FrontController extends Controller
{
    public function index() {
        $cities = City::all();
        $services = CarService::withCount(['storeServices'])->get();
        return view('front.index' , compact('cities', 'services'));
    }
}
