<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My wallet · CatalogPro Merchant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<div id="toastTop" aria-live="polite"></div>

<!-- ============ Sidebar ============ -->
<aside class="sidebar" id="sidebar">
  <a href="{{ route('merchant.orders.index') }}" class="brand"><i class="bi bi-grid-fill me-2"></i>CatalogPro</a>
  <nav class="nav flex-column flex-grow-1 overflow-auto">
    <div class="nav-label">Merchant</div>
    <a class="nav-link" href="{{ route('merchant.orders.index') }}"><i class="bi bi-cart-plus"></i>New order</a>
    <a class="nav-link active" href="{{ route('merchant.wallet') }}"><i class="bi bi-wallet2"></i>My wallet</a>
    <div class="nav-label">Site</div>
    <a class="nav-link" href="{{ url('/') }}" target="_blank"><i class="bi bi-globe2"></i>View catalog</a>
  </nav>
  <div class="p-3 border-top border-secondary-subtle">
      <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="nav-link m-0 bg-transparent border-0 text-danger w-100 text-start">
              <i class="bi bi-box-arrow-left me-2"></i>Log out
          </button>
      </form>
  </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-toggle></div>

<div class="main">
  <!-- ============ Topbar ============ -->
  <header class="topbar d-flex align-items-center gap-3 px-3 px-lg-4 py-2">
    <button class="btn btn-light d-lg-none" data-sidebar-toggle aria-label="Menu"><i class="bi bi-list fs-5"></i></button>
    <h1 class="h5 fw-bold mb-0 me-auto">My wallet</h1>
    <a href="{{ route('merchant.orders.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">New order</span></a>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center gap-2 text-dark" data-bs-toggle="dropdown">
        <span class="thumb g3" style="width:36px;height:36px;border-radius:50%">{{ substr(Auth::user()->name, 0, 1) }}</span>
        <span class="d-none d-sm-block small fw-semibold">{{ Auth::user()->name }}</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-left me-2"></i>Log out</button>
            </form>
        </li>
      </ul>
    </div>
  </header>

  <main class="p-3 p-lg-4 flex-grow-1">
    <div class="row g-3 g-lg-4 mb-4">
      <!-- Main balance card -->
      <div class="col-lg-5">
        <div class="hero p-4 h-100 d-flex flex-column justify-content-between" style="border-radius:1.25rem;min-height:190px">
          <div class="position-relative" style="z-index:1">
            <div class="d-flex align-items-center gap-2 opacity-75 mb-2"><i class="bi bi-wallet2"></i><span class="small">Available balance</span></div>
            <!-- عرض الرصيد المحسوب بدقة (الـ 100 جنيه) -->
            <div class="display-5 fw-bold" id="wAvail">{{ $merchant->calculated_wallet ?? ($merchant->wallet ?? 0) }} EGP</div>
          </div>
          <div class="position-relative small opacity-75" style="z-index:1"><i class="bi bi-info-circle me-1"></i>Profit is added to your wallet after the order is delivered and confirmed by admin.</div>
        </div>
      </div>
      <!-- Small stats -->
      <div class="col-lg-7">
        <div class="row g-3 h-100">
          <!-- Pending profit -->
          <div class="col-sm-6">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3 h-100">
              <div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-hourglass-split"></i></div>
              <div><div class="text-muted small">Pending profit</div><div class="h4 fw-bold mb-0" id="wPending">{{ $pendingProfit }} EGP</div></div>
            </div>
          </div>
          <!-- Delivered orders -->
          <div class="col-sm-6">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3 h-100">
              <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-box2-heart"></i></div>
              <div><div class="text-muted small">Delivered orders</div><div class="h4 fw-bold mb-0" id="wDelivered">{{ $deliveredCount }}</div></div>
            </div>
          </div>
          <!-- Returned / cancelled -->
          <div class="col-sm-6">
            <div class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3 h-100">
              <div class="stat-icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-arrow-return-left"></i></div>
              <div><div class="text-muted small">Returned / cancelled</div><div class="h4 fw-bold mb-0" id="wLost">{{ $lostCount }}</div></div>
            </div>
          </div>
          <!-- Add new order shortcut -->
          <div class="col-sm-6">
            <a href="{{ route('merchant.orders.index') }}" class="card-soft stat-card p-3 p-lg-4 d-flex align-items-center gap-3 h-100 text-reset">
              <div class="stat-icon bg-brand-soft text-primary"><i class="bi bi-cart-plus"></i></div>
              <div><div class="fw-bold">Add a new order</div><div class="text-muted small">Send a customer to admin</div></div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Wallet activity: one row per order -->
    <div class="card-soft">
      <div class="p-3 p-lg-4 pb-2 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h2 class="h6 fw-bold mb-0">Wallet activity</h2>
        <div class="input-icon" style="min-width:240px"><i class="bi bi-search"></i><input type="search" class="form-control form-control-sm py-2" placeholder="Search…" data-filter="#walletTable"></div>
      </div>
      <div class="table-responsive">
        <table class="table table-modern" id="walletTable">
          <thead>
              <tr>
                  <th>Date</th>
                  <th>Order</th>
                  <th>Product</th>
                  <th class="d-none d-md-table-cell">Customer</th>
                  <th>Status</th>
                  <th>Wallet</th>
                  <th class="text-end">Profit</th>
              </tr>
          </thead>
          <tbody id="walletBody">
              @forelse($orders as $order)
                  <tr>
                      <td>{{ $order->created_at->format('Y-m-d') }}</td>
                      <td class="fw-semibold">#{{ $order->id }}</td>
                      <td>{{ $order->product->name ?? 'Deleted Product' }}</td>
                      <td class="d-none d-md-table-cell">{{ $order->customer_name }}</td>
                      <td>
                          @if($order->status == 'pending') <span class="badge badge-soft-warning text-dark">Pending</span>
                          @elseif($order->status == 'processing') <span class="badge badge-soft-info text-dark">Processing</span>
                          @elseif($order->status == 'shipped') <span class="badge badge-soft-primary text-dark">Shipped</span>
                          @elseif($order->status == 'delivered') <span class="badge badge-soft-success text-dark">Delivered</span>
                          @elseif($order->status == 'cancelled') <span class="badge badge-soft-danger text-dark">Cancelled</span>
                          @elseif($order->status == 'returned') <span class="badge badge-soft-secondary text-dark">Returned</span>
                          @else <span class="badge badge-soft-secondary">{{ ucfirst($order->status) }}</span>
                          @endif
                      </td>
                      <td>
                          @if($order->is_paid_to_wallet)
                              <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                          @else
                              <span class="text-muted small">Pending</span>
                          @endif
                      </td>
                      <td class="text-end fw-bold text-success">+{{ $order->profit }} EGP</td>
                  </tr>
              @empty
                  <tr>
                      <td colspan="7" class="text-center text-muted py-4">No wallet activity found yet.</td>
                  </tr>
              @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- ============ Scripts ============ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>