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
use App\Http\Controllers\Admin\OrderController;
use App\Models\Category;

// Route::get('/', function () {
//     return view('auth.login');
// })->name('catalog.index'); 

Route::get('/', function () {
    $categories = Category::withCount('products')->get();    
    return view('welcome', compact('categories'));
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
    
// استثنينا الـ show من الـ resource عشان نعمله بشكل صريح ومضبوط بالـ admin prefix
Route::resource('categories', CategoryController::class)
    ->except(['create', 'show', 'edit'])
    ->parameters(['categories' => 'category:slug']);


    Route::resource('customers', CustomerController::class)->only(['index', 'destroy']);

    Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});

// مسارات السوبر أدمن فقط
Route::middleware(['auth', SuperAdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('admins', AdminUserController::class)->except(['create', 'show', 'edit']);
});

// front end

// مسار عرض القسم للعامة أو الزوار
Route::get('categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

require __DIR__.'/auth.php';