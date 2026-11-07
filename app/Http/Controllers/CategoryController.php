<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    // 1. عرض الأقسام في جدول الداشبورد (الدالة اللي كانت ناقصة)
public function index()
    {
        $categories = Category::with('parent', 'children')->withCount('products')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }
    
    public function show(Category $category) 
   {
    $products = $category->products()->paginate(10);

    return view('admin.categories.show', compact('category', 'products'));
   }



    // 2. حفظ قسم جديد (رئيسي أو فرعي)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except(['_token', 'image']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->input('is_active', 1);

        if ($request->hasFile('image')) {
            $data['image'] = $this->mediaService->uploadAndOptimizeImage($request->file('image'), 'categories');
        }

        Category::create($data);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    // 3. تعديل القسم
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except(['_token', '_method', 'image']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->input('is_active', 0);

        if ($request->hasFile('image')) {
            $this->mediaService->deleteFile($category->image);
            $data['image'] = $this->mediaService->uploadAndOptimizeImage($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    // 4. حذف القسم
    public function destroy(Category $category)
    {
        $this->mediaService->deleteFile($category->image);
        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}