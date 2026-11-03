@extends('layouts.admin')

@section('title', 'Customers Management · CatalogPro Admin')
@section('page_title', 'Store Customers')

@section('content')
<div class="card-soft mb-4">
    <!-- Header -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h6 fw-bold mb-0">Registered Customers</h2>
            <p class="text-muted small mb-0">View and manage regular users registered on your store.</p>
        </div>
    </div>

    <!-- رسائل التنبيه -->
    @if(session('success'))
        <div class="alert alert-success m-3 rounded-3 small">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3 rounded-3 small">{{ session('error') }}</div>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Email Address</th>
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
                                <span class="fw-semibold">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td><code>{{ $customer->email }}</code></td>
                        <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light text-danger btn-sm" aria-label="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No regular customers registered yet.</td>
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
                {{ $customers->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this customer account?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush