<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\SuperAdminMiddleware;


Route::get('/', function () {
    return view('auth.login');
})->name('catalog.index'); 

// 2. مسارات ملف الشخصي (Profile)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 3. مسارات الأدمن المحمية (Admin Dashboard & Shell)
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
Route::resource('admins', AdminUserController::class)->except(['create', 'show', 'edit']);
});


Route::middleware([SuperAdminMiddleware::class])->group(function () {
        Route::resource('admins', AdminUserController::class)->except(['create', 'show', 'edit']);
    });



require __DIR__.'/auth.php';