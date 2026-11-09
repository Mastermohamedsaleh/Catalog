<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $category->name }} – CatalogPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<div id="toastTop" aria-live="polite"></div>

<!-- ============ Navbar ============ -->
<nav class="navbar navbar-expand-md site-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}"><i class="bi bi-grid-fill me-2"></i>CatalogPro</a>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto ms-md-3">
        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="{{ route('shop.categories.index') }}">Categories</a></li>
      </ul>
      <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->role === 'catalog_master_root' || auth()->user()->is_super || auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('merchant.orders.index') }}" class="btn btn-primary">Dashboard</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Sign in</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
        @endauth
      </div>
    </div>
  </div>
</nav>

<!-- Main content -->
<main class="container py-4 py-lg-5 flex-grow-1">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('shop.categories.index') }}">Categories</a></li>
      @if($category->parent)
          <li class="breadcrumb-item"><a href="{{ route('shop.category', $category->parent->slug) }}">{{ $category->parent->name }}</a></li>
      @endif
      <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
    </ol>
  </nav>

  <!-- Header & Search -->
  <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h2 fw-bold mb-1">{{ $category->name }}</h1>
        <p class="text-muted mb-0">Explore items in this category</p>
    </div>
    @if($category->children->count() == 0 && $products->count() > 0)
    <div class="d-flex gap-2">
      <div class="input-icon flex-grow-1">
          <i class="bi bi-search"></i>
          <input type="search" class="form-control" placeholder="Search products…" data-filter="#grid">
      </div>
    </div>
    @endif
  </header>

  <!-- العرض الذكي: لو فيه أقسام فرعية اعرضها، لو مفيش اعرض المنتجات مباشرة -->
  @if($category->children->count() > 0)
      <div class="mb-5">
          <h3 class="h5 fw-bold mb-3">الأقسام الفرعية</h3>
          <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-3 row-cols-xl-4">
              @foreach($category->children as $index => $subcat)
                  @php
                      $colors = ['g1', 'g2', 'g3', 'g4', 'g5', 'g6'];
                      $colorClass = $colors[$index % count($colors)];
                  @endphp
                  <div class="col">
                      <a href="{{ route('shop.category', $subcat->slug) }}" class="cat-card text-decoration-none">
                          @if($subcat->image)
                              <div class="position-relative overflow-hidden rounded-3 mb-2" style="height: 130px;">
                                  <img src="{{ asset('storage/' . $subcat->image) }}" alt="{{ $subcat->name }}" class="w-100 h-100" style="object-fit: cover;">
                              </div>
                          @else
                              <div class="ph ph-cat {{ $colorClass }} rounded-3 mb-2 d-flex align-items-center justify-content-center text-white" style="height: 130px;">
                                  <i class="bi bi-grid-fill fs-2"></i>
                              </div>
                          @endif
                          <div class="p-2">
                              <h4 class="h6 fw-bold mb-1 text-dark">{{ $subcat->name }}</h4>
                              <p class="small text-muted mb-0">{{ $subcat->products_count ?? 0 }} products</p>
                          </div>
                      </a>
                  </div>
              @endforeach
          </div>
      </div>
  @else
      @if($products->count() > 0)
          <div class="row g-3 g-lg-4 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4" id="grid">
              @foreach($products as $index => $p)
                  @php
                      $colors = ['g1', 'g2', 'g3', 'g4', 'g5', 'g6'];
                      $colorClass = $colors[$index % count($colors)];
                  @endphp
                  <div class="col product-item">
                      <a href="{{ route('shop.product.show', $p->id) }}" class="prod-card text-decoration-none">
                          @if($p->main_image)
                              <div class="card-img-top position-relative overflow-hidden rounded-3 mb-2" style="aspect-ratio: 4/3;">
                                  <img src="{{ asset('storage/' . $p->main_image) }}" alt="{{ $p->name }}" class="w-100 h-100" style="object-fit: cover;">
                              </div>
                          @else
                              <div class="ph ph-prod {{ $colorClass }} rounded-3 mb-2 d-flex align-items-center justify-content-center text-white" style="aspect-ratio: 4/3;"><i class="bi bi-box-seam fs-2"></i></div>
                          @endif

                          <div class="p-2">
                              <span class="badge badge-soft-primary mb-2">{{ $category->name }}</span>
                              <h2 class="h6 fw-bold mb-1 text-dark">{{ $p->name }}</h2>
                              <p class="small text-muted mb-2 text-truncate">{{ $p->description ?? 'No description available.' }}</p>
                              <div class="price fw-bold text-primary">{{ $p->price }} EGP</div>
                          </div>
                      </a>
                  </div>
              @endforeach
          </div>

          <!-- Pagination -->
          @if($products->hasPages())
              <nav class="mt-5 d-flex justify-content-center" aria-label="Pagination">
                  {{ $products->appends(request()->query())->links() }}
              </nav>
          @endif
      @else
          <div class="text-center py-5">
              <p class="text-muted fs-5">لا توجد أقسام فرعية أو منتجات متاحة في هذا القسم حالياً.</p>
          </div>
      @endif
  @endif
</main>

<!-- ============ Footer ============ -->
<footer class="site-footer pt-5 pb-4 mt-auto">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-4"><div class="fs-5 fw-bold text-white mb-2"><i class="bi bi-grid-fill me-2"></i>CatalogPro</div><p class="small mb-0">Quality products, honest prices and real support since 2015.</p></div>
      <div class="col-6 col-lg-2"><div class="text-white fw-semibold mb-2">Links</div><ul class="list-unstyled small mb-0"><li><a class="text-reset" href="{{ url('/') }}#about">About us</a></li><li><a class="text-reset" href="{{ url('/') }}#services">What we offer</a></li><li><a class="text-reset" href="{{ route('shop.categories.index') }}">Categories</a></li><li><a class="text-reset" href="{{ url('/') }}#contact">Contact</a></li></ul></div>
      <div class="col-6 col-lg-3"><div class="text-white fw-semibold mb-2">Contact</div><ul class="list-unstyled small mb-0"><li><i class="bi bi-telephone me-2">EN</i>+20 100 000 0000</li><li><i class="bi bi-envelope me-2"></i>info@catalogpro.com</li><li><i class="bi bi-geo-alt me-2"></i>Nasr City, Cairo</li></ul></div>
      <div class="col-lg-3"><div class="text-white fw-semibold mb-2">Follow us</div><div class="d-flex gap-3 fs-5"><a class="text-reset" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a><a class="text-reset" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a><a class="text-reset" href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a></div></div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  'use strict';
  document.querySelectorAll('[data-filter]').forEach(function (inp) {
    inp.addEventListener('input', function () {
      var q = inp.value.toLowerCase();
      var s = inp.dataset.filter;
      var items = document.querySelectorAll(s + ' > .product-item');

      items.forEach(function (el) {
        var match = el.textContent.toLowerCase().indexOf(q) > -1;
        el.hidden = !match;
      });
    });
  });
})();
</script>
</body>
</html>