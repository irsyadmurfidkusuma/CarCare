<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class StoreService extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'car_service_id',
        'car_store_id'
    ];


    public function store(): BelongsTo
    {
        return $this->belongsTo(CarStore::class, 'car_store_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(CarService::class, 'car_service_id');
    }

    // public function bookingService(): BelongsTo
    // {
    //     return $this->belongsTo(CarSevice::class, 'car_service_id');
    // }
}
