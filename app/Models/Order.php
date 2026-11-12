<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_id', // أضفناها هنا لضمان حفظها في الـ Mass Assignment
        'customer_name',
        'customer_phone',
        'customer_address',
        'product_price',
        'selling_price',
        'cancellation_reason',
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

    // تم توحيد اسم العلاقة لتقبل الـ productVariant
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }



    // علاقة بديلة احتياطية لو تم استدعاؤها باسم variant مباشرة
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

protected static function booted()
    {
        static::saved(function ($variant) {
            if ($variant->product) {
                $totalStock = $variant->product->variants()->sum('stock');
                $variant->product->update(['stock' => $totalStock]);
            }
        });

        static::deleted(function ($variant) {
            if ($variant->product) {
                $totalStock = $variant->product->variants()->sum('stock');
                $variant->product->update(['stock' => $totalStock]);
            }
        });
    }
}