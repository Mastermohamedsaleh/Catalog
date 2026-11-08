<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FrontendController;
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


    // Delete One image from gallery 
Route::delete('/product-images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');


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
// Route::get('categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');




// Frontend
Route::middleware(['auth'])->group(function () {


    Route::get('/categories', [FrontendController::class, 'allCategories'])->name('shop.categories.index');


    // عرض تفاصيل القسم (سواء رئيسي أو فرعي) والمنتجات اللي جواها
    Route::get('/category/{slug}', [FrontendController::class, 'showCategory'])->name('shop.category');
    
    // صفحة تفاصيل المنتج الفردي (صور، فديوهات، تفاصيل)
    Route::get('/product/{id}', [FrontendController::class, 'showProduct'])->name('shop.product.show');
});




Route::middleware(['auth'])->prefix('merchant')->name('merchant.')->group(function () {
    // صفحة الأوردرات ونموذج إضافة أوردر جديد
    Route::get('/orders', [App\Http\Controllers\Merchant\OrderController::class, 'index'])->name('orders.index');
    
    // حفظ الأوردر الجديد في الداتا بيز وخصم الاستوك
    Route::post('/orders', [App\Http\Controllers\Merchant\OrderController::class, 'store'])->name('orders.store');
    
    // مسار البحث اللحظي عن المنتجات والمقاسات (AJAX Search)
    Route::get('/products-search', [App\Http\Controllers\Merchant\OrderController::class, 'searchProducts'])->name('products.search');
    
    // صفحة المحفظة الخاصة بالتاجر
    Route::get('/wallet', function () {
        // جلب أوردرات التاجر وحساب الأرباح المعلقة والمتاحة من قاعدة البيانات
        $orders = \App\Models\Order::where('user_id', auth()->id())->latest()->get();
        $availableBalance = auth()->user()->wallet ?? 0;
        $pendingProfit = $orders->where('is_paid_to_wallet', false)->whereNotIn('status', ['returned', 'cancelled'])->sum('profit');
        $deliveredCount = $orders->where('status', 'delivered')->count();
        $lostCount = $orders->whereIn('status', ['returned', 'cancelled'])->count();

        return view('merchant.wallet', compact('orders', 'availableBalance', 'pendingProfit', 'deliveredCount', 'lostCount'));
    })->name('wallet');
});






require __DIR__.'/auth.php';