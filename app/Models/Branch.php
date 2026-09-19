<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'latitude',
        'longitude',
        'base_delivery_fee',
        'delivery_fee_per_km',
        'max_delivery_distance_km',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'base_delivery_fee' => 'decimal:2',
        'delivery_fee_per_km' => 'decimal:2',
        'max_delivery_distance_km' => 'decimal:2',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
