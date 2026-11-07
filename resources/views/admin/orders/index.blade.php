@extends('layouts.admin')

@section('title', 'Orders · CatalogPro Admin')
@section('page_title', 'Store Orders')

@section('content')
<div class="card-soft mb-4">
    <!-- Header & Add Button -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3">
        <h2 class="h6 fw-bold mb-0">All orders</h2>
        
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
                    <th>Status</th>
                    <th>Marketer</th>
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
                            <span class="fw-semibold text-primary">{{ $order->product->name ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="small text-muted">Cost: <span class="fw-semibold text-dark">{{ $order->product_price }} EGP</span></div>
                            <div class="small text-muted">Sell: <span class="fw-semibold text-dark">{{ $order->selling_price }} EGP</span></div>
                        </td>
                        <td>
                            <span class="badge badge-soft-success fw-bold">+{{ $order->profit }} EGP</span>
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
                            <span class="badge bg-{{ $color }} text-capitalize">{{ $order->status }}</span>
                        </td>
                        <td class="small text-muted">
                            {{ $order->user->name ?? 'System' }}
                        </td>
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
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>

<!-- مودالات تعديل حالة الطلب -->
@if(isset($orders))
    @foreach($orders as $order)
    <div class="modal fade" id="editOrderStatusModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Order Status #{{ $order->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Customer: <span class="text-muted">{{ $order->customer_name }}</span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Order Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control text-capitalize" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending (معلق)</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing (قيد التجهيز)</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped (تم الشحن)</option>
                            <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered (تم التوصيل - سيتم إضافة الأرباح للمحفظة)</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled (ملغي)</option>
                            <option value="returned" {{ $order->status == 'returned' ? 'selected' : '' }}>Returned (مرتجع)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
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

    // لو حصل خطأ في الـ Validation، افتح المودال أوتوماتيك
    @if ($errors->any())
        var orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        orderModal.show();
    @endif
});
</script>
@endpush