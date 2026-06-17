<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SchoolMS' }} — {{ $currentTenant->name ?? config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --brand:#3b6db5; --brand-dark:#2a4f87; --sidebar-bg:#1e293b; --sidebar-text:#94a3b8; --sidebar-active:#fff; --sidebar-hover-bg:rgba(255,255,255,.08); }
        body { background:#f4f6fb; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-brand { font-weight:700; letter-spacing:.5px; }

        /* ── Sidebar fixed layout ── */
        .sidebar-main {
            position: fixed;
            top: 56px;
            left: 0;
            width: 16rem;
            height: calc(100vh - 56px);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            z-index: 1030;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform .3s ease-in-out;
        }
        .sidebar-main.open { transform: translateX(0); }

        @media (min-width: 768px) {
            .sidebar-main {
                transform: translateX(0);
            }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            top: 56px;
            background: rgba(0,0,0,.5);
            z-index: 1025;
        }

        .app-main {
            min-height: calc(100vh - 56px);
            padding: 1.5rem;
            transition: margin-left .3s ease-in-out;
        }
        @media (min-width: 768px) {
            .app-main { margin-left: 16rem; }
        }

        /* ── Sidebar inner content ── */
        .sidebar-inner { padding: .75rem .5rem 2rem; }

        .sidebar-inner .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--sidebar-text);
            border-radius: 6px;
            padding: .5rem .75rem;
            margin: 1px 0;
            font-size: .875rem;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .sidebar-inner .nav-link:hover,
        .sidebar-inner .nav-link.active {
            background: var(--sidebar-hover-bg);
            color: var(--sidebar-active);
        }
        .sidebar-inner .nav-link i { width: 1.1rem; text-align: center; font-size: .95rem; }
        .sidebar-inner .nav-link.active i { color: #60a5fa; }

        .sidebar-inner .nav-section {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 1rem .75rem .25rem;
        }

        /* ── Accordion ── */
        .accordion-group { margin: 0; }
        .accordion-trigger {
            display: flex;
            align-items: center;
            gap: .75rem;
            width: 100%;
            border: none;
            background: transparent;
            color: var(--sidebar-text);
            border-radius: 6px;
            padding: .5rem .75rem;
            font-size: .875rem;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .accordion-trigger:hover { background: var(--sidebar-hover-bg); color: var(--sidebar-active); }
        .accordion-trigger i:first-child { width: 1.1rem; text-align: center; font-size: .95rem; }
        .accordion-chevron {
            margin-left: auto;
            transition: transform .25s ease;
            font-size: .75rem;
            color: #64748b;
        }
        .accordion-chevron.rotated { transform: rotate(180deg); }

        .accordion-body {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height .35s ease, opacity .25s ease;
        }
        .accordion-body.open {
            max-height: 600px;
            opacity: 1;
        }
        .accordion-body .nav-link { padding-left: 2.25rem !important; }

        /* ── Misc ── */
        .stat-card {
            border: none; border-left: 4px solid var(--brand);
            border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05);
        }
        .stat-card .stat-value { font-size: 1.7rem; font-weight: 700; color: #2c3e50; }
        .stat-card .stat-label { color: #7d8a9c; font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; }
        .card { border: none; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
        .table thead th { background: #eef2f7; color: #4a5568; font-weight: 600; text-transform: uppercase; font-size: .75rem; }
        .badge-status-pending { background: #fff3cd; color: #856404; }
        .badge-status-paid { background: #d4edda; color: #155724; }
        .badge-status-partial { background: #cce5ff; color: #004085; }
        .badge-status-overdue { background: #f8d7da; color: #721c24; }
        .badge-status-waived { background: #e2e3e5; color: #383d41; }
        .print-hide { }
        @media print {
            .sidebar-main, .navbar, .sidebar-backdrop, .print-hide, .no-print { display: none !important; }
            main.app-main { margin-left: 0 !important; padding: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }" @keydown.escape="sidebarOpen = false">
    <nav class="navbar navbar-dark sticky-top" style="background:var(--brand);">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-outline-light d-md-none me-2" type="button" aria-label="Toggle sidebar" :aria-expanded="sidebarOpen">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="fas fa-school me-2"></i>{{ $currentTenant->name ?? config('app.name') }}
                </a>
            </div>
            <div class="d-flex align-items-center text-white">
                @include('partials._academic_year_selector')
                <span class="me-3 d-none d-sm-inline">
                    <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                    <small class="text-white-50 ms-1">({{ ucfirst(str_replace('_',' ', auth()->user()->role)) }})</small>
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside id="sidebar" class="sidebar-main" :class="{'open': sidebarOpen}" role="navigation" aria-label="Main navigation">
        @include('partials.sidebar')
    </aside>

    {{-- Backdrop (mobile only) --}}
    <div x-show="sidebarOpen"
         class="sidebar-backdrop d-md-none"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity duration-300"
         x-transition:leave="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="presentation">
    </div>

    {{-- Main content --}}
    <main class="app-main">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        $(function() {
            $('table.datatable').DataTable({
                pageLength: 25,
                order: [],
                language: { search: '', searchPlaceholder: 'Search...' }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
