<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;



    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'size',
        'color',
        'notification',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the cart item.
     */
    public function product()
    {
        return $this->belongsTo(ProductPage::class);
    }

    // // Check if notification has been sent
    // public function isNotified()
    // {
    //     return $this->notification;
    // }
}


