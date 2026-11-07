@extends('layouts.site')

@section('title', 'CatalogPro – Browse categories')

@section('content')
<!-- Hero Section -->
<section class="hero p-4 p-lg-5 mb-5">
    <div class="position-relative" style="z-index:1;max-width:560px">
        <h1 class="display-5 fw-bold mb-3">Find the right product, faster.</h1>
        <p class="lead opacity-75 mb-4">Browse our categories, explore photos and videos, and download full product details.</p>
        <a href="#categories" class="btn btn-light btn-lg text-primary fw-semibold px-4">Browse categories</a>
    </div>
</section>

<!-- Category grid -->
<section id="categories" class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Shop by category</h2>
            <p class="text-muted mb-0">Pick a category to see its products.</p>
        </div>
    </div>

    

    <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-3 row-cols-xl-4">
        @forelse($categories as $index => $category)
            @php
                $gradients = ['g1', 'g2', 'g3', 'g4', 'g5', 'g6'];
                $gradientClass = $gradients[$index % count($gradients)];
            @endphp



            <div class="col">
               <a href="{{ route('categories.show', $category->slug) }}" class="cat-card text-decoration-none d-block h-100 border rounded-4 overflow-hidden shadow-sm bg-white">
                    
                    @if($category->image)
<<div class="category-image-container w-100" style="height: 140px; overflow: hidden;">
        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
    </div>
                    @else
                        <div class="ph ph-cat {{ $gradientClass }} d-flex align-items-center justify-content-center text-white" style="height: 140px;">
                            <i class="bi bi-grid fs-2"></i>
                        </div>
                    @endif

                    <div class="p-3">
                        <h3 class="h6 fw-bold mb-1 text-dark">{{ $category->name }}</h3>
                        <p class="small text-muted mb-0">{{ $category->products_count ?? 0 }} products</p>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">No categories available right now.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection