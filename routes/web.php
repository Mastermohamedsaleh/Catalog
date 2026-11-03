<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return view('auth.login');
})->name('catalog.index'); 

// مسارات الملف الشخصي (Profile)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// مسارات الأدمن المحمية (Admin Dashboard & Shell)
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', CustomerController::class)->only(['index', 'destroy']);

    // مسارات المنتجات الصحيحة (بدون تكرار prefix)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// مسارات السوبر أدمن فقط
Route::middleware(['auth', SuperAdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('admins', AdminUserController::class)->except(['create', 'show', 'edit']);
});

require __DIR__.'/auth.php';