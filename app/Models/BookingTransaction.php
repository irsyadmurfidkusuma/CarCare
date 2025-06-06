<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;




class BookingTransaction extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'name',
        'trx_id',
        'phone_number',
        'is_paid',
        'proof',
        'total_amount',
        'car_store_id',
        'car_service_id',
        'started_at',
        'time_at'
    ];

    protected $casts = [
        'started_at' => 'date'
    ];

    public static function generateUniqueTrxId() {
        $prefix = 'CC';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('trx_id', $randomString)->exists());

        return $randomString;
    }

    public function service_details(): BelongsTo
    {
        return $this->belongsTo(CarService::class, 'car_service_id');
    }

    public function store_details(): BelongsTo
    {
        return $this->belongsTo(CarStore::class, 'car_store_id');
    }
}
