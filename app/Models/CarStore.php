<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;





class CarStore extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'is_open',
        'is_full',
        'address',
        'phone_number',
        'cs_name',
        'city_id'
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
    
    // menghubungkan ke store sevice
    public function storeServices(): HasMany
    {
        return $this->hasMany(StoreService::class,'car_store_id');
    }

    // menghubungkan ke table storephoto
    public function photos(): HasMany
    {
        return $this->hasMany(StorePhoto::class, 'car_store_id');
    }

    // menghubungkan ke tabel kota
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    // menghubungkan ke tabel
    public function storeDetails(): BelongsTo
    {
        return $this->belongsTo(BookingTransaction::class, 'car_store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
