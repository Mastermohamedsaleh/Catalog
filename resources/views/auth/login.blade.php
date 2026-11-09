<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · CatalogPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --brand: #4f46e5;
      --brand-dark: #4338ca;
      --brand-soft: #eef2ff;
      --ink: #0f172a;
      --muted: #64748b;
      --canvas: #f4f6fb;
      --sidebar: #0f172a;
      --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .06);
      --shadow-md: 0 8px 24px rgba(15, 23, 42, .08);
      --bs-primary: var(--brand);
    }
    body { background: var(--canvas); color: var(--ink); -webkit-font-smoothing: antialiased; }
    a { text-decoration: none; }
    .btn-primary { background-color: var(--brand); border-color: var(--brand); }
    .btn-primary:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); }
    .auth-wrap { min-height: 100vh; }
    .auth-hero { background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 60%, #7c3aed 100%); color: #fff; position: relative; overflow: hidden; }
    .auth-card { max-width: 420px; width: 100%; }
    .input-icon { position: relative; }
    .input-icon > .bi { position: absolute; left: .95rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .input-icon .form-control { padding-left: 2.6rem; border-radius: .65rem; padding-top: .65rem; padding-bottom: .65rem; border-color: #e2e8f0; }
    .input-icon .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 .22rem rgba(79, 70, 229, .15); }
    .input-icon .toggle-pass { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); border: none; background: none; }
  </style>
</head>
<body>

<main class="row g-0 auth-wrap">
  <!-- Left: Brand Panel -->
  <section class="col-lg-6 d-none d-lg-flex auth-hero align-items-center p-5">
    <div class="position-relative" style="z-index:1;max-width:460px">
      <div class="fs-3 fw-bold mb-4"><i class="bi bi-grid-fill me-2"></i>CatalogPro</div>
      <h1 class="display-6 fw-bold mb-3">Manage your catalog with confidence.</h1>
      <p class="opacity-75 mb-4">Organize categories, publish products with rich media, and let customers download full details in one click.</p>
      <ul class="list-unstyled opacity-75 mb-0">
        <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Categories &amp; products in minutes</li>
        <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Images, video &amp; downloadable files</li>
        <li><i class="bi bi-check2-circle me-2"></i>Fast, responsive public storefront</li>
      </ul>
    </div>
  </section>

  <!-- Right: Login Form -->
  <section class="col-lg-6 d-flex align-items-center justify-content-center p-4 bg-white">
    <div class="auth-card">
      <div class="d-lg-none fs-4 fw-bold text-primary mb-4"><i class="bi bi-grid-fill me-2"></i>CatalogPro</div>
      <h2 class="fw-bold mb-1">Welcome back 👋</h2>
    

      <!-- Session Status (e.g. Password reset status) -->
      @if (session('status'))
          <div class="alert alert-success small mb-3" role="alert">
              {{ session('status') }}
          </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
          <label for="email" class="form-label small fw-semibold">Email</label>
          <div class="input-icon">
            <i class="bi bi-envelope"></i>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <label for="password" class="form-label small fw-semibold">Password</label>

          </div>
          <div class="input-icon">
            <i class="bi bi-lock"></i>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="btn btn-sm text-muted toggle-pass" aria-label="Toggle password"><i class="bi bi-eye"></i></button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="remember" name="remember">
          <label class="form-check-label small" for="remember">Remember me</label>
        </div>

        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
      </form>

      <p class="text-center text-muted small mt-4 mb-1">Don't have an account? <a href="{{ route('register') }}" class="fw-semibold">Create one</a></p>
      <p class="text-center small mb-0"><a href="/" class="text-muted"><i class="bi bi-arrow-left"></i> Back to website</a></p>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle password visibility script
  document.querySelectorAll('.toggle-pass').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = b.parentElement.querySelector('input');
      var show = i.type === 'password';
      i.type = show ? 'text' : 'password';
      b.firstElementChild.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');
    });
  });
</script>
</body>
</html>