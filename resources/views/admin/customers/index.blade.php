@extends('layouts.admin')

@section('title', 'Customers Management · CatalogPro Admin')
@section('page_title', 'Store Customers')

@section('content')
<div class="card-soft mb-4">
    <!-- Header & Search -->
    <div class="p-3 p-lg-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h6 fw-bold mb-0">Registered Customers</h2>
            <p class="text-muted small mb-0">View and manage regular users registered on your store. Click on any customer name to view their orders.</p>
        </div>

        <!-- فورم البحث المباشر للعملاء -->
        <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto" style="max-width: 350px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap"><i class="bi bi-search"></i></button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-danger btn-sm" title="Clear Search"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>

    <!-- رسائل التنبيه -->
    @if(session('success'))
        <div class="alert alert-success m-3 rounded-3 small alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3 rounded-3 small alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Wallet Balance</th> <!-- عمود الرصيد الجديد -->
                    <th>Joined Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="thumb g{{ ($loop->index % 6) + 1 }}" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e9ecef; font-weight: bold;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </span>
                                <a href="{{ route('admin.orders.index', ['user_id' => $customer->id]) }}" class="fw-semibold text-decoration-none text-dark hover-primary" title="View customer orders">
                                    {{ $customer->name }} <i class="bi bi-box-arrow-up-right small text-muted ms-1"></i>
                                </a>
                            </div>
                        </td>
                        <td><code>{{ $customer->email }}</code></td>
                        <td><code>{{ $customer->phone }}</code></td>
                        
                        <!-- عرض الرصيد المتاح من جدول المحافظ مباشرة (أداء سريع جداً) -->
                        <td>
                            <span class="fw-bold text-success">
                                {{ $customer->wallet->balance ?? 0 }} EGP
                            </span>
                        </td>

                        <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                        
                        <td class="text-end text-nowrap">
                            <!-- زرار تصفير الخزنة / سحب الأرباح -->
                            <form action="{{ route('admin.customers.zeroWallet', $customer->id) }}" method="POST" class="d-inline zero-wallet-form">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm me-1" title="Zero Wallet & Withdraw" {{ ($customer->wallet->balance ?? 0) <= 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-cash-coin"></i> Zero Wallet
                                </button>
                            </form>

                            <!-- زرار إعادة تعيين كلمة السر -->
                            <form action="{{ route('admin.customers.resetPassword', $customer->id) }}" method="POST" class="d-inline reset-form">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm me-1" title="Reset Password">
                                    <i class="bi bi-key-fill"></i> Reset
                                </button>
                            </form>

                            <!-- زرار الحذف -->
                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger btn-sm" title="Delete Customer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No regular customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }}</span>
            <div>
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // تأكيد الحذف
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this customer account?')) {
                e.preventDefault();
            }
        });
    });

    // تأكيد إعادة تعيين كلمة السر
    document.querySelectorAll('.reset-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to reset this customer password?')) {
                e.preventDefault();
            }
        });
    });

    // تأكيد تصفير المحفظة
    document.querySelectorAll('.zero-wallet-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('هل أنت متأكد من تصفير محفظة هذا التاجر وسحب الأرباح بالكامل؟')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush