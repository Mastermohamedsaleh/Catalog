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


    // 1. عرض القسم والأقسام الفرعية التابعة له ومنتجاته
    public function showCategory($slug)
    {
        // جلب القسم الحالي مع أقسامه الفرعية (الأولاد)
        $category = Category::with(['children', 'products' => function($q) {
            $q->where('is_active', 1);
        }])->where('slug', $slug)->firstOrFail();

        // لو القسم الرئيسي تحته أقسام فرعية، بنجمع آي دي هات القسم الرئيسي والأقسام الفرعية عشان نعرض كل منتجاتهم
        $categoryIds = $category->children->pluck('id')->push($category->id);

        // جلب المنتجات التابعة للقسم (والأقسام الفرعية إن وجدت) مع الباجينيشن
        $products = Product::whereIn('category_id', $categoryIds)
                           ->where('is_active', 1)
                           ->latest()
                           ->paginate(12);

        return view('frontend.category', compact('category', 'products'));
    }

    // 2. عرض تفاصيل المنتج الفردي (الصور، المعرض، الفيديو، المتغيرات)
    public function showProduct($id)
    {
        $product = Product::with(['category', 'images', 'variants'])->where('is_active', 1)->findOrFail($id);

        return view('frontend.product-details', compact('product'));
    }
}