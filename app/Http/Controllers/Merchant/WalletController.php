<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    public function index()
    {
        $merchant = Auth::user();

        // 1. جلب الرصيد المتاح من جدول المحافظ مباشرة (أداء صاروخي 🚀)
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $merchant->id],
            ['balance' => 0]
        );
        
        $availableBalance = $wallet->balance;

        // 2. الأرباح المعلقة (Pending, Processing, Shipped) من جدول الأوردرات
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

        // 5. جلب سجل الأوردرات
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
            // قفل محفظة التاجر لمنع أي تضارب (Race Condition)
            $wallet = Wallet::where('user_id', $merchant->id)->lockForUpdate()->first();

            if (!$wallet || $wallet->balance <= 0) {
                DB::rollBack();
                return back()->withErrors(['error' => 'عذراً، لا يوجد رصيد متاح للسحب حالياً!']);
            }

            $amountToWithdraw = $wallet->balance;

            // 1. تسجيل معاملة السحب في سجل الحركات المالية (Audit Trail)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amountToWithdraw,
                'description' => "سحب أرباح أونلاين على رقم فودافون كاش: " . $request->phone
            ]);

            // 2. تصفير رصيد المحفظة فوراً
            $wallet->balance = 0;
            $wallet->save();

            DB::commit();
            return back()->with('success', 'تم تقديم طلب سحب الأرباح بنجاح، وتصفير الخزنة في انتظار التحويل على رقم فودافون كاش الخاص بك!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء تنفيذ طلب السحب: ' . $e->getMessage()]);
        }
    }
}