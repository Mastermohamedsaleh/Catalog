<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\FileUploadService;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . time();
        $data['is_active'] = $request->has('is_active');

        // رفع الصورة لو موجودة
        if ($request->hasFile('image')) {
            $data['image'] = $this->fileUploadService->upload($request->file('image'), 'categories');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category created successfully with image!');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . $category->id;
        $data['is_active'] = $request->has('is_active');

        // لو فيه صورة جديدة مرفوعة
        if ($request->hasFile('image')) {
            // احذف الصورة القديمة لو موجودة عشان ما تملأش مساحة السيرفر
            $this->fileUploadService->delete($category->image);
            // ارفع الصورة الجديدة
            $data['image'] = $this->fileUploadService->upload($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // حذف الصورة المرتبطة من التخزين نهائياً قبل حذف السجل
        $this->fileUploadService->delete($category->image);
        
        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category and its image deleted successfully!');
    }
}