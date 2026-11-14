<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // استخدام with('wallet') لجلب الرصيد مباشرة وبسرعة فائقة 🚀
        $query = User::with('wallet')
                     ->where('role', '!=', 'catalog_master_root')
                     ->where('is_super', false);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * تصفير محفظة التاجر / العميل (سحب الأرباح)
     */
    public function zeroWallet(User $customer)
    {
        DB::beginTransaction();
        try {
            // قفل محفظة المستخدم لمنع أي تضارب (Race Condition)
            $wallet = Wallet::where('user_id', $customer->id)->lockForUpdate()->first();

            if (!$wallet || $wallet->balance <= 0) {
                DB::rollBack();
                return back()->with('error', 'عذراً، هذا التاجر ليس لديه رصيد متاح لتصفيره!');
            }

            $amountToWithdraw = $wallet->balance;

            // 1. تسجيل معاملة السحب في سجل الحركات المالية (Audit Trail)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amountToWithdraw,
                'description' => "تصفير وسحب الأرباح بواسطة الإدارة من لوحة التحكم"
            ]);

            // 2. تصفير الرصيد في جدول المحافظ فوراً
            $wallet->balance = 0;
            $wallet->save();

            DB::commit();
            return back()->with('success', "تم تصفير محفظة التاجر ({$customer->name}) بنجاح بمبلغ {$amountToWithdraw} EGP!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تصفير المحفظة: ' . $e->getMessage());
        }
    }

    /**
     * إعادة تعيين كلمة السر للتاجر بواسطة الأدمن بكلمة مرور قوية
     */
    public function resetPassword(User $customer)
    {
        $newPassword = 'Ab' . rand(10, 99) . '!' . Str::random(6);

        $customer->update([
            'password' => Hash::make($newPassword),
        ]);

        return back()->with('success', "تم إعادة تعيين كلمة السر بنجاح! كلمة السر الجديدة للتاجر ({$customer->name}) هي: <strong>{$newPassword}</strong>");
    }

    public function destroy(User $customer)
    {
        if ($customer->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
                         ->with('success', 'Customer deleted successfully!');
    }
}