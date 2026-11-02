@extends('layouts.admin')

@section('title', 'Admins Management · CatalogPro Admin')
@section('page_title', 'Admins Management')

@section('content')
<div class="card-soft mb-4">
    <!-- Header & Add Button -->
    <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h6 fw-bold mb-0">System Administrators</h2>
            <p class="text-muted small mb-0">Manage users and administrative access levels.</p>
        </div>
        
        <button class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#createAdminModal">
            <i class="bi bi-plus-lg me-1"></i> Add new admin
        </button>
    </div>

    <!-- رسائل الأخطاء أو النجاح -->
    @if(session('error'))
        <div class="alert alert-danger m-3 rounded-3 small">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger m-3 rounded-3 small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Access Level</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admins->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="thumb g{{ ($loop->index % 6) + 1 }}" style="width: 36px; height: 36px; border-radius: 50%;">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </span>
                                <span class="fw-semibold">{{ $admin->name }}</span>
                                @if($admin->id === auth()->id())
                                    <span class="badge bg-light text-dark border">You</span>
                                @endif
                            </div>
                        </td>
                        <td><code>{{ $admin->email }}</code></td>
                        <td>
                            @if($admin->is_super)
                                <span class="badge badge-soft-danger">Super Admin</span>
                            @else
                                <span class="badge badge-soft-primary">Regular Admin</span>
                            @endif
                        </td>
                        <td>{{ $admin->created_at->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-icon btn-light" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editAdminModal{{ $admin->id }}" 
                                    aria-label="Edit">
                                <i class="bi bi-pencil text-primary"></i>
                            </button>

                            @if($admin->id !== auth()->id())
                                <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-light text-danger" aria-label="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No administrators found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($admins->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center small text-muted border-top">
            <span>Showing {{ $admins->firstItem() ?? 0 }}–{{ $admins->lastItem() ?? 0 }} of {{ $admins->total() }}</span>
            <div>
                {{ $admins->links() }}
            </div>
        </div>
    @endif
</div>

<!-- ============ مودالات التعديل (خارج الجدول تماماً) ============ -->
@foreach($admins as $admin)
<div class="modal fade" id="editAdminModal{{ $admin->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Admin: {{ $admin->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Password <span class="text-muted">(Leave blank to keep current)</span></label>
                    <input type="password" class="form-control" name="password" placeholder="••••••••">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" placeholder="••••••••">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="editSuper{{ $admin->id }}" name="is_super" value="1" {{ $admin->is_super ? 'checked' : '' }} @if($admin->id === auth()->id()) disabled @endif>
                    <label class="form-check-label small fw-semibold text-danger" for="editSuper{{ $admin->id }}">Grant Super Admin Privileges</label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Admin</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- ============ مودال إضافة أدمن جديد ============ -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('admin.admins.store') }}" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
        @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Add New Administrator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="admin@catalogpro.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password" placeholder="••••••••" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="password_confirmation" placeholder="••••••••" required>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="createSuper" name="is_super" value="1">
          <label class="form-check-label small fw-semibold text-danger" for="createSuper">Grant Super Admin Privileges</label>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Admin</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this administrator account?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush