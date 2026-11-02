@extends('layouts.admin')

@section('title', 'Categories · CatalogPro Admin')
@section('page_title', 'Categories')

@section('content')
<div class="card-soft mb-4">
    <!-- Header & Add Button (بدون شريط بحث) -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3">
        <h2 class="h6 fw-bold mb-0">All categories</h2>
        
        <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-plus-lg me-1"></i> Add category
        </button>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0" id="catTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
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
                        <td colspan="6" class="text-center text-muted py-4">No categories found.</td>
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

<!-- مودالات التعديل والإضافة تظل كما هي في نهاية الملف -->
@foreach($categories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Category: {{ $category->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="3" maxlength="300">{{ $category->description }}</textarea>
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
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="editActive{{ $category->id }}" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
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

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="catTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg needs-validation" novalidate>
        @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="catTitle">Add New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <div class="mb-3">
          <label for="catName" class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="catName" name="name" placeholder="e.g. Electronics" required>
        </div>
        <div class="mb-3">
          <label for="catDesc" class="form-label small fw-semibold">Description</label>
          <textarea class="form-control" id="catDesc" name="description" rows="3" maxlength="300" placeholder="Short description…"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Image / Icon</label>
          <label class="drop-zone w-100">
            <i class="bi bi-cloud-arrow-up fs-3 text-primary d-block"></i>
            <span class="small text-muted">Click to upload (PNG, JPG, SVG)</span>
            <input type="file" name="image" accept="image/*" style="display: none;">
          </label>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="catActive" name="is_active" value="1" checked>
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