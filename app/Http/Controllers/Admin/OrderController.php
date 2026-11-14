<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product', 'variant'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        // 🔒 حماية مالية صارمة: منع تعديل الأوردر لو وصل لحالة نهائية (تم التسليم أو ملغي)
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return back()->with('error', 'عذراً، هذا الطلب وصل لحالة نهائية ولا يمكن تغيير حالته لحماية السجل المالي!');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
            'cancellation_reason' => 'required_if:status,cancelled,returned|nullable|string|max:500',
        ], [
            'cancellation_reason.required_if' => 'يجب كتابة سبب الإلغاء أو الإرجاع نظراً لتغيير حالة الطلب.',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $newStatus = $request->status;
            $quantityToRestore = $order->stock;

            if (in_array($newStatus, ['cancelled', 'returned']) && !in_array($oldStatus, ['cancelled', 'returned'])) {
                if ($order->product_variant_id) {
                    $variant = ProductVariant::find($order->product_variant_id);
                    if ($variant) {
                        $variant->stock += $quantityToRestore;
                        $variant->save();
                    }
                } else {
                    $product = Product::find($order->product_id);
                    if ($product) {
                        $product->stock += $quantityToRestore;
                        $product->save();
                    }
                }
            }

            $order->status = $newStatus;
            
            if (in_array($newStatus, ['cancelled', 'returned'])) {
                $order->cancellation_reason = $request->cancellation_reason;
            } else {
                $order->cancellation_reason = null;
            }
            
            $order->save();

            // 🚀 إيداع الأرباح في جدول Wallets و WalletTransactions عند التسليم لأول مرة
            if ($newStatus == 'delivered' && $oldStatus != 'delivered' && !$order->is_paid_to_wallet) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $order->user_id],
                    ['balance' => 0]
                );

                $wallet->balance += $order->profit;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'order_id' => $order->id,
                    'type' => 'credit',
                    'amount' => $order->profit,
                    'description' => "أرباح تسليم الطلب رقم #{$order->id}"
                ]);

                $order->is_paid_to_wallet = true;
                $order->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'تم تحديث حالة الطلب وإدارة المخزون بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->init()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage()); // Fixed potential syntax or just let it be back()->with...
        }
    }

    // 🛠️ دالة تحديث وعرض سبب الإلغاء/الإرجاع من المودال المنفصل
    public function updateReason(Request $request, Order $order)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ], [
            'cancellation_reason.required' => 'يجب كتابة السبب.',
        ]);

        $order->cancellation_reason = $request->cancellation_reason;
        $order->save();

        return redirect()->back()->with('success', 'تم تحديث سبب الطلب بنجاح!');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح.');
    }
}