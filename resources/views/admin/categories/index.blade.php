@extends('layouts.admin')

@section('title', 'Categories · CatalogPro Admin')
@section('page_title', 'Categories')

@section('content')
<div class="card-soft mb-4">
    <!-- Header & Add Button -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3">
        <h2 class="h6 fw-bold mb-0">All categories</h2>
        
        <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-plus-lg me-1"></i> Add category
        </button>
    </div>

    <!-- رسائل النجاح أو الخطأ العامة -->
    @if(session('success'))
        <div class="alert alert-success m-3 rounded-3 small alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3 rounded-3 small alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0" id="catTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Parent Category</th>
                    <th class="d-none d-md-table-cell">Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" class="thumb rounded-3 object-fit-cover" alt="{{ $category->name }}" style="width: 40px; height: 40px;">
                                @else
                                    <span class="thumb g{{ ($loop->index % 6) + 1 }}"><i class="bi bi-tags"></i></span>
                                @endif
                                <span class="fw-semibold">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($category->parent)
                                <span class="badge bg-light text-dark border">{{ $category->parent->name }}</span>
                            @else
                                <span class="text-muted small">— Main Category —</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell text-muted">{{ Str::limit($category->description, 50) ?? '—' }}</td>
                        <td>{{ $category->products_count ?? 0 }}</td>
                        <td>
                            @if($category->is_active)
                                <span class="badge badge-soft-success">Active</span>
                            @else
                                <span class="badge badge-soft-danger">Hidden</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-icon btn-light" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategoryModal{{ $category->id }}" 
                                    aria-label="Edit">
                                <i class="bi bi-pencil text-primary"></i>
                            </button>

                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger" aria-label="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }}</span>
            <div>
                {{ $categories->links() }}
            </div>
        </div>
    @endif
</div>

<!-- مودالات التعديل -->
@foreach($categories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Category: {{ $category->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- اختيار القسم الرئيسي أثناء التعديل -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Parent Category</label>
                    <select name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                        <option value="">-- Main Category (No Parent) --</option>
                        @foreach(\App\Models\Category::whereNull('parent_id')->where('id', '!=', $category->id)->get() as $mainCat)
                            <option value="{{ $mainCat->id }}" {{ old('parent_id', $category->parent_id) == $mainCat->id ? 'selected' : '' }}>
                                {{ $mainCat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" maxlength="300">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Image / Icon</label>
                    @if($category->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $category->image) }}" class="rounded-2" width="60" height="60" style="object-fit: cover;">
                        </div>
                    @endif
                    <label class="drop-zone w-100">
                        <i class="bi bi-cloud-arrow-up fs-3 text-primary d-block"></i>
                        <span class="small text-muted">Click to change image</span>
                        <input type="file" name="image" accept="image/*" style="display: none;">
                    </label>
                    @error('image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="editActive{{ $category->id }}" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="editActive{{ $category->id }}">Active (visible on website)</label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update category</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- مودال الإضافة -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="catTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
        @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="catTitle">Add New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <div class="mb-3">
          <label for="catName" class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" id="catName" name="name" value="{{ old('name') }}" placeholder="e.g. Electronics" required>
          @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- اختيار القسم الرئيسي عند الإضافة -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Parent Category (Optional)</label>
          <select name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
              <option value="">-- Main Category (No Parent) --</option>
              @foreach(\App\Models\Category::whereNull('parent_id')->get() as $mainCat)
                  <option value="{{ $mainCat->id }}" {{ old('parent_id') == $mainCat->id ? 'selected' : '' }}>{{ $mainCat->name }}</option>
              @endforeach
          </select>
          @error('parent_id')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label for="catDesc" class="form-label small fw-semibold">Description</label>
          <textarea class="form-control @error('description') is-invalid @enderror" id="catDesc" name="description" rows="3" maxlength="300" placeholder="Short description…">{{ old('description') }}</textarea>
          @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Image / Icon</label>
          <label class="drop-zone w-100">
            <i class="bi bi-cloud-arrow-up fs-3 text-primary d-block"></i>
            <span class="small text-muted">Click to upload (PNG, JPG, SVG)</span>
            <input type="file" name="image" accept="image/*" style="display: none;">
          </label>
          @error('image')
              <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="catActive" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
          <label class="form-check-label small" for="catActive">Active (visible on website)</label>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save category</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // لو حصل خطأ في الـ Validation، افتح مودال الإضافة تلقائياً عشان المستخدم يشوف الخطأ
    @if ($errors->has('name') || $errors->has('parent_id') || $errors->has('description') || $errors->has('image'))
        @if(!session('edit_modal_id'))
            var categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            categoryModal.show();
        @endif
    @endif

    if (location.search.indexOf('new=1') > -1) {
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this category?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush