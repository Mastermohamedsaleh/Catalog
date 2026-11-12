<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;

class WalletController extends Controller
{
    public function index()
    {
        $merchant = Auth::user();

        // 1. حساب الرصيد المتاح بدقة من الأوردرات المسلمة (delivered)
        $availableBalance = Order::where('user_id', $merchant->id)
            ->where('status', 'delivered')
            ->sum('profit');

        // التحقق من حقل المحفظة في جدول الـ users لضمان الأمان
        if (isset($merchant->wallet) && $merchant->wallet > 0) {
            $availableBalance = max($merchant->wallet, $availableBalance);
        } elseif (isset($merchant->wallet_balance) && $merchant->wallet_balance > 0) {
            $availableBalance = max($merchant->wallet_balance, $availableBalance);
        }

        // 2. الأرباح المعلقة (Pending, Processing, Shipped)
        $pendingProfit = Order::where('user_id', $merchant->id)
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->sum('profit');

        // 3. عدد الأوردرات المسلمة بنجاح
        $deliveredCount = Order::where('user_id', $merchant->id)
            ->where('status', 'delivered')
            ->count();

        // 4. عدد الأوردرات الملغية أو المرتجعة
        $lostCount = Order::where('user_id', $merchant->id)
            ->whereIn('status', ['cancelled', 'returned'])
            ->count();

        // 5. جلب سجل المعاملات والأوردرات الخاصة بالتاجر
        $orders = Order::where('user_id', $merchant->id)
            ->with('product')
            ->latest()
            ->get();

        // حقن الرصيد المتاح جوه كائن الـ merchant عشان يظهر صح في الـ view
        $merchant->calculated_wallet = $availableBalance;

        return view('frontend.merchant-wallet', compact(
            'merchant', 
            'pendingProfit', 
            'deliveredCount', 
            'lostCount', 
            'orders'
        ));
    }

    /**
     * دالة طلب سحب الأرباح وتصفير الخزنة للتاجر (فودافون كاش)
     */
    public function withdraw(Request $request)
    {
        $merchant = Auth::user();

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^01[0125][0-9]{8}$/'],
        ], [
            'phone.regex' => 'رقم المحفظة غير صحيح، يجب أن يكون رقم محمول مصري مكون من 11 رقم (مثل 01012345678).',
        ]);

        DB::beginTransaction();
        try {
            // قفل صف المستخدم لمنع تضارب العمليات (Race Condition)
            $lockedMerchant = User::where('id', $merchant->id)->lockForUpdate()->first();

            // حساب الرصيد المتاح بدقة وقت الطلب
            $availableBalance = Order::where('user_id', $lockedMerchant->id)
                ->where('status', 'delivered')
                ->sum('profit');

            if ($availableBalance <= 0) {
                DB::rollBack();
                return back()->withErrors(['error' => 'عذراً، لا يوجد رصيد متاح للسحب حالياً!']);
            }

            // 1. تصفير رصيد المحفظة في جدول الـ users بأمان تام
            if (isset($lockedMerchant->wallet)) {
                $lockedMerchant->wallet = 0;
            }
            if (isset($lockedMerchant->wallet_balance)) {
                $lockedMerchant->wallet_balance = 0;
            }
            $lockedMerchant->save();

            // 2. تحديث الأوردرات المسلمة التي تم سحب أرباحها (أو وضع علامة أنها سُحبت لكي لا تتكرر في الحساب)
            // (اختياري: لو حابب تحدد جدول مخصص لطلبات السحب Withdrawals، أو تفضل معتمد على تصفير الخزنة مباشرتاً)

            DB::commit();
            return back()->with('success', 'تم تقديم طلب سحب الأرباح بنجاح، وتصفير الخزنة في انتظار التحويل على رقم فودافون كاش الخاص بك!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء تنفيذ طلب السحب: ' . $e->getMessage()]);
        }
    }
}