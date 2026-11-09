<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'catalog_master_root')
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
     * إعادة تعيين كلمة السر للتاجر بواسطة الأدمن بكلمة مرور قوية (حروف وأرقام ورموز)
     */
    public function resetPassword(User $customer)
    {
        // توليد كلمة سر قوية ومستوفيّة للشروط (حروف كبيرة وصغيرة، أرقام، ورموز)
        $newPassword = 'Ab' . rand(10, 99) . '!' . Str::random(6); // مثال لشكل الباسورد: Ab42!x9kY

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