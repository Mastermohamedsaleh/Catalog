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
        static::updated(function ($order) {
            // لو الأدمن غير حالة الأوردر إلى delivered ولم يتم تحويل الربح للمحفظة من قبل
            if ($order->status === 'delivered' && !$order->is_paid_to_wallet) {
                
                $merchant = $order->user;
                
                if ($merchant) {
                    $qty = $order->stock ?? 1;
                    
                    // تصحيح علامات الضرب (*) المفقودة
                    $totalCost = $order->product_price * $qty;
                    $totalSelling = $order->selling_price * $qty;
                    $netProfit = $totalSelling - $totalCost; // تصحيح علامة الطرح (-)

                    if ($netProfit > 0) {
                        // تحديث حقل الربح في الأوردر للتأكيد
                        $order->updateQuietly(['profit' => $netProfit]);

                        // إضافة الربح لمحفظة التاجر بأمان حسب اسم العمود المتاح في جدول الـ users
                        if (Schema::hasColumn('users', 'wallet')) {
                            $merchant->increment('wallet', $netProfit);
                        } elseif (Schema::hasColumn('users', 'wallet_balance')) {
                            $merchant->increment('wallet_balance', $netProfit);
                        }
                    }

                    // قفل الأوردر عشان الفلوس متمش إيداعها مرتين
                    $order->updateQuietly(['is_paid_to_wallet' => true]);
                }
            }
        });
    }
}