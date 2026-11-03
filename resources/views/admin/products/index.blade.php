@extends('layouts.admin')

@section('title', 'Products Management · CatalogPro Admin')
@section('page_title', 'Store Products')

@section('content')
<div class="card-soft mb-4">
    <!-- Header -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h2 class="h6 fw-bold mb-0">All Products</h2>
            <p class="text-muted small mb-0">Manage store inventory, media files, and pricing.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </button>
    </div>

    <!-- رسائل التنبيه -->
    @if(session('success'))
        <div class="alert alert-success m-3 rounded-3 small">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3 rounded-3 small">{{ session('error') }}</div>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th class="d-none d-md-table-cell">Media</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($product->main_image)
                                    <img src="{{ asset('storage/' . $product->main_image) }}" class="thumb" style="width: 44px; height: 44px; object-fit: cover; border-radius: .7rem;" alt="{{ $product->name }}">
                                @else
                                    <span class="thumb g{{ ($loop->index % 6) + 1 }}" style="width: 44px; height: 44px; border-radius: .7rem; display: flex; align-items: center; justify-content: center; background: #e9ecef; color: #fff;">
                                        <i class="bi bi-box-seam"></i>
                                    </span>
                                @endif
                                <span class="fw-semibold">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                        <td class="fw-bold text-primary">${{ number_format($product->price, 2) }}</td>
                        <td class="d-none d-md-table-cell text-muted small">
                            <i class="bi bi-images"></i> {{ $product->main_image ? 1 : 0 }} &nbsp;
                            <i class="bi bi-camera-video"></i> {{ $product->video_url ? 1 : 0 }}
                        </td>
                        <td>
                            @if($product->is_active ?? true)
                                <span class="badge badge-soft-success">Published</span>
                            @else
                                <span class="badge badge-soft-warning">Draft</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger btn-sm" aria-label="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No products found. Add your first product!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}</span>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>

<!-- ============ Modal إضافة منتج جديد ============ -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 needs-validation" novalidate>
      @csrf
      <div class="modal-header border-0 pb-0">
        <h2 class="modal-title h5 fw-bold">Add New Product</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Product title" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Price <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text border-end-0 bg-white" style="border-radius:.65rem 0 0 .65rem">$</span>
                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" placeholder="0.00" required style="border-radius:0 .65rem .65rem 0">
            </div>
            @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
              <option value="">Select category…</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
              @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Status</label>
            <select class="form-select" name="is_active">
              <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Published</option>
              <option value="0" {{ old('is_active') === 0 ? 'selected' : '' }}>Draft</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Full product details…">{{ old('description') }}</textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Main Image (Auto WebP)</label>
            <label class="drop-zone border-2 border-dashed rounded-3 p-3 text-center bg-light d-block cursor-pointer">
              <i class="bi bi-images fs-3 text-primary d-block"></i>
              <span class="small text-muted">Select image file</span>
              <input type="file" name="main_image" accept="image/*" class="d-none" required>
            </label>
            @error('main_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Direct Video File Upload</label>
            <label class="drop-zone border-2 border-dashed rounded-3 p-3 text-center bg-light d-block cursor-pointer">
              <i class="bi bi-camera-video fs-3 text-primary d-block"></i>
              <span class="small text-muted">Select MP4 video file</span>
              <input type="file" name="video_file" accept="video/*" class="d-none">
            </label>
            @error('video_file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush