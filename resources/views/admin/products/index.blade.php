@extends('layouts.admin')

@section('title', 'Products · CatalogPro Admin')
@section('page_title', 'Store Products')

@section('content')
<div class="card-soft mb-4">
    <!-- Header, Search & Add Button -->
    <div class="p-3 p-lg-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <h2 class="h6 fw-bold mb-0">All Products</h2>
        
        <!-- فورم البحث المباشر للتعامل مع آلاف المنتجات -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto" style="max-width: 350px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap"><i class="bi bi-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger btn-sm" title="Clear Search"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>

        <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-lg me-1"></i> Add new product
        </button>
    </div>

    <!-- تنبيه عام لو فيه أي أخطاء في الـ Validation -->
    @if ($errors->any())
        <div class="alert alert-danger m-3 mb-0 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>
                <strong>تنبيه!</strong> برجاء مراجعة البيانات المدخلة وتصحيح الأخطاء المشار إليها أدناه.
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success m-3 mb-0 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Table -->
    <div class="table-responsive p-3">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Video Preview</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Wholesale Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        
                        <!-- معاينة الصورة الرئيسية -->
                        <td>
                            @if($product->main_image)
                                <a href="{{ asset('storage/' . $product->main_image) }}" target="_blank" title="View Full Image">
                                    <img src="{{ asset('storage/' . $product->main_image) }}" alt="Product" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                </a>
                            @else
                                <span class="badge bg-light text-muted border">No Image</span>
                            @endif
                        </td>

                        <!-- معاينة الفيديو -->
                        <td>
                            @if($product->video_url)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#videoModal{{ $product->id }}" title="Watch Video">
                                    <i class="bi bi-play-circle-fill me-1"></i> Watch
                                </button>
                            @else
                                <span class="badge bg-light text-muted border">No Video</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            
                            <!-- زر عرض صور المعرض السريع -->
                            @if($product->images->count() > 0)
                                <button type="button" class="btn btn-sm btn-light border mt-1 text-primary p-0 px-2 py-0 shadow-sm" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#galleryModal{{ $product->id }}">
                                    <i class="bi bi-images me-1"></i> معرض الصور ({{ $product->images->count() }})
                                </button>
                            @endif

                            @if($product->variants->count() > 0)
                                <span class="badge bg-secondary ms-1" style="font-size: 10px;">{{ $product->variants->count() }} variants</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $product->category->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-primary">{{ $product->price }} EGP</span>
                        </td>
                        <td>
                            @if($product->stock > 0)
                                <span class="badge badge-soft-success">{{ $product->stock }} in stock</span>
                            @else
                                <span class="badge badge-soft-danger">Out of stock</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $product->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <!-- زرار التعديل -->
                            <button class="btn btn-icon btn-light" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal{{ $product->id }}" 
                                    title="Edit Product">
                                <i class="bi bi-pencil text-primary"></i>
                            </button>

                            <!-- زرار الحذف -->
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger" title="Delete Product">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($products) && $products->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}</span>
            <div>{{ $products->appends(request()->query())->links() }}</div>
        </div>
    @endif
</div>

<!-- ================= مودال إضافة منتج جديد ================= -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg needs-validation" novalidate>
        @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        
        <!-- اسم المنتج -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter product name" required>
        </div>

        <!-- القسم -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
              <option value="">-- Choose Category --</option>
              @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}
                  </option>
              @endforeach
          </select>
        </div>

        <!-- السعر والمخزون -->
        <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-semibold">Wholesale Price (EGP) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" placeholder="e.g. 150" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label small fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('stock') is-invalid @enderror" name="stock" value="{{ old('stock', 10) }}" required>
            </div>
        </div>

        <!-- الصورة الرئيسية -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Main Image</label>
          <input type="file" class="form-control" name="main_image" accept="image/*">
        </div>

        <!-- صور المعرض المتعددة -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Gallery Images (Multiple)</label>
          <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
          <div class="form-text small text-muted">يمكنك اختيار أكثر من صورة لعرضها في تفاصيل المنتج.</div>
        </div>

        <!-- الفيديو -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Video (MP4, MOV)</label>
          <input type="file" class="form-control" name="video_url" accept="video/*">
          <div class="form-text small text-muted">فيديو تسويقي للمنتج يستطيع المسوقون تحميله.</div>
        </div>

        <!-- قسم المتغيرات (المقاسات والألوان) -->
        <div class="card bg-light border-0 p-3 mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="has_variants" id="hasVariantsCheck" value="1" {{ old('has_variants') ? 'checked' : '' }}>
              <label class="form-check-label fw-bold small text-dark" for="hasVariantsCheck">هل المنتج يحتوي على مقاسات وألوان متعددة؟ (مثل الملابس والأحذية)</label>
            </div>
            
            <div id="variantsSection" style="display: {{ old('has_variants') ? 'block' : 'none' }};">
                <p class="small text-muted mb-2">أضف المقاسات والألوان المتاحة مع مخزونها الخاص:</p>
                <div id="variantsContainer">
                    <div class="row g-2 mb-2 variant-row">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" name="variants[0][size]" placeholder="Size (e.g. S, M, L, XL)">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" name="variants[0][color]" placeholder="Color (e.g. Red, Black)">
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control form-control-sm" name="variants[0][stock]" placeholder="Stock" value="10">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-variant" style="display:none;"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addVariantBtn"><i class="bi bi-plus"></i> إضافة مقاس/لون آخر</button>
            </div>
        </div>

        <!-- الوصف -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Description</label>
          <textarea class="form-control" name="description" rows="3" placeholder="Product details...">{{ old('description') }}</textarea>
        </div>

        <!-- الحالة -->
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
          <label class="form-check-label small fw-semibold" for="isActiveCheck">Active Product</label>
        </div>

      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save product</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= مودالات التعديل، معاينة المعرض، ومعاينة الفيديو لكل منتج ================= -->
@foreach($products as $product)

<!-- 1. مودال التعديل -->
<div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
        @csrf
        @method('PUT')
        
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Product: {{ $product->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-start">
        
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-control" required>
              @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}
                  </option>
              @endforeach
          </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-semibold">Wholesale Price (EGP) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" name="price" value="{{ $product->price }}" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label small fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="stock" value="{{ $product->stock }}" required>
            </div>
        </div>

        <!-- الصورة الرئيسية -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Main Image</label>
          @if($product->main_image)
              <div class="mb-2">
                  <img src="{{ asset('storage/' . $product->main_image) }}" alt="Current Image" class="rounded border" style="max-height: 80px;">
              </div>
          @endif
          <input type="file" class="form-control" name="main_image" accept="image/*">
          <div class="form-text small text-muted">اتركها فارغة إذا لم ترد تغيير الصورة.</div>
        </div>

        <!-- صور المعرض -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Gallery Images</label>
          @if($product->images->count() > 0)
              <div class="d-flex flex-wrap gap-2 mb-2 p-2 bg-light rounded border">
                  @foreach($product->images as $img)
                      <div class="position-relative border rounded p-1 bg-white" style="width: 60px; height: 60px;">
                          <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded w-100 h-100" style="object-fit: cover;">
                          
                          <div class="position-absolute top-0 start-100 translate-middle">
                              <button type="submit" form="delete-img-form-{{ $img->id }}" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 20px; height: 20px;" title="Delete Image" onclick="return confirm('حذف هذه الصورة؟');">
                                  <i class="bi bi-x" style="font-size: 12px;"></i>
                              </button>
                          </div>
                      </div>
                  @endforeach
              </div>
          @else
              <div class="text-muted small mb-2">لا توجد صور إضافية في المعرض.</div>
          @endif

          <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
        </div>

        <!-- الفيديو -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Product Video</label>
          @if($product->video_url)
              <div class="mb-2">
                  <video width="160" controls class="rounded border">
                      <source src="{{ asset('storage/' . $product->video_url) }}" type="video/mp4">
                      Your browser does not support the video tag.
                  </video>
              </div>
          @endif
          <input type="file" class="form-control" name="video_url" accept="video/*">
        </div>

        <!-- المتغيرات -->
        <div class="card bg-light border-0 p-3 mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="has_variants" id="editHasVariantsCheck{{ $product->id }}" value="1" {{ $product->variants->count() > 0 ? 'checked' : '' }}>
              <label class="form-check-label fw-bold small text-dark" for="editHasVariantsCheck{{ $product->id }}">تفعيل المقاسات والألوان لهذا المنتج</label>
            </div>
            
            <div id="editVariantsSection{{ $product->id }}" style="display: {{ $product->variants->count() > 0 ? 'block' : 'none' }};">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr class="small text-muted">
                                <th>Size</th>
                                <th>Color</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->variants as $index => $variant)
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[{{ $index }}][size]" value="{{ $variant->size }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[{{ $index }}][color]" value="{{ $variant->color }}"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[0][size]" placeholder="Size"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="variants[0][color]" placeholder="Color"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="variants[0][stock]" value="10"></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Description</label>
          <textarea class="form-control" name="description" rows="3">{{ $product->description }}</textarea>
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_active" id="isActiveEdit{{ $product->id }}" value="1" {{ $product->is_active ? 'checked' : '' }}>
          <label class="form-check-label small fw-semibold" for="isActiveEdit{{ $product->id }}">Active Product</label>
        </div>

      </div>

      <!-- الفوتر الصحيح لجوا المودال (الزرارين في مكانهم الصح تحت) -->
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update product</button>
      </div>

    </form>
  </div>
</div>

<!-- 2. مودال معاينة صور المعرض السريعة -->
@if($product->images->count() > 0)
<div class="modal fade" id="galleryModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold fs-6">معرض صور: {{ $product->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="small text-muted mb-3">يمكنك معاينة الصور الإضافية وحذف ما تحتاجه مباشرة:</p>
        
        <div class="d-flex flex-wrap gap-3 justify-content-center p-3 bg-light rounded border">
            @foreach($product->images as $img)
                <div class="position-relative border rounded p-1 bg-white shadow-sm" style="width: 80px; height: 80px;">
                    <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded w-100 h-100" style="object-fit: cover;">
                    
                    <!-- زر حذف الصورة الفردية -->
                    <form action="{{ route('admin.products.images.destroy', $img->id) }}" method="POST" class="position-absolute top-0 start-100 translate-middle" onsubmit="return confirm('هل أنت متأكد من حذف هذه الصورة؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow" style="width: 22px; height: 22px;" title="حذف الصورة">
                            <i class="bi bi-x" style="font-size: 14px;"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>
@endif

<!-- 3. مودال معاينة الفيديو لكل منتج -->
@if($product->video_url)
<div class="modal fade" id="videoModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg bg-dark text-white">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fs-6">Video Preview: {{ $product->name }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-3">
        <video width="100%" controls class="rounded shadow">
            <source src="{{ asset('storage/' . $product->video_url) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
      </div>
    </div>
  </div>
</div>
@endif

<!-- فورمات حذف صور المعرض المنفصلة (خارج المودالات تماماً عشان تخدم زر الحذف جوه المودال صح) -->
@foreach($product->images as $img)
    <form id="delete-img-form-{{ $img->id }}" action="{{ route('admin.products.images.destroy', $img->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // تأكيد الحذف العام للمنتج
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this product and its media files?')) {
                e.preventDefault();
            }
        });
    });

    // إظهار/إخفاء قسم المتغيرات في مودال الإضافة
    const hasVariantsCheck = document.getElementById('hasVariantsCheck');
    const variantsSection = document.getElementById('variantsSection');
    if (hasVariantsCheck) {
        hasVariantsCheck.addEventListener('change', function() {
            variantsSection.style.display = this.checked ? 'block' : 'none';
        });
    }

    // إضافة صف جديد للمتغيرات ديناميكياً
    let variantIndex = 1;
    const addVariantBtn = document.getElementById('addVariantBtn');
    const variantsContainer = document.getElementById('variantsContainer');
    
    if (addVariantBtn) {
        addVariantBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 variant-row';
            row.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][size]" placeholder="Size (e.g. S, M, L)">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][color]" placeholder="Color (e.g. Red, Black)">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][stock]" placeholder="Stock" value="10">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-variant"><i class="bi bi-x"></i></button>
                </div>
            `;
            variantsContainer.appendChild(row);
            variantIndex++;

            row.querySelector('.remove-variant').addEventListener('click', function() {
                row.remove();
            });
        });
    }

    // تفعيل إظهار/إخفاء المتغيرات في مودالات التعديل لكل منتج
    @foreach($products as $prod)
        const editCheck{{ $prod->id }} = document.getElementById('editHasVariantsCheck{{ $prod->id }}');
        const editSec{{ $prod->id }} = document.getElementById('editVariantsSection{{ $prod->id }}');
        if (editCheck{{ $prod->id }}) {
            editCheck{{ $prod->id }}.addEventListener('change', function() {
                editSec{{ $prod->id }}.style.display = this.checked ? 'block' : 'none';
            });
        }
    @endforeach

    // إعادة فتح مودال الإضافة تلقائياً لو حصل خطأ في الـ Validation
    @if ($errors->any())
        var productModal = new bootstrap.Modal(document.getElementById('productModal'));
        productModal.show();
    @endif
});
</script>
@endpush