<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;



class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products_count'   => Product::count(),
            'categories_count' => Category::count(),
            'users_count'      => User::where('role', 'app_client_usr')->count(),
            'admins_count'     => User::where('role', 'catalog_master_root')->count(),
        ];

        $latestProducts = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'latestProducts'));
    }
}
