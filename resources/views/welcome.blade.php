<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CatalogPro – Quality products, trusted service</title>
  <meta name="description" content="CatalogPro: browse our product categories, download full details, and contact us directly.">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- ربط ملف الـ CSS الخاص بالمشروع بالطريقة الصحيحة في لارافيل -->
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
<div id="toastTop" aria-live="polite"></div>

<!-- ============ Navbar ============ -->
<nav class="navbar navbar-expand-lg site-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}"><i class="bi bi-grid-fill me-2"></i>CatalogPro</a>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto ms-lg-3">
        <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About us</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">What we offer</a></li>
        <li class="nav-item"><a class="nav-link" href="#categories">Categories</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact &amp; location</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('merchant.orders.index') }}">Create New Order</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
        <a href="tel:+201000000000" class="btn btn-light btn-sm d-none d-xl-inline-flex gap-1"><i class="bi bi-telephone-fill text-primary"></i>+20 100 000 0000</a>
        
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

<main>
  <!-- ============ HERO ============ -->
  <section id="home" class="container pt-4 pt-lg-5">
    <div class="hero hero-lg">
      <div class="row align-items-center g-4 position-relative" style="z-index:1">
        <div class="col-lg-7">
          <span class="badge rounded-pill text-bg-light text-primary mb-3 px-3 py-2"><i class="bi bi-patch-check-fill me-1"></i>Trusted by 1,000+ customers</span>
          <h1 class="display-4 fw-bold mb-3">Quality products. Honest prices. Real support.</h1>
          <p class="lead opacity-75 mb-4">We supply a wide range of products for homes and businesses, with full details, photos and videos for every item, so you can choose with confidence.</p>
          <div class="d-flex flex-wrap gap-2">
            <a href="#categories" class="btn btn-light btn-lg text-primary fw-semibold px-4">Browse products</a>
            <a href="tel:+201000000000" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-telephone me-2"></i>Call us now</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="row g-3">
            <div class="col-6"><div class="hero-stat"><div class="h2 fw-bold mb-0">250+</div><div class="small opacity-75">Products</div></div></div>
            <div class="col-6"><div class="hero-stat"><div class="h2 fw-bold mb-0">12</div><div class="small opacity-75">Categories</div></div></div>
            <div class="col-6"><div class="hero-stat"><div class="h2 fw-bold mb-0">10+</div><div class="small opacity-75">Years of experience</div></div></div>
            <div class="col-6"><div class="hero-stat"><div class="h2 fw-bold mb-0">4.9<i class="bi bi-star-fill fs-6 ms-1"></i></div><div class="small opacity-75">Customer rating</div></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ ABOUT ============ -->
  <section id="about" class="container sec">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        <div class="about-visual ph g1"><i class="bi bi-buildings"></i>
          <div class="about-badge"><span class="fw-bold text-primary">Since 2015</span><br><span class="small text-muted">Serving customers across Egypt</span></div>
        </div>
      </div>
      <div class="col-lg-6">
        <span class="eyebrow">Who we are</span>
        <h2 class="fw-bold mb-3">A local team focused on quality and service</h2>
        <p class="text-muted">CatalogPro is a product supplier built on one idea: make buying simple. We carefully select every product, publish complete specifications, and stand behind what we sell with fast delivery and after-sales support.</p>
        <p class="text-muted mb-4">Whether you are furnishing a home or equipping a business, our team helps you find the right product at the right price.</p>
        <div class="row g-3">
          <div class="col-sm-6 d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="small fw-semibold">Original, quality-checked products</span></div>
          <div class="col-sm-6 d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="small fw-semibold">Clear prices, no hidden fees</span></div>
          <div class="col-sm-6 d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="small fw-semibold">Friendly, knowledgeable team</span></div>
          <div class="col-sm-6 d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span class="small fw-semibold">Warranty on every item</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ WHAT WE OFFER ============ -->
  <section id="services" class="container sec pt-0">
    <div class="text-center mb-5">
      <span class="eyebrow">What we offer</span>
      <h2 class="fw-bold mb-2">Everything you need, in one place</h2>
      <p class="text-muted mb-0 mx-auto" style="max-width:560px">From a single item to bulk orders, we cover it all.</p>
    </div>
    <div class="row g-3 g-lg-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-box-seam"></i></div><h3 class="h5 fw-bold">Wide product range</h3><p class="text-muted small mb-0">Hundreds of products across many categories, updated regularly.</p></div></div>
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-cart-check"></i></div><h3 class="h5 fw-bold">Retail &amp; wholesale</h3><p class="text-muted small mb-0">Special pricing for businesses and bulk orders.</p></div></div>
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-truck"></i></div><h3 class="h5 fw-bold">Fast delivery</h3><p class="text-muted small mb-0">Reliable delivery to your door, with order tracking.</p></div></div>
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-file-earmark-arrow-down"></i></div><h3 class="h5 fw-bold">Downloadable details</h3><p class="text-muted small mb-0">Get full specification sheets for any product in one click.</p></div></div>
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-shield-check"></i></div><h3 class="h5 fw-bold">Warranty &amp; returns</h3><p class="text-muted small mb-0">Peace of mind with warranty cover and easy returns.</p></div></div>
      <div class="col"><div class="feature-card"><div class="feature-icon"><i class="bi bi-headset"></i></div><h3 class="h5 fw-bold">Expert support</h3><p class="text-muted small mb-0">Call or message us and get advice from real people.</p></div></div>
    </div>
  </section>

  <!-- ============ CATEGORIES (Dynamic from Database - Max 8) ============ -->
  <section id="categories" class="container sec pt-0">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
      <div>
        <span class="eyebrow">Our products</span>
        <h2 class="fw-bold mb-1">Shop by category</h2>
        <p class="text-muted mb-0">Pick a category to see its products.</p>
      </div>
      <!-- زرار الانتقال لصفحة عرض كل الأقسام -->
      <a href="{{ route('shop.categories.index') }}" class="btn btn-outline-primary">View all categories <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-3 row-cols-xl-4">
      @forelse($categories as $index => $c)
          @php
              $colors = ['g1', 'g2', 'g3', 'g4', 'g5', 'g6'];
              $colorClass = $colors[$index % count($colors)];
          @endphp
          <div class="col">
              <a href="{{ route('shop.category', $c->slug) }}" class="cat-card">
                  @if($c->image)
                      <!-- عرض صورة القسم الحقيقية لو مرفوعة -->
                      <div class="position-relative overflow-hidden" style="height: 130px;">
                          <img src="{{ asset('storage/' . $c->image) }}" alt="{{ $c->name }}" class="w-100 h-100" style="object-fit: cover;">
                      </div>
                  @else
                      <!-- التصميم الافتراضي (Gradient Placeholder) لو مفيش صورة -->
                      <div class="ph ph-cat {{ $colorClass }}">
                          <i class="bi bi-grid-fill"></i>
                      </div>
                  @endif

                  <div class="p-3">
                      <h3 class="h6 fw-bold mb-1">{{ $c->name }}</h3>
                      <p class="small text-muted mb-0">{{ $c->products_count }} products</p>
                  </div>
              </a>
          </div>
      @empty
          <div class="col-12 text-center py-4">
              <p class="text-muted">لا توجد أقسام مضافة حالياً في قاعدة البيانات.</p>
          </div>
      @endforelse
    </div>
  </section>

  <!-- ============ STATS BAND ============ -->
  <section class="container sec pt-0">
    <div class="band p-4 p-lg-5">
      <div class="row g-4 text-center">
        <div class="col-6 col-lg-3"><div class="n">10+</div><div class="small opacity-75">Years in business</div></div>
        <div class="col-6 col-lg-3"><div class="n">1,000+</div><div class="small opacity-75">Happy customers</div></div>
        <div class="col-6 col-lg-3"><div class="n">250+</div><div class="small opacity-75">Products available</div></div>
        <div class="col-6 col-lg-3"><div class="n">24h</div><div class="small opacity-75">Average reply time</div></div>
      </div>
    </div>
  </section>

  <!-- ============ HOW IT WORKS ============ -->
  <section class="container sec pt-0">
    <div class="text-center mb-5"><span class="eyebrow">How it works</span><h2 class="fw-bold">Ordering in 3 easy steps</h2></div>
    <div class="row g-4">
      <div class="col-md-4"><div class="feature-card"><div class="step-n">1</div><h3 class="h5 fw-bold">Browse</h3><p class="text-muted small mb-0">Explore categories and open any product for photos, videos and specs.</p></div></div>
      <div class="col-md-4"><div class="feature-card"><div class="step-n">2</div><h3 class="h5 fw-bold">Contact us</h3><p class="text-muted small mb-0">Call, WhatsApp or send a message to confirm availability and price.</p></div></div>
      <div class="col-md-4"><div class="feature-card"><div class="step-n">3</div><h3 class="h5 fw-bold">Receive</h3><p class="text-muted small mb-0">Pick up from our store or get it delivered to your door.</p></div></div>
    </div>
  </section>

  <!-- ============ TESTIMONIALS ============ -->
  <section class="container sec pt-0">
    <div class="text-center mb-5"><span class="eyebrow">Testimonials</span><h2 class="fw-bold">What our customers say</h2></div>
    <div class="row g-3 g-lg-4">
      <div class="col-md-4"><div class="feature-card"><div class="text-warning mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div><p class="small text-muted">"Great quality and the team answered all my questions quickly. Delivery was on time."</p><div class="fw-semibold small">Ahmed M. <span class="text-muted fw-normal">· Cairo</span></div></div></div>
      <div class="col-md-4"><div class="feature-card"><div class="text-warning mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div><p class="small text-muted">"The downloadable spec sheets made it easy to compare products for our office."</p><div class="fw-semibold small">Sara K. <span class="text-muted fw-normal">· Giza</span></div></div></div>
      <div class="col-md-4"><div class="feature-card"><div class="text-warning mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i></div><p class="small text-muted">"Fair prices and honest advice. I will definitely order again."</p><div class="fw-semibold small">Omar H. <span class="text-muted fw-normal">· Alexandria</span></div></div></div>
    </div>
  </section>

  <!-- ============ CONTACT & LOCATION ============ -->
  <section id="contact" class="container sec pt-0">
    <div class="text-center mb-5"><span class="eyebrow">Contact &amp; location</span><h2 class="fw-bold mb-2">Visit us or get in touch</h2><p class="text-muted mb-0">We would love to hear from you.</p></div>
    <div class="row g-4">
      <div class="col-lg-5 d-flex flex-column gap-3">
        <a class="contact-item" href="tel:+201000000000"><div class="feature-icon"><i class="bi bi-telephone-fill"></i></div><div><div class="fw-bold">Phone</div><div class="text-muted small">+20 100 000 0000</div></div></a>
        <a class="contact-item" href="https://wa.me/201000000000" target="_blank" rel="noopener"><div class="feature-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-whatsapp"></i></div><div><div class="fw-bold">WhatsApp</div><div class="text-muted small">Chat with us instantly</div></div></a>
        <a class="contact-item" href="mailto:info@catalogpro.com"><div class="feature-icon"><i class="bi bi-envelope-fill"></i></div><div><div class="fw-bold">Email</div><div class="text-muted small">info@catalogpro.com</div></div></a>
        <div class="contact-item"><div class="feature-icon"><i class="bi bi-geo-alt-fill"></i></div><div><div class="fw-bold">Address</div><div class="text-muted small">123 Example Street, Nasr City, Cairo, Egypt</div></div></div>
        <div class="contact-item"><div class="feature-icon"><i class="bi bi-clock-fill"></i></div><div><div class="fw-bold">Working hours</div><div class="text-muted small">Sat – Thu: 9:00 AM – 6:00 PM<br>Friday: Closed</div></div></div>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <div class="col-12" style="min-height:300px">
            <iframe class="map-frame shadow-sm" title="Our location" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              src="https://maps.google.com/maps?q=Nasr%20City%2C%20Cairo%2C%20Egypt&output=embed"></iframe>
          </div>
          <div class="col-12">
            <form class="card-soft p-3 p-lg-4 needs-validation" data-success="Message sent. We will contact you soon.">
              <h3 class="h5 fw-bold mb-3">Send us a message</h3>
              <div class="row g-3">
                <div class="col-sm-6"><label class="form-label small fw-semibold" for="cName">Name</label><input class="form-control" id="cName" required minlength="2" placeholder="Your name"><div class="invalid-feedback">Please enter your name.</div></div>
                <div class="col-sm-6"><label class="form-label small fw-semibold" for="cPhone">Phone</label><input class="form-control" id="cPhone" type="tel" required pattern="[0-9+\s\-]{8,}" placeholder="+20 1XX XXX XXXX"><div class="invalid-feedback">Enter a valid phone number.</div></div>
                <div class="col-12"><label class="form-label small fw-semibold" for="cMsg">Message</label><textarea class="form-control" id="cMsg" rows="3" required placeholder="How can we help?"></textarea><div class="invalid-feedback">Please write a message.</div></div>
                <div class="col-12"><button class="btn btn-primary px-4" type="submit"><i class="bi bi-send me-2"></i>Send message</button></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- ============ Footer ============ -->
<footer class="site-footer pt-5 pb-4">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-4"><div class="fs-5 fw-bold text-white mb-2"><i class="bi bi-grid-fill me-2"></i>CatalogPro</div><p class="small mb-0">Quality products, honest prices and real support since 2015.</p></div>
      <div class="col-6 col-lg-2"><div class="text-white fw-semibold mb-2">Links</div><ul class="list-unstyled small mb-0"><li><a class="text-reset" href="#about">About us</a></li><li><a class="text-reset" href="#services">What we offer</a></li><li><a class="text-reset" href="#categories">Categories</a></li><li><a class="text-reset" href="#contact">Contact</a></li></ul></div>
      <div class="col-6 col-lg-3"><div class="text-white fw-semibold mb-2">Contact</div><ul class="list-unstyled small mb-0"><li><i class="bi bi-telephone me-2"></i>+20 100 000 0000</li><li><i class="bi bi-envelope me-2"></i>info@catalogpro.com</li><li><i class="bi bi-geo-alt me-2"></i>Nasr City, Cairo</li></ul></div>
      <div class="col-lg-3"><div class="text-white fw-semibold mb-2">Follow us</div><div class="d-flex gap-3 fs-5"><a class="text-reset" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a><a class="text-reset" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a><a class="text-reset" href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a></div></div>
    </div>
  </div>
</footer>

<a class="wa-float" href="https://wa.me/201000000000" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- ربط ملف الـ JS الخاص بالمشروع بطريقة لارافيل -->
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>