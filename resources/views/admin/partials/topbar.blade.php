<header class="topbar d-flex align-items-center gap-3 px-3 px-lg-4 py-2">
    <button class="btn btn-light d-lg-none" data-sidebar-toggle aria-label="Menu">
        <i class="bi bi-list fs-5"></i>
    </button>

    <h1 class="h5 fw-bold mb-0 me-auto">@yield('page_title', 'Dashboard')</h1>

    <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-dark" data-bs-toggle="dropdown">
            <span class="thumb g1" style="width:36px;height:36px;border-radius:50%">
                {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 1)) }}
            </span>
            <span class="d-none d-sm-block small fw-semibold">
                {{ auth()->user()->name ?? 'Admin Master' }}
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-left me-2"></i>Log out
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>