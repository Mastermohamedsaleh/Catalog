<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $product->name }} – CatalogPro</title>
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
            <a href="{{ route('admin.products.index') }}" class="btn btn-primary btn-sm">Dashboard</a>
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
      @if($product->category)
          <li class="breadcrumb-item"><a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a></li>
      @endif
      <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
    </ol>
  </nav>

  <div class="row g-4 g-lg-5">
    <!-- Gallery & Download Image -->
    <div class="col-lg-6">
      <div class="mb-3 border rounded-4 overflow-hidden shadow-sm bg-white text-center position-relative" style="height: 400px; cursor: zoom-in;" data-bs-toggle="modal" data-bs-target="#imageZoomModal">
          @if($product->main_image)
              <img src="{{ asset('storage/' . $product->main_image) }}" class="w-100 h-100" style="object-fit: contain;" id="mainProductImage">
          @else
              <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light"><i class="bi bi-box-seam display-1 text-muted"></i></div>
          @endif
      </div>

      <!-- زر تحميل الصورة الرئيسية (بنفس تصميم زرار الواتساب) -->
      @if($product->main_image)
      <div class="mb-3">
          <a href="{{ asset('storage/' . $product->main_image) }}" download class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
              <i class="bi bi-download me-2"></i> Download Image
          </a>
      </div>
      @endif

      <!-- Thumbnails -->
      @if($product->images->count() > 0)
          <div class="d-flex gap-2 mt-3 flex-wrap">
              @if($product->main_image)
                  <div class="border rounded p-1 bg-white cursor-pointer gallery-thumb active" style="width: 78px; height: 78px;" onclick="changeImage('{{ asset('storage/' . $product->main_image) }}')">
                      <img src="{{ asset('storage/' . $product->main_image) }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                  </div>
              @endif
              @foreach($product->images as $img)
                  <div class="border rounded p-1 bg-white cursor-pointer gallery-thumb" style="width: 78px; height: 78px;" onclick="changeImage('{{ asset('storage/' . $img->image_path) }}')">
                      <img src="{{ asset('storage/' . $img->image_path) }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                  </div>
              @endforeach
          </div>
      @endif
    </div>

    <!-- Info -->
    <div class="col-lg-6">
      @if($product->category)
          <a href="{{ route('shop.category', $product->category->slug) }}" class="badge badge-soft-primary mb-3">{{ $product->category->name }}</a>
      @endif
      <h1 class="fw-bold mb-2">{{ $product->name }}</h1>
      <div class="h2 price mb-3">{{ $product->price }} EGP</div>
      <p class="text-muted">{{ $product->description ?? 'No description available for this product.' }}</p>

      <!-- عرض المقاسات والمخزون باللغة الإنجليزية -->
      @if($product->variants && $product->variants->count() > 0)
          <div class="mb-4 p-3 bg-light rounded-4 border">
              <label class="form-label small fw-bold mb-2 text-dark"><i class="bi bi-tags-fill me-1 text-primary"></i> Sizes & Available Stock:</label>
              <div class="d-flex flex-wrap gap-2">
                  @foreach($product->variants as $variant)
                      <div class="badge bg-white text-dark border p-2 shadow-sm d-flex align-items-center gap-1">
                          <span class="fw-bold text-primary">Size: {{ $variant->size }}</span> 
                          @if($variant->color)
                              <span class="text-muted">| Color: {{ $variant->color }}</span>
                          @endif
                          <span class="ms-1 badge {{ $variant->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                              {{ $variant->stock }} available
                          </span>
                      </div>
                  @endforeach
              </div>
          </div>
      @endif

      <ul class="list-unstyled small mb-4">
        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Total Stock: {{ $product->stock ?? 0 }} available</li>
        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Original, quality-checked product</li>
        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Fast delivery &amp; support</li>
      </ul>

      <div class="d-grid gap-2">
        <a href="https://wa.me/201000000000?text={{ urlencode('مرحباً، أهتم بالاستفسار عن المنتج: ' . $product->name) }}" target="_blank" class="btn btn-primary btn-lg fw-semibold shadow-sm">
          <i class="bi bi-whatsapp me-2"></i> Inquire via WhatsApp
        </a>
      </div>
    </div>
  </div>

  <!-- Videos & Download Video -->
  @if($product->video_url)
  <section class="mt-5">
    <h2 class="h4 fw-bold mb-3">Product video</h2>
    <div class="row g-3">
      <div class="col-md-8">
        <video width="100%" controls class="rounded-4 shadow-sm border mb-3" style="max-height: 350px;" id="productVideo">
            <source src="{{ asset('storage/' . $product->video_url) }}" type="video/mp4">
            متصفحك لا يدعم عرض الفيديو.
        </video>
        <!-- زر تحميل الفيديو (نفس تصميم زرار الواتساب) -->
        <a href="{{ asset('storage/' . $product->video_url) }}" download class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
            <i class="bi bi-download me-2"></i> Download Video
        </a>
      </div>
    </div>
  </section>
  @endif
</main>

<!-- Modal for Image Zoom -->
@if($product->main_image)
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="{{ asset('storage/' . $product->main_image) }}" class="img-fluid rounded-4 shadow-lg" id="modalZoomImage" style="max-height: 85vh;">
      </div>
    </div>
  </div>
</div>
@endif

<!-- ============ Footer ============ -->
<footer class="site-footer pt-5 pb-4 mt-auto">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-4"><div class="fs-5 fw-bold text-white mb-2"><i class="bi bi-grid-fill me-2"></i>CatalogPro</div><p class="small mb-0">Quality products, honest prices and real support since 2015.</p></div>
      <div class="col-6 col-lg-2"><div class="text-white fw-semibold mb-2">Links</div><ul class="list-unstyled small mb-0"><li><a class="text-reset" href="{{ url('/') }}#about">About us</a></li><li><a class="text-reset" href="{{ url('/') }}#services">What we offer</a></li><li><a class="text-reset" href="{{ route('shop.categories.index') }}">Categories</a></li><li><a class="text-reset" href="{{ url('/') }}#contact">Contact</a></li></ul></div>
      <div class="col-6 col-lg-3"><div class="text-white fw-semibold mb-2">Contact</div><ul class="list-unstyled small mb-0"><li><i class="bi bi-telephone me-2"></i>+20 100 000 0000</li><li><i class="bi bi-envelope me-2"></i>info@catalogpro.com</li><li><i class="bi bi-geo-alt me-2"></i>Nasr City, Cairo</li></ul></div>
      <div class="col-lg-3"><div class="text-white fw-semibold mb-2">Follow us</div><div class="d-flex gap-3 fs-5"><a class="text-reset" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a><a class="text-reset" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a><a class="text-reset" href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a></div></div>
    </div>

  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function changeImage(src) {
        document.getElementById('mainProductImage').src = src;
        var modalImg = document.getElementById('modalZoomImage');
        if(modalImg) modalImg.src = src;
    }
</script>
</body>
</html>