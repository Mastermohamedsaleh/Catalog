<aside class="sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand">
        <i class="bi bi-grid-fill me-2"></i>CatalogPro
    </a>
    
    <nav class="nav flex-column flex-grow-1 overflow-auto">
        <div class="nav-label">Main</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i>Dashboard
        </a>
        
        <div class="nav-label">Catalog</div>
        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
            <i class="bi bi-tags"></i>Categories
        </a>
        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="#">
            <i class="bi bi-box-seam"></i>Products
        </a>
@if(auth()->check() && auth()->user()->is_super)
    <div class="nav-label">Security & Access</div>
    <a class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" href="{{ route('admin.admins.index') }}">
        <i class="bi bi-shield-lock"></i>Admins Management
    </a>
@endif
        
        <div class="nav-label">Site</div>
        <a class="nav-link" href="{{ route('catalog.index') }}" target="_blank">
            <i class="bi bi-globe2"></i>View website
        </a>
    </nav>
    
    <div class="p-3 border-top border-secondary-subtle">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <a href="#" class="nav-link m-0" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i>Log out
            </a>
        </form>
    </div>
</aside>