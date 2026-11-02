<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · CatalogPro Admin</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #4f46e5;
            --brand-dark: #4338ca;
            --brand-soft: #eef2ff;
            --ink: #0f172a;
            --muted: #64748b;
            --canvas: #f4f6fb;
            --sidebar: #0f172a;
            --sidebar-w: 260px;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .06);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, .08);
            --bs-primary: var(--brand);
            --bs-primary-rgb: 79, 70, 229;
            --bs-body-font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        body { background: var(--canvas); color: var(--ink); -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; }
        .btn-primary { 
            --bs-btn-bg: var(--brand); --bs-btn-border-color: var(--brand);
            --bs-btn-hover-bg: var(--brand-dark); --bs-btn-hover-border-color: var(--brand-dark);
            --bs-btn-active-bg: var(--brand-dark); --bs-btn-active-border-color: var(--brand-dark);
        }
        .btn-outline-primary { 
            --bs-btn-color: var(--brand); --bs-btn-border-color: var(--brand);
            --bs-btn-hover-bg: var(--brand); --bs-btn-hover-border-color: var(--brand); --bs-btn-active-bg: var(--brand); 
        }
        .text-primary { color: var(--brand) !important; }
        .bg-brand-soft { background: var(--brand-soft); }
        .form-control, .form-select { border-radius: .65rem; padding: .65rem .9rem; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 .22rem rgba(79, 70, 229, .15); }
        .btn { border-radius: .65rem; font-weight: 500; }
        .card-soft { background: #fff; border: 0; border-radius: 1rem; box-shadow: var(--shadow-sm); }

        /* Toast Top */
        #toastTop { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 2000; width: min(92vw, 440px); display: flex; flex-direction: column; gap: .5rem; pointer-events: none; }
        .toast-top { pointer-events: auto; border: 0; border-radius: .85rem; box-shadow: var(--shadow-md); animation: slideDown .35s cubic-bezier(.2, .8, .2, 1); border-left: 5px solid var(--t, var(--brand)); background: #fff; }
        .toast-top.hiding { animation: slideUp .3s ease forwards; }
        .toast-top .ti { color: var(--t); font-size: 1.25rem; }
        @keyframes slideDown { from { transform: translateY(-140%); opacity: 0; } to { transform: none; opacity: 1; } }
        @keyframes slideUp { to { transform: translateY(-140%); opacity: 0; } }

        /* Sidebar & Admin Shell */
        .sidebar { position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w); background: var(--sidebar); color: #cbd5e1; z-index: 1040; transition: transform .25s ease; display: flex; flex-direction: column; }
        .sidebar .brand { color: #fff; font-weight: 700; font-size: 1.2rem; padding: 1.25rem 1.5rem; }
        .sidebar .nav-label { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: #64748b; padding: 1rem 1.5rem .4rem; }
        .sidebar .nav-link { color: #cbd5e1; margin: .12rem .85rem; padding: .65rem .9rem; border-radius: .7rem; display: flex; align-items: center; gap: .75rem; transition: background .15s, color .15s; }
        .sidebar .nav-link:hover { background: rgba(255, 255, 255, .06); color: #fff; }
        .sidebar .nav-link.active { background: var(--brand); color: #fff; box-shadow: 0 6px 16px rgba(79, 70, 229, .35); }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .5); z-index: 1030; }
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar { background: rgba(255, 255, 255, .9); backdrop-filter: blur(8px); position: sticky; top: 0; z-index: 1020; box-shadow: var(--shadow-sm); }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            body.sidebar-open .sidebar { transform: none; }
            body.sidebar-open .sidebar-backdrop { display: block; }
        }

        .stat-card { transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-icon { width: 52px; height: 52px; border-radius: .9rem; display: grid; place-items: center; font-size: 1.5rem; }
        .table-modern { --bs-table-bg: transparent; margin: 0; }
        .table-modern thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); border-bottom: 1px solid #e2e8f0; background: #f8fafc; white-space: nowrap; padding: .85rem 1rem; }
        .table-modern td { padding: .85rem 1rem; vertical-align: middle; border-color: #f1f5f9; }
        .table-modern tbody tr:hover { background: #fafbff; }
        .thumb { width: 44px; height: 44px; border-radius: .7rem; display: grid; place-items: center; color: #fff; font-size: 1.2rem; flex: none; }
        .badge-soft-success { background: #dcfce7; color: #166534; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; }
        .badge-soft-primary { background: var(--brand-soft); color: var(--brand-dark); }
        .badge { font-weight: 600; padding: .4em .7em; }
        .ph { display: grid; place-items: center; color: rgba(255, 255, 255, .92); }
        .g1 { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .g2 { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
        .g3 { background: linear-gradient(135deg, #10b981, #059669); }
        .g4 { background: linear-gradient(135deg, #f59e0b, #ea580c); }
        .g5 { background: linear-gradient(135deg, #ec4899, #db2777); }
        .g6 { background: linear-gradient(135deg, #14b8a6, #0891b2); }
    </style>
    @stack('styles')
</head>
<body>

    <div id="toastTop" aria-live="polite"></div>

    <!-- Sidebar Partial -->
    @include('admin.partials.sidebar')

    <div class="sidebar-backdrop" data-sidebar-toggle></div>

    <div class="main">
        <!-- Topbar Partial -->
        @include('admin.partials.topbar')

        <!-- Dynamic Content -->
        <main class="p-3 p-lg-4">
            @yield('content')
        </main>
    </div>

    <!-- JavaScript Base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        'use strict';
        var TYPES = {
            success: { icon: 'bi-check-circle-fill', color: '#16a34a' },
            error:   { icon: 'bi-x-circle-fill',     color: '#dc2626' },
            warning: { icon: 'bi-exclamation-triangle-fill', color: '#d97706' },
            info:    { icon: 'bi-info-circle-fill',  color: '#4f46e5' }
        };
        window.showToast = function (message, type, ms) {
            type = TYPES[type] ? type : 'success';
            var box = document.getElementById('toastTop');
            if (!box) { box = document.createElement('div'); box.id = 'toastTop'; document.body.appendChild(box); }
            var t = TYPES[type], el = document.createElement('div');
            el.className = 'toast-top d-flex align-items-center gap-3 p-3';
            el.style.setProperty('--t', t.color);
            el.setAttribute('role', 'alert');
            el.innerHTML = '<i class="bi ' + t.icon + ' ti"></i><div class="flex-grow-1 small fw-semibold"></div>' +
                           '<button type="button" class="btn-close btn-sm" aria-label="Close"></button>';
            el.children[1].textContent = message;
            var close = function () { el.classList.add('hiding'); setTimeout(function () { el.remove(); }, 300); };
            el.querySelector('.btn-close').addEventListener('click', close);
            box.appendChild(el);
            setTimeout(close, ms || 3500);
        };

        var PAL = { g1:['#6366f1','#8b5cf6'], g2:['#0ea5e9','#2563eb'], g3:['#10b981','#059669'], g4:['#f59e0b','#ea580c'], g5:['#ec4899','#db2777'], g6:['#14b8a6','#0891b2'] };
        function artUri(k, n) {
            var c = PAL[k] || PAL.g1, r = (n * 37) % 60;
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice">' +
              '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' + c[0] + '"/><stop offset="1" stop-color="' + c[1] + '"/></linearGradient></defs>' +
              '<rect width="400" height="300" fill="url(#g)"/>' +
              '<circle cx="' + (320 - r) + '" cy="' + (40 + r) + '" r="130" fill="#fff" opacity=".10"/>' +
              '<circle cx="' + (60 + r) + '" cy="290" r="110" fill="#fff" opacity=".08"/>' +
              '<circle cx="200" cy="150" r="70" fill="#fff" opacity=".07"/></svg>';
            return 'url("data:image/svg+xml,' + encodeURIComponent(svg) + '")';
        }
        var artCount = 0;
        window.applyArt = function (el) {
            var m = /\bg[1-6]\b/.exec(el.className); if (!m) return;
            el.style.backgroundImage = artUri(m[0], el.dataset.n || (el.dataset.n = ++artCount));
            el.style.backgroundSize = 'cover'; el.style.backgroundPosition = 'center';
        };

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ph, .thumb').forEach(window.applyArt);
            document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
                b.addEventListener('click', function () { document.body.classList.toggle('sidebar-open'); });
            });
        });
    })();
    </script>

    <!-- Flash Notifications from Laravel Session -->
    @if(session('success'))
        <script>showToast("{{ session('success') }}", 'success');</script>
    @endif
    @if(session('error'))
        <script>showToast("{{ session('error') }}", 'error');</script>
    @endif

    @stack('scripts')
</body>
</html>