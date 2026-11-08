<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New order · CatalogPro Merchant</title>
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
    <a class="nav-link active" href="{{ route('merchant.orders.index') }}"><i class="bi bi-cart-plus"></i>New order</a>
    <a class="nav-link" href="{{ route('merchant.wallet') }}"><i class="bi bi-wallet2"></i>My wallet</a>
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
    <h1 class="h5 fw-bold mb-0 me-auto">New order</h1>
    <a href="{{ route('merchant.wallet') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2" title="Wallet balance">
      <i class="bi bi-wallet2 text-primary"></i><span class="fw-semibold">{{ Auth::user()->wallet ?? 0 }} EGP</span>
    </a>
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
    <!-- الأخطاء والرسائل -->
    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mb-4 small">{{ session('success') }}</div>
    @endif

    <!-- ============ ORDER FORM ============ -->
    <form action="{{ route('merchant.orders.store') }}" method="POST" id="orderForm" class="card-soft p-3 p-lg-4 mb-4 needs-validation" novalidate>
      @csrf
      <div class="d-flex align-items-center gap-2 mb-1"><i class="bi bi-person-lines-fill text-primary fs-5"></i><h2 class="h5 fw-bold mb-0">Customer &amp; order details</h2></div>
      <p class="text-muted small mb-4">Fill in your customer's data. The order is sent to the admin for confirmation.</p>

      <div class="row g-3">
        <!-- البحث عن المنتج (Live Search) -->
        <div class="col-md-6 position-relative">
          <label for="productSearch" class="form-label small fw-semibold">Search Product <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="productSearch" placeholder="Type product name..." autocomplete="off" required>
          <input type="hidden" id="productId" name="product_id" required>
          <div id="searchResults" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
          <div class="invalid-feedback">Please choose a valid product from the list.</div>
        </div>

        <!-- اختيار المقاس (يظهر فقط لو المنتج له Variants) -->
        <div class="col-md-6" id="variantContainer" style="display: none;">
          <label for="variantSelect" class="form-label small fw-semibold">Select Size / Variant <span class="text-danger">*</span></label>
          <select class="form-select" id="variantSelect" name="product_variant_id">
              <option value="">Choose size first...</option>
          </select>
        </div>

        <!-- الكمية (Quantity) -->
        <div class="col-6 col-md-3">
          <label for="qty" class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="qty" name="quantity" min="1" step="1" value="1" required>
          <div class="form-text" id="stockHint">Select a product first</div>
          <div class="invalid-feedback">Enter a valid quantity within available stock.</div>
        </div>

        <!-- سعر المنتج الأساسي (التكلفة - Readonly) -->
        <div class="col-6 col-md-3">
          <label for="productPrice" class="form-label small fw-semibold">Product price <span class="text-muted fw-normal">(cost)</span></label>
          <div class="input-group"><input type="number" class="form-control bg-light" id="productPrice" name="product_price" readonly tabindex="-1" placeholder="0"><span class="input-group-text">EGP</span></div>
        </div>

        <!-- سعر البيع للعميل (Selling Price) -->
        <div class="col-md-4">
          <label for="sellingPrice" class="form-label small fw-semibold">Selling price <span class="text-muted fw-normal">(per item)</span> <span class="text-danger">*</span></label>
          <div class="input-group"><input type="number" class="form-control" id="sellingPrice" name="selling_price" min="0" step="0.01" required placeholder="0.00"><span class="input-group-text">EGP</span></div>
          <div class="invalid-feedback">Selling price must be at least the product price.</div>
        </div>

        <!-- الربح المتوقع (Auto Profit) -->
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Your profit <span class="text-muted fw-normal">(auto)</span></label>
          <div class="border rounded-3 px-3 py-2 bg-light d-flex align-items-center justify-content-between" style="min-height:44px">
            <span id="profitText" class="h4 fw-bold mb-0">—</span><i class="bi bi-graph-up-arrow text-success"></i>
          </div>
          <input type="hidden" id="profit" name="profit">
        </div>

        <!-- الحالة -->
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Status</label>
          <div class="border rounded-3 px-3 py-2 bg-light d-flex align-items-center gap-2" style="min-height:44px"><span class="badge badge-soft-warning">Pending</span><span class="small text-muted">Set by admin</span></div>
          <input type="hidden" name="status" value="pending">
          <input type="hidden" name="is_paid_to_wallet" value="0">
        </div>

        <!-- بيانات العميل -->
        <div class="col-md-6">
          <label for="customerName" class="form-label small fw-semibold">Customer name <span class="text-danger">*</span></label>
          <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control" id="customerName" name="customer_name" required minlength="3" placeholder="Full name"><div class="invalid-feedback">Enter customer name.</div></div>
        </div>
        <div class="col-md-6">
          <label for="customerPhone" class="form-label small fw-semibold">Customer phone <span class="text-danger">*</span></label>
          <div class="input-icon"><i class="bi bi-telephone"></i><input type="tel" class="form-control" id="customerPhone" name="customer_phone" required placeholder="01XXXXXXXXX"><div class="invalid-feedback">Enter valid phone number.</div></div>
        </div>
        <div class="col-12">
          <label for="customerAddress" class="form-label small fw-semibold">Customer address <span class="text-danger">*</span></label>
          <textarea class="form-control" id="customerAddress" name="customer_address" rows="2" required placeholder="Governorate, city, street, building…"></textarea>
          <div class="invalid-feedback">Enter delivery address.</div>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
          <button type="reset" class="btn btn-light">Clear</button>
          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-2"></i>Send order to admin</button>
        </div>
      </div>
    </form>

    <!-- ============ MY ORDERS TABLE ============ -->
    <div class="card-soft">
      <div class="p-3 p-lg-4 pb-2 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h2 class="h6 fw-bold mb-0">My orders <span class="badge badge-soft-primary ms-1" id="ordersCount">{{ $orders->count() }}</span></h2>
        <div class="input-icon" style="min-width:240px"><i class="bi bi-search"></i><input type="search" class="form-control form-control-sm py-2" placeholder="Search orders…" data-filter="#ordersTable"></div>
      </div>
      <div class="table-responsive">
        <table class="table table-modern" id="ordersTable">
          <thead>
              <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Customer</th>
                  <th class="d-none d-xxl-table-cell">Address</th>
                  <th>Cost</th>
                  <th>Selling</th>
                  <th>Qty</th>
                  <th>Profit</th>
                  <th>Status</th>
                  <th>Wallet</th>
              </tr>
          </thead>
          <tbody>
              @forelse($orders as $order)
                  <tr>
                      <td>{{ $order->id }}</td>
                      <td class="fw-semibold">{{ $order->product->name ?? 'Deleted Product' }}</td>
                      <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                      <td class="d-none d-xxl-table-cell small text-muted">{{ $order->customer_address }}</td>
                      <td>{{ $order->product_price }} EGP</td>
                      <td>{{ $order->selling_price }} EGP</td>
                      <td>{{ $order->quantity }}</td>
                      <td class="fw-bold text-success">+{{ $order->profit }} EGP</td>
                      <td>
                          @if($order->status == 'pending') <span class="badge badge-soft-warning">Pending</span>
                          @elseif($order->status == 'delivered') <span class="badge badge-soft-success">Delivered</span>
                          @else <span class="badge badge-soft-secondary">{{ ucfirst($order->status) }}</span>
                          @endif
                      </td>
                      <td>
                          @if($order->is_paid_to_wallet)
                              <i class="bi bi-check-circle-fill text-success"> Paid</i>
                          @else
                              <span class="text-muted small">Pending</span>
                          @endif
                      </td>
                  </tr>
              @empty
                  <tr>
                      <td colspan="10" class="text-center text-muted py-4">No orders placed yet.</td>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('productSearch');
    const resultsBox = document.getElementById('searchResults');
    const productIdInput = document.getElementById('productId');
    const productPriceInput = document.getElementById('productPrice');
    const qtyInput = document.getElementById('qty');
    const sellingPriceInput = document.getElementById('sellingPrice');
    const profitText = document.getElementById('profitText');
    const profitInput = document.getElementById('profit');
    const stockHint = document.getElementById('stockHint');
    const variantContainer = document.getElementById('variantContainer');
    const variantSelect = document.getElementById('variantSelect');

    // 1. البحث اللحظي عن المنتجات
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            let query = this.value.trim();
            if (query.length < 1) {
                resultsBox.style.display = 'none';
                return;
            }

            fetch(`/merchant/products-search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsBox.innerHTML = '';
                    if (data.length > 0) {
                        resultsBox.style.display = 'block';
                        data.forEach(product => {
                            let item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action small';
                            item.innerHTML = `<strong>${product.name}</strong> — <span class="text-primary">${product.price} EGP</span> (Stock: ${product.stock})`;
                            
                            item.addEventListener('click', function (e) {
                                e.preventDefault();
                                searchInput.value = product.name;
                                productIdInput.value = product.id;
                                productPriceInput.value = product.price;
                                
                                // معالجة المقاسات لو المنتج له Variants
                                variantSelect.innerHTML = '<option value="">Choose size/color...</option>';
                                if (product.variants && product.variants.length > 0) {
                                    variantContainer.style.display = 'block';
                                    variantSelect.setAttribute('required', 'required');
                                    product.variants.forEach(variant => {
                                        let opt = document.createElement('option');
                                        opt.value = variant.id;
                                        opt.textContent = `${variant.size} ${variant.color ? '(' + variant.color + ')' : ''} — Stock: ${variant.stock}`;
                                        opt.setAttribute('data-stock', variant.stock);
                                        variantSelect.appendChild(opt);
                                    });
                                    if(stockHint) stockHint.textContent = `Select a variant size`;
                                } else {
                                    variantContainer.style.display = 'none';
                                    variantSelect.removeAttribute('required');
                                    qtyInput.max = product.stock;
                                    if(stockHint) stockHint.textContent = `Available stock: ${product.stock}`;
                                }
                                
                                resultsBox.style.display = 'none';
                                calculateProfit();
                            });
                            resultsBox.appendChild(item);
                        });
                    } else {
                        resultsBox.style.display = 'none';
                    }
                });
        });
    }

    // 2. تحديث المخزون الأقصى عند اختيار مقاس معين (Variant)
    if (variantSelect) {
        variantSelect.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
            qtyInput.max = stock;
            if(stockHint) stockHint.textContent = `Available stock for size: ${stock}`;
            calculateProfit();
        });
    }

    // 3. حساب الربح التلقائي
    if (qtyInput || sellingPriceInput) {
        [qtyInput, sellingPriceInput].forEach(input => {
            if (input) input.addEventListener('input', calculateProfit);
        });
    }

    function calculateProfit() {
        const price = parseFloat(productPriceInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 1;
        const selling = parseFloat(sellingPriceInput.value) || 0;

        const totalCost = price * qty;
        const totalSelling = selling * qty;
        const totalProfit = totalSelling - totalCost;

        if (selling > 0 && totalProfit >= 0) {
            profitText.textContent = totalProfit.toFixed(2) + ' EGP';
            profitInput.value = totalProfit.toFixed(2);
            sellingPriceInput.setCustomValidity('');
        } else {
            profitText.textContent = '—';
            profitInput.value = '0';
            if (selling > 0) {
                sellingPriceInput.setCustomValidity('Selling price must cover product cost.');
            }
        }
    }
});
</script>
</body>
</html>