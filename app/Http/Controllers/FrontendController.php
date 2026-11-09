<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    // جلب أول 8 أقسام فقط للصفحة الرئيسية
    public function welcome()
    {
        $categories = Category::whereNull('parent_id')->withCount('products')->take(8)->get();
        return view('welcome', compact('categories'));
    }

    // جلب كل الأقسام لصفحة "View All"
    public function allCategories()
    {
        $allCategories = Category::whereNull('parent_id')->withCount('products')->get();
        return view('frontend.all-categories', compact('allCategories'));
    }

    // عرض القسم، الأقسام الفرعية، أو المنتجات حسب الهيكل الشجري
    public function showCategory($slug)
    {
        // جلب القسم الحالي مع أقسامه الفرعية
        $category = Category::with(['children', 'products' => function($q) {
            $q->where('is_active', 1);
        }])->where('slug', $slug)->firstOrFail();

        $products = collect();

        // لو القسم ليس لديه أقسام فرعية، نجلب منتجاته مباشرة
        if ($category->children->count() === 0) {
            $products = Product::where('category_id', $category->id)
                               ->where('is_active', 1)
                               ->latest()
                               ->paginate(12);
        }

        return view('frontend.category', compact('category', 'products'));
    }

    // عرض تفاصيل المنتج الفردي (الصور، المعرض، الفيديو، المتغيرات)
    public function showProduct($id)
    {
        $product = Product::with(['category', 'images', 'variants'])->where('is_active', 1)->findOrFail($id);

        return view('frontend.product-details', compact('product'));
    }
}