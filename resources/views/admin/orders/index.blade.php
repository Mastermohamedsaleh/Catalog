@extends('layouts.admin')

@section('title', 'Orders · CatalogPro Admin')
@section('page_title', 'Store Orders')

@section('content')
<div class="card-soft mb-4">
    <!-- Header, Search & Add Button -->
    <div class="p-3 p-lg-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <h2 class="h6 fw-bold mb-0">All orders</h2>
        
        <!-- فورم البحث المباشر للطلبات -->
        <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto" style="max-width: 350px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ID, customer name, phone..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap"><i class="bi bi-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-danger btn-sm" title="Clear Search"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>

        <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#orderModal">
            <i class="bi bi-plus-lg me-1"></i> Add new order
        </button>
    </div>

    <!-- تنبيه عام لو فيه أي أخطاء في الـ Validation أو المخزون -->
    @if ($errors->any())
        <div class="alert alert-danger m-3 mb-0 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>
                <strong>تنبيه!</strong> برجاء مراجعة البيانات المدخلة وتصحيح الأخطاء أدناه.
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger m-3 mb-0 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-x-circle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success m-3 mb-0 rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Table -->
    <div class="table-responsive p-3">
        <table class="table table-modern align-middle mb-0" id="ordersTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Prices (Cost / Sell)</th>
                    <th>Profit</th>
                    <th>Size / Color</th>
                    <th>Status</th>
                    <th>Marketer</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $orders->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $order->customer_name }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->customer_phone }}</div>
                        </td>
                        <td>
                            <!-- اسم المنتج أصبح رابطاً قابلاً للنقر لفتح تفاصيل المنتج في تبويب جديد -->
                            <a href="{{ route('shop.product.show', $order->product_id) }}" target="_blank" class="fw-semibold text-primary text-decoration-none" title="View Product Details">
                                {{ $order->product->name ?? '—' }} <i class="bi bi-box-arrow-up-right small ms-1 opacity-75"></i>
                            </a>
                        </td>
                        <td>
                            <div class="small text-muted">Cost: <span class="fw-semibold text-dark">{{ $order->product_price }} EGP</span></div>
                            <div class="small text-muted">Sell: <span class="fw-semibold text-dark">{{ $order->selling_price }} EGP</span></div>
                        </td>
                        <td>
                            <span class="badge badge-soft-success fw-bold">+{{ $order->profit }} EGP</span>
                        </td>

                                    <td>
    <span class="fw-bold">{{ $order->product->name }}</span>
    
    <!-- عرض المقاس واللون لو الأوردر له فاريانت -->
    @if($order->variant)
        <div class="mt-1">
            <span class="badge bg-secondary">
                المقاس: {{ $order->variant->size ?? 'N/A' }} 
                @if($order->variant->color)
                    | اللون: {{ $order->variant->color }}
                @endif
            </span>
        </div>
    @else
        <div class="text-muted small">بدون مقاس/لون</div>
    @endif
</td>



                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    'returned' => 'secondary',
                                ];
                                $color = $statusColors[$order->status] ?? 'secondary';
                            @endphp
                            <div>
                                <span class="badge bg-{{ $color }} text-capitalize">{{ $order->status }}</span>
                            </div>
                            
                            <!-- إظهار سبب الإلغاء أو الإرجاع مباشرة تحت الحالة في الجدول لو موجود -->
                            @if(in_array($order->status, ['cancelled', 'returned']) && $order->cancellation_reason)
                                <div class="small text-danger mt-1" style="max-width: 200px; font-size: 11px;" title="{{ $order->cancellation_reason }}">
                                    <i class="bi bi-info-circle me-1"></i> <strong>السبب:</strong> {{ Str::limit($order->cancellation_reason, 40) }}
                                </div>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $order->user->name ?? 'System' }}
                        </td>
                        <td class="small text-muted">
{{ $order->user->created_at->diffForHumans() }}                        </td>
                        <td class="text-end text-nowrap">
                            <!-- زرار تغيير الحالة -->
                            <button class="btn btn-icon btn-light" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editOrderStatusModal{{ $order->id }}" 
                                    aria-label="Update Status" title="Change Status">
                                <i class="bi bi-gear text-primary"></i>
                            </button>

                            <!-- زرار الحذف -->
                            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger" aria-label="Delete" title="Delete Order">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($orders) && method_exists($orders, 'hasPages') && $orders->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}</span>
            <div>
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- مودالات تعديل حالة الطلب -->
@if(isset($orders))
    @foreach($orders as $order)
    <div class="modal fade" id="editOrderStatusModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
           <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            
            <div class="modal-header">
                <h5 class="modal-title">تحديث حالة الطلب #{{ $order->id }}</h5>
                <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body text-start">
                <p class="small text-muted mb-3 text-end">العميل: <strong>{{ $order->customer_name }}</strong></p>

                @if($order->status === 'cancelled')
                    <div class="alert alert-danger small text-end">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> تنبيه: هذا الطلب ملغي من قبل التاجر، ولا يمكن تعديل حالته.
                    </div>
                @else
                    <!-- اختيار الحالة (Select) -->
                    <div class="mb-3 text-end">
                        <label class="form-label small fw-semibold">حالة الطلب <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>قيد الانتظار (Pending)</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>جاري التجهيز (Processing)</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>تم الشحن (Shipped)</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>تم التوصيل (Delivered)</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي (Cancelled)</option>
                            <option value="returned" {{ $order->status == 'returned' ? 'selected' : '' }}>مرتجع (Returned)</option>
                        </select>
                    </div>

                    <!-- سبب الإلغاء أو الإرجاع -->
                    <div class="mb-3 text-end">
                        <label class="form-label small fw-semibold">سبب الإلغاء أو الإرجاع <span class="text-danger">* (لو تم اختيار ملغي أو مرتجع)</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="اكتب السبب هنا...">{{ $order->cancellation_reason }}</textarea>
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                @if($order->status !== 'cancelled')
                    <button type="submit" class="btn btn-primary btn-sm px-4">حفظ التغييرات</button>
                @endif
            </div>
        </form>
        </div>
    </div>
    @endforeach
@endif

<!-- مودال إضافة أوردر جديد -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form action="{{ route('admin.orders.store') }}" method="POST" class="modal-content border-0 rounded-4 shadow-lg needs-validation" novalidate>
        @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add New Order (Dropshipping)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        
        <!-- اختيار المنتج مع التحقق من المخزون -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Select Product <span class="text-danger">*</span></label>
          <select name="product_id" id="productSelect" class="form-control @error('product_id') is-invalid @enderror" required>
              <option value="">-- Choose Product --</option>
              @foreach(\App\Models\Product::where('is_active', 1)->get() as $product)
                  <option value="{{ $product->id }}" 
                          data-stock="{{ $product->stock ?? 0 }}"
                          {{ ($product->stock ?? 0) <= 0 ? 'disabled' : '' }}
                          {{ old('product_id') == $product->id ? 'selected' : '' }}>
                      {{ $product->name }} (Wholesale: {{ $product->price }} EGP) 
                      {{ ($product->stock ?? 0) > 0 ? '[Stock: ' . $product->stock . ']' : '[OUT OF STOCK ❌]' }}
                  </option>
              @endforeach
          </select>
          @error('product_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <!-- سعر البيع للعميل -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Selling Price (EGP) <span class="text-danger">*</span></label>
          <input type="number" step="0.01" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" value="{{ old('selling_price') }}" placeholder="e.g. 350" required>
          @error('selling_price')
              <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <div class="form-text small text-muted">Must be greater than or equal to product wholesale price.</div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <!-- بيانات العميل -->
        <div class="mb-3">
          <label class="form-label small fw-semibold">Customer Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" placeholder="Full name" required>
          @error('customer_name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Customer Phone <span class="text-danger">*</span></label>
          <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="01012345678" required>
          @error('customer_phone')
              <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <div class="form-text small text-muted">Example: 01012345678 (11 digits starting with 01)</div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Customer Address <span class="text-danger">*</span></label>
          <textarea class="form-control @error('customer_address') is-invalid @enderror" name="customer_address" rows="3" placeholder="City, Street, Building, Floor..." required>{{ old('customer_address') }}</textarea>
          @error('customer_address')
              <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <div class="form-text small text-muted">أدخل تفاصيل العنوان بوضوح (أقل شيئ 10 حروف).</div>
        </div>

      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create order</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // تأكيد الحذف
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this order?')) {
                e.preventDefault();
            }
        });
    });

    // إظهار وإخفاء صندوق سبب الإلغاء أو الإرجاع بذكاء حسب الحالة المتاحة
    document.querySelectorAll('.order-status-select').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.getAttribute('data-order-id');
            const reasonBox = document.querySelector('.reason-box-' + orderId);
            if (this.value === 'cancelled' || this.value === 'returned') {
                reasonBox.style.display = 'block';
                reasonBox.querySelector('textarea').setAttribute('required', 'required');
            } else {
                reasonBox.style.display = 'none';
                reasonBox.querySelector('textarea').removeAttribute('required');
            }
        });
    });

    // لو حصل خطأ في الـ Validation، افتح المودال أوتوماتيك
    @if ($errors->any())
        var orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        orderModal.show();
    @endif
});
</script>
@endpush