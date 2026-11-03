<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4">
    @csrf

    <div class="modal-header border-0 pb-0">
        <h2 class="modal-title h5 fw-bold" id="prodTitle">Add New Product</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="row g-3">
            <!-- Title -->
            <div class="col-md-8">
                <label for="pTitle" class="form-label small fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="pTitle" name="name" value="{{ old('name') }}" placeholder="Product title" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Price -->
            <div class="col-md-4">
                <label for="pPrice" class="form-label small fw-semibold">Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-white" style="border-radius:.65rem 0 0 .65rem">$</span>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="pPrice" name="price" value="{{ old('price') }}" min="0" step="0.01" placeholder="0.00" required style="border-radius:0 .65rem .65rem 0">
                </div>
                @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Category -->
            <div class="col-md-6">
                <label for="pCat" class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="pCat" name="category_id" required>
                    <option value="">Select category…</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Status -->
            <div class="col-md-6">
                <label for="pStatus" class="form-label small fw-semibold">Status</label>
                <select class="form-select" id="pStatus" name="is_active">
                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ old('is_active') === 0 ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <!-- Description -->
            <div class="col-12">
                <label for="pDesc" class="form-label small fw-semibold">Description</label>
                <textarea class="form-control" id="pDesc" name="description" rows="4" placeholder="Full product details…">{{ old('description') }}</textarea>
            </div>

            <!-- Main Image Upload (Local Storage + WebP) -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Main Image (Auto WebP)</label>
                <label class="drop-zone">
                    <i class="bi bi-images fs-3 text-primary d-block"></i>
                    <span class="small text-muted">Select image file</span>
                    <input type="file" name="main_image" accept="image/*" data-preview="#imgPreview">
                </label>
                <div class="preview-grid" id="imgPreview"></div>
            </div>

            <!-- Direct Video File Upload (Auto Upload to S3/Cloud) -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Direct Video File Upload</label>
                <label class="drop-zone">
                    <i class="bi bi-camera-video fs-3 text-primary d-block"></i>
                    <span class="small text-muted">Select MP4 / WebM from computer</span>
                    <input type="file" name="video_file" accept="video/*" data-preview="#vidPreview">
                </label>
                <div class="preview-grid" id="vidPreview"></div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save product</button>
    </div>
</form>