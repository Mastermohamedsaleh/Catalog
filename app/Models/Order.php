<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model

{


protected $fillable = [
        'user_id',
        'product_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'product_price',
        'selling_price',
        'profit',
        'stock',
        'status',
        'is_paid_to_wallet',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }




}
