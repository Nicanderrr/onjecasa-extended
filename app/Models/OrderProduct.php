<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected $fillable = [
        'branch_id',
        'order_id',
        'user_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'total',
        'size',
        'color',
        'subtotal',
        'username',
        'phone',
        'address',
        'fulfillment_method',
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
        'status',
    ];

    /**
     * Get the user that owns the order product.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the product associated with this order product.
     */
    public function product()
    {
        return $this->belongsTo(ProductPage::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
