<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'total',
        'fulfillment_method',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_distance_km',
        'delivery_fee',
        'delivery_status',
        'assigned_staff_user_id',
        'assigned_staff_name',
        'confirmed_at',
        'preparing_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
        'status_updated_by',
        'status_updated_by_name',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
        'delivery_distance_km' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'preparing_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all order line items linked to this order.
     */
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }
}
