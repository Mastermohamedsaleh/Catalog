<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Wallet · CatalogPro Merchant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

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
    <h1 class="h5 fw-bold mb-0 me-auto">My Wallet</h1>
    <div class="btn btn-light btn-sm d-flex align-items-center gap-2">
      <i class="bi bi-wallet2 text-primary"></i><span class="fw-semibold">{{ number_format($availableBalance, 2) }} EGP</span>
    </div>
  </header>

  <main class="p-3 p-lg-4 flex-grow-1">
    <!-- إحصائيات المحفظة -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Available Balance</div>
          <div class="h3 fw-bold text-success mb-0" id="wAvail">{{ number_format($availableBalance, 2) }} EGP</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Pending Profit</div>
          <div class="h3 fw-bold text-warning mb-0" id="wPending">{{ number_format($pendingProfit, 2) }} EGP</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Delivered Orders</div>
          <div class="h3 fw-bold mb-0" id="wDelivered">{{ $deliveredCount }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Cancelled / Returned</div>
          <div class="h3 fw-bold text-danger mb-0" id="wLost">{{ $lostCount }}</div>
        </div>
      </div>
    </div>

    <!-- جدول سجل الأرباح والمعاملات -->
    <div class="card-soft">
      <div class="p-3 p-lg-4 pb-2">
        <h2 class="h6 fw-bold mb-0">Wallet History &amp; Profits</h2>
      </div>
      <div class="table-responsive">
        <table class="table table-modern">
          <thead>
            <tr>
              <th>Date</th>
              <th>Order ID</th>
              <th>Product</th>
              <th class="d-none d-md-table-cell">Customer</th>
              <th>Status</th>
              <th>Wallet Status</th>
              <th class="text-end">Profit</th>
            </tr>
          </thead>
          <tbody id="walletBody">
            @forelse($orders as $o)
              @php
                $isLost = in_array($o->status, ['returned', 'cancelled']);
                $cls = $isLost ? 'text-muted text-decoration-line-through' : ($o->is_paid_to_wallet ? 'text-success' : 'text-warning-emphasis');
              @endphp
              <tr>
                <td class="text-muted small text-nowrap">{{ $o->created_at->format('Y-m-d') }}</td>
                <td class="fw-semibold">#{{ $o->id }}</td>
                <td>{{ $o->product->name ?? 'Deleted Product' }}</td>
                <td class="d-none d-md-table-cell">{{ $o->customer_name }}</td>
                <td>
                  @if($o->status == 'pending') <span class="badge badge-soft-warning">Pending</span>
                  @elseif($o->status == 'delivered') <span class="badge badge-soft-success">Delivered</span>
                  @elseif($o->status == 'shipped') <span class="badge bg-info-subtle text-info-emphasis">Shipped</span>
                  @elseif($o->status == 'returned') <span class="badge badge-soft-danger">Returned</span>
                  @else <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ ucfirst($o->status) }}</span>
                  @endif
                </td>
                <td>
                  @if($o->is_paid_to_wallet)
                    <span class="badge badge-soft-success"><i class="bi bi-check2-circle me-1"></i>Credited</span>
                  @elseif($isLost)
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">No profit</span>
                  @else
                    <span class="badge badge-soft-warning">Not yet</span>
                  @endif
                </td>
                <td class="text-end fw-semibold text-nowrap {{ $cls }}">+ {{ number_format($o->profit, 2) }} EGP</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No wallet transactions found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>