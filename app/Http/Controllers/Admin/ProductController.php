<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\MediaService;

class ProductController extends Controller
{
    protected $mediaService;

    // حقن الـ MediaService في الكونستركتور للالتزام بالـ Architecture
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

public function index(Request $request)
{
    $query = Product::with('category', 'images', 'variants')->latest();

    // تفعيل البحث بالاسم أو الوصف
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $products = $query->paginate(10);
    $categories = Category::all();

    return view('admin.products.index', compact('products', 'categories'));
}

    public function store(Request $request)
    {
$request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'main_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // صور المعرض المتعددة
            'video_url'   => 'nullable|mimes:mp4,mov,avi,wmv|max:10240',    
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'عذراً، هذا الاسم مسجل مسبقاً لمنتج آخر. يرجى اختيار اسم فريد.',
            'category_id.required' => 'يرجى اختيار القسم التابع له المنتج.',
            'price.required' => 'سعر الجملة مطلوب.',
            'stock.required' => 'كمية المخزون مطلوبة.',
        ]);

        // التحقق من تطابق مجموع الاستوك للمتغيرات مع الاستوك الأساسي لو المنتج مفعل له متغيرات
        if ($request->has('has_variants') && $request->has('variants')) {
            $totalVariantStock = collect($request->variants)->sum('stock');
            if ($totalVariantStock != $request->stock) {
                return back()->withInput()->withErrors([
                    'stock' => "مجموع مخزون المقاسات والألوان ($totalVariantStock) يجب أن يساوي المخزون الإجمالي للمنتج ({$request->stock})!"
                ]);
            }
        }

        // رفع وتحسين الصورة الرئيسية باستخدام الـ MediaService
        $imagePath = null;
        if ($request->hasFile('main_image')) {
            $imagePath = $this->mediaService->uploadAndOptimizeImage($request->file('main_image'), 'products/images');
        }
        
        // رفع الفيديو
        $videoPath = null;
        if ($request->hasFile('video_url')) {
            $videoPath = $this->mediaService->uploadFile($request->file('video_url'), 'products/videos');
        }

        // توليد Slug فريد لمنع خطأ التكرار Duplicate entry
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        // إنشاء المنتج الأساسي
        $product = Product::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => $slug, // استخدام الـ Slug الفريد النظيف
            'price'       => $request->price,
            'stock'       => $request->stock,
            'main_image'  => $imagePath,  
            'video_url'   => $videoPath,  
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
        ]);

        // حفظ صور المعرض المتعددة (Multiple Gallery Images)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $galleryPath = $this->mediaService->uploadAndOptimizeImage($file, 'products/gallery');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $galleryPath,
                ]);
            }
        }

        // حفظ المقاسات والألوان (Variants) لو المنتج مفعل له الخاصية دي
        if ($request->has('has_variants') && $request->has('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['size']) || !empty($variant['color'])) {
                    ProductVariant::create([
                        'product_id'     => $product->id,
                        'size'           => $variant['size'] ?? null,
                        'color'          => $variant['color'] ?? null,
                        'stock'          => $variant['stock'] ?? 0,
                        'price_modifier' => $variant['price_modifier'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج مع صوره ومتغيراته بنجاح!');
    }

    public function update(Request $request, Product $product)
    {
$request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'main_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'video_url'   => 'nullable|mimes:mp4,mov,avi,wmv|max:10240',
            'description' => 'nullable|string',
        ], [
            'name.unique' => 'عذراً، هذا الاسم مسجل مسبقاً لمنتج آخر. يرجى اختيار اسم فريد.',
            'category_id.required' => 'يرجى اختيار القسم التابع له المنتج.',
            'price.required' => 'سعر الجملة مطلوب.',
            'stock.required' => 'كمية المخزون مطلوبة.',
        ]);

        // التحقق من تطابق الاستوك أثناء التعديل أيضاً
        if ($request->has('has_variants') && $request->has('variants')) {
            $totalVariantStock = collect($request->variants)->sum('stock');
            if ($totalVariantStock != $request->stock) {
                return back()->withInput()->withErrors([
                    'stock' => "مجموع مخزون المقاسات والألوان ($totalVariantStock) يجب أن يساوي المخزون الإجمالي للمنتج ({$request->stock})!"
                ]);
            }
        }

        $imagePath = $product->main_image;
        if ($request->hasFile('main_image')) {
            $this->mediaService->deleteFile($product->main_image);
            $imagePath = $this->mediaService->uploadAndOptimizeImage($request->file('main_image'), 'products/images');
        }

        $videoPath = $product->video_url;
        if ($request->hasFile('video_url')) {
            $this->mediaService->deleteFile($product->video_url);
            $videoPath = $this->mediaService->uploadFile($request->file('video_url'), 'products/videos');
        }

        // توليد Slug فريد أثناء التحديث (مع استثناء المنتج الحالي نفسه)
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $product->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => $slug, // الـ Slug المحدث الفريد
            'price'       => $request->price,
            'stock'       => $request->stock,
            'main_image'  => $imagePath,
            'video_url'   => $videoPath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
        ]);

        // تحديث صور المعرض
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $galleryPath = $this->mediaService->uploadAndOptimizeImage($file, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $galleryPath,
                ]);
            }
        }

        // تحديث المتغيرات (Variants)
        if ($request->has('has_variants')) {
            $product->variants()->delete(); 
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    if (!empty($variant['size']) || !empty($variant['color'])) {
                        ProductVariant::create([
                            'product_id'     => $product->id,
                            'size'           => $variant['size'] ?? null,
                            'color'          => $variant['color'] ?? null,
                            'stock'          => $variant['stock'] ?? 0,
                            'price_modifier' => $variant['price_modifier'] ?? 0,
                        ]);
                    }
                }
            }
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح!');
    }

    public function destroy(Product $product)
    {
        // حذف الصورة الرئيسية
        if ($product->main_image) {
            $this->mediaService->deleteFile($product->main_image);
        }

        // حذف الفيديو
        if ($product->video_url) {
            $this->mediaService->deleteFile($product->video_url);
        }

        // حذف صور المعرض المرتبطة بالمنتج من التخزين ومن الداتا بيز
        foreach ($product->images as $img) {
            $this->mediaService->deleteFile($img->image_path);
            $img->delete();
        }

        // حذف المتغيرات المرتبطة
        $product->variants()->delete();

        $product->delete();
        
        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج وملفاته ومتغيراته بنجاح.');
    }

// Delete One Image from Galley
    public function destroyImage(ProductImage $image)
{
    // حذف الملف من التخزين
    if ($image->image_path) {
        $this->mediaService->deleteFile($image->image_path);
    }
    
    // حذف السجل من قاعدة البيانات
    $image->delete();

    return back()->with('success', 'تم حذف الصورة من المعرض بنجاح.');
}

}