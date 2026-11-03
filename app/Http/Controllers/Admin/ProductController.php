<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    // دالة عرض المنتجات في الجدول
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

public function store(Request $request)
{
    // التحقق من البيانات
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'main_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    // استبعاد الـ _token والـ video_file مؤقتاً لتجهيز البيانات
    $data = $request->except(['_token', 'video_file']);
    
    $data['slug'] = Str::slug($request->name);
    $data['is_active'] = $request->input('is_active', 1);

    // معالجة وضغط الصورة الرئيسية
    if ($request->hasFile('main_image')) {
        $data['main_image'] = $this->mediaService->uploadAndOptimizeImage($request->file('main_image'));
    }

    // معالجة الفيديو لو موجود
    if ($request->hasFile('video_file')) {
        $data['video_url'] = $request->file('video_file')->store('products/videos', 'public');
    }

    // حفظ المنتج بنجاح بدون مشاكل Mass Assignment
    Product::create($data);

    return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
}

    // دالة حذف المنتج
    public function destroy(Product $product)
    {
        // حذف الملفات المرتبطة لو حابة (اختياري)
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }
}