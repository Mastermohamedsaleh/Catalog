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



    protected static function booted()
    {
        static::updated(function ($order) {
            // لو الأدمن غير حالة الأوردر إلى delivered ولم يتم تحويل الربح للمحفظة من قبل
            if ($order->status === 'delivered' && !$order->is_paid_to_wallet) {
                
                $merchant = $order->user;
                
                if ($merchant) {
                    // حساب الربح بدقة: (سعر البيع للعميل - سعر المنتج الأساسي في الجملة) * الكمية
                    $totalCost = $order->product_price * ($order->quantity ?? 1);
                    $totalSelling = $order->selling_price * ($order->quantity ?? 1);
                    $netProfit = $totalSelling - $totalCost;

                    if ($netProfit > 0) {
                        // تحديث حقل الربح في الأوردر للتأكيد
                        $order->updateQuietly(['profit' => $netProfit]);

                        // إضافة الربح لمحفظة التاجر
                        $merchant->increment('wallet', $netProfit);
                    }

                    // قفل الأوردر عشان الفلوس متمش إيداعها مرتين
                    $order->updateQuietly(['is_paid_to_wallet' => true]);
                }
            }
        });
    }




}
