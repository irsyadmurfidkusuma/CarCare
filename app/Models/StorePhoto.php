<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class StorePhoto extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'photo',
        'car_store_id'
    ];
}
