<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'catalog_master_root')
                  ->orWhere('is_super', true)
                  ->latest()
                  ->paginate(10);
        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_super' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'catalog_master_root',
            'is_super' => $request->has('is_super'), // تحديد لو هو سوبر أدمن أو أدمن عادي
        ]);

        return redirect()->route('admin.admins.index')
                         ->with('success', 'Admin created successfully!');
    }

    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_super' => ['nullable', 'boolean'],
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        
        // منع السوبر أدمن الأساسي من إزالة صلاحية السوبر عن نفسه خطأً
        if ($admin->id !== auth()->id()) {
            $admin->is_super = $request->has('is_super');
        }

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.admins.index')
                         ->with('success', 'Admin updated successfully!');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own active account!');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
                         ->with('success', 'Admin deleted successfully!');
    }
}