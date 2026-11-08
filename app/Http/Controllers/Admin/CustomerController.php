<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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