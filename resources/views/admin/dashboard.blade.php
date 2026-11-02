@extends('layouts.admin')

@section('title', 'Dashboard Overview')
@section('page_title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3">
                <div class="stat-icon bg-brand-soft text-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-muted small">Total Products</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['products_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-tags"></i></div>
                <div>
                    <div class="text-muted small">Categories</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['categories_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-people"></i></div>
                <div>
                    <div class="text-muted small">Users</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['users_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <div class="text-muted small">Admins</div>
                    <div class="h3 fw-bold mb-0">{{ $stats['admins_count'] ?? 1 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <!-- Latest products -->
        <div class="col-xl-8">
            <div class="card-soft">
                <div class="d-flex justify-content-between align-items-center p-3 p-lg-4 pb-2">
                    <h2 class="h6 fw-bold mb-0">Latest products</h2>
                    <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestProducts ?? [] as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="thumb g1"><i class="bi bi-box"></i></span>
                                            <span class="fw-semibold">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge badge-soft-success">Published</span>
                                        @else
                                            <span class="badge badge-soft-warning">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No products added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="col-xl-4">
            <div class="card-soft p-3 p-lg-4 h-100">
                <h2 class="h6 fw-bold mb-3">Quick actions</h2>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add category</a>
                    <a href="#" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Add product</a>
                    <button class="btn btn-light" onclick="showToast('Cache cleared successfully', 'info')">
                        <i class="bi bi-arrow-repeat me-1"></i>Clear cache
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection