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
        :root { --brand:#3b6db5; --brand-dark:#2a4f87; }
        body { background:#f4f6fb; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-brand { font-weight:700; letter-spacing:.5px; }
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, #1f2d3d 0%, #2a3f57 100%);
            color: #cfd8e3;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: #cfd8e3;
            border-radius: 6px;
            padding: .55rem .9rem;
            margin: 2px 8px;
            font-size: .92rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .sidebar .nav-link i { width: 20px; }
        .sidebar .nav-section {
            font-size: .72rem; text-transform: uppercase; letter-spacing: 1.2px;
            color: #7c8aa0; padding: 1rem 1.2rem .35rem;
        }
        main.app-main { padding: 1.5rem; }
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
        @media (max-width: 768px) {
            .sidebar { display: none; }
        }
        .print-hide { }
        @media print {
            .sidebar, .navbar, .print-hide, .no-print { display: none !important; }
            main.app-main { padding: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-dark sticky-top" style="background:var(--brand);">
        <div class="container-fluid">
            <button class="btn btn-sm btn-outline-light d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-school me-2"></i>{{ $currentTenant->name ?? config('app.name') }}
            </a>
            <div class="d-flex align-items-center text-white">
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

    <div class="container-fluid p-0">
        <div class="row g-0">
            <aside class="col-md-2 d-none d-md-block sidebar">
                @include('partials.sidebar')
            </aside>
            <main class="col-md-10 app-main">
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
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav">
        <div class="offcanvas-body p-0">
            @include('partials.sidebar')
        </div>
    </div>

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
