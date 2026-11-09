<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class WalletController extends Controller
{
    public function index()
    {
        $merchant = Auth::user();

        // 1. حساب الرصيد المتاح بدقة من الأوردرات المسلمة (اللي اتدفع مكسبها للمحفظة أو حالتها delivered)
        // ده بيضمن إن الـ 100 جنيه تظهر فوق فوراً حتى لو حقل المحفظة في جدول الـ users لسه صفر
        $availableBalance = Order::where('user_id', $merchant->id)
            ->where('status', 'delivered')
            ->sum('profit');

        // لو حابب تعتمد على الحقل مباشرة وتخليه يقرأ القيمة الأكبر بينهم لضمان الأمان:
        if (isset($merchant->wallet) && $merchant->wallet > 0) {
            $availableBalance = max($merchant->wallet, $availableBalance);
        } elseif (isset($merchant->wallet_balance) && $merchant->wallet_balance > 0) {
            $availableBalance = max($merchant->wallet_balance, $availableBalance);
        }

        // 2. الأرباح المعلقة (الأوردرات التي لم يتم تسليمها بعد مثل pending أو processing أو shipped)
        $pendingProfit = Order::where('user_id', $merchant->id)
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->sum('profit');

        // 3. عدد الأوردرات المسلمة بنجاح
        $deliveredCount = Order::where('user_id', $merchant->id)
            ->where('status', 'delivered')
            ->count();

        // 4. عدد الأوردرات الملغية أو المرتجعة (تأكيد الحالات كلها cancelled, returned)
        $lostCount = Order::where('user_id', $merchant->id)
            ->whereIn('status', ['cancelled', 'returned'])
            ->count();

        // 5. جلب سجل المعاملات والأوردرات الخاصة بالتاجر مرتبة من الأحدث
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
}