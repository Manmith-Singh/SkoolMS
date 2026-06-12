@php
    $masterSubdomain = config('tenancy.master_subdomain');
    $onMasterSubdomain = $masterSubdomain && request()->getHost() === $masterSubdomain . '.' . config('tenancy.app_domain');
    $isMaster = !($currentTenant ?? null) || $onMasterSubdomain || request()->is('admin', 'admin/*');
    $user     = auth()->user();
    $role     = $user?->role;

    /**
     * Sidebar helper: show this nav block only if the current user's role
     * is allowed to access at least one of the route patterns in the list.
     */
    $canAccess = function (array $patterns) use ($role) {
        if (! $role) return false;
        if (in_array($role, config('permissions.admin_roles', ['super_admin', 'admin']), true)) {
            return true;
        }
        $allowed = (array) config("permissions.roles.$role", []);
        if (in_array('*', $allowed, true)) return true;

        foreach ($patterns as $p) {
            // Exact match
            if (in_array($p, $allowed, true)) return true;

            // Pattern is a wildcard like "students.*" — match if any
            // allowed item starts with the same prefix.
            if (str_ends_with($p, '.*')) {
                $prefix = substr($p, 0, -2);
                foreach ($allowed as $a) {
                    if ($a === $prefix . '.*') return true;          // wildcard on both sides
                    if (str_starts_with($a, $prefix . '.')) return true; // allowed is a literal under the prefix
                }
            }
        }
        return false;
    };
@endphp

@if($user && $user->isSuperAdmin() && $isMaster)
    {{-- SuperAdmin sidebar (apex) --}}
    <div class="nav-section">Overview</div>
    <a href="{{ route('master.dashboard') }}" class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="nav-section">Operations</div>
    <a href="{{ route('master.tenants.index') }}" class="nav-link {{ request()->routeIs('master.tenants.*') ? 'active' : '' }}">
        <i class="fas fa-building"></i> Tenant Management
    </a>
    <a href="{{ route('master.users.index') }}" class="nav-link {{ request()->routeIs('master.users.*') ? 'active' : '' }}">
        <i class="fas fa-users-cog"></i> User Management
    </a>

    <div class="nav-section">Billing</div>
    <a href="{{ route('master.plans.index') }}" class="nav-link {{ request()->routeIs('master.plans.*') ? 'active' : '' }}">
        <i class="fas fa-cubes"></i> Subscription Plans
    </a>
    <a href="{{ route('master.invoices.index') }}" class="nav-link {{ request()->routeIs('master.invoices.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice"></i> Invoices
    </a>
    <a href="{{ route('master.payments.index') }}" class="nav-link {{ request()->routeIs('master.payments.*') ? 'active' : '' }}">
        <i class="fas fa-credit-card"></i> Payments
    </a>

    <div class="nav-section">Configuration</div>
    <a href="{{ route('master.settings.index') }}" class="nav-link {{ request()->routeIs('master.settings.*') ? 'active' : '' }}">
        <i class="fas fa-sliders-h"></i> System Settings
    </a>
    <a href="{{ route('master.api-keys.index') }}" class="nav-link {{ request()->routeIs('master.api-keys.*') ? 'active' : '' }}">
        <i class="fas fa-key"></i> API Keys
    </a>

    <div class="nav-section">Insights</div>
    <a href="{{ route('master.reports.index') }}" class="nav-link {{ request()->routeIs('master.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> Reports
    </a>
    <a href="{{ route('master.audit.index') }}" class="nav-link {{ request()->routeIs('master.audit.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i> Audit Logs
    </a>

    <div class="nav-section">Help</div>
    <a href="{{ route('master.tickets.index') }}" class="nav-link {{ request()->routeIs('master.tickets.*') ? 'active' : '' }}">
        <i class="fas fa-life-ring"></i> Support Tickets
    </a>
    <a href="{{ route('master.security.index') }}" class="nav-link {{ request()->routeIs('master.security.*') ? 'active' : '' }}">
        <i class="fas fa-shield-alt"></i> Security
    </a>

    <div class="nav-section">Account</div>
    <a href="{{ route('master.register') }}" class="nav-link {{ request()->routeIs('master.register') ? 'active' : '' }}">
        <i class="fas fa-plus-circle"></i> Register School
    </a>
    <form method="POST" action="{{ route('logout') }}" class="m-0">
        @csrf
        <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </form>
@else
    {{-- Tenant sidebar (role-filtered) --}}
    <div class="nav-section">Main</div>
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    @if($canAccess(['classes.*']))
    <div class="nav-section">Academics</div>
    @endif
    @if($canAccess(['classes.*']))
    <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
        <i class="fas fa-chalkboard"></i> Classes
    </a>
    @endif
    @if($canAccess(['subjects.*']))
    <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
        <i class="fas fa-book"></i> Subjects
    </a>
    @endif
    @if($canAccess(['exams.*']))
    <a href="{{ route('exams.index') }}" class="nav-link {{ request()->routeIs('exams.*') ? 'active' : '' }}">
        <i class="fas fa-file-alt"></i> Exams
    </a>
    @endif
    @if($canAccess(['results.*']))
    <a href="{{ route('results.index') }}" class="nav-link {{ request()->routeIs('results.*') ? 'active' : '' }}">
        <i class="fas fa-poll"></i> Results
    </a>
    @endif
    @if($canAccess(['attendance.*']))
    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> Attendance
    </a>
    @endif

    @if($canAccess(['students.*', 'teachers.*']))
    <div class="nav-section">People</div>
    @endif
    @if($canAccess(['students.*']))
    <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i> Students
    </a>
    @endif
    @if($canAccess(['teachers.*']))
    <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i> Teachers
    </a>
    @endif

    @if($canAccess(['fees.index', 'fees.show']))
    <div class="nav-section">Finance</div>
    @endif
    @if($canAccess(['fees.index', 'fees.show']))
    <a href="{{ route('fees.index') }}" class="nav-link {{ request()->routeIs('fees.*') && !request()->routeIs('fees.payments.*') && !request()->routeIs('fees.categories.*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar"></i> Fee Assignments
    </a>
    @endif
    @if($canAccess(['fees.payments.*']))
    <a href="{{ route('fees.payments.index') }}" class="nav-link {{ request()->routeIs('fees.payments.*') ? 'active' : '' }}">
        <i class="fas fa-cash-register"></i> Payments
    </a>
    @endif
    @if($canAccess(['fees.categories.*']))
    <a href="{{ route('fees.categories.index') }}" class="nav-link {{ request()->routeIs('fees.categories.*') ? 'active' : '' }}">
        <i class="fas fa-tags"></i> Fee Categories
    </a>
    @endif

    @if($canAccess(['reports.results', 'reports.attendance', 'reports.fees']))
    <div class="nav-section">Reports</div>
    @endif
    @if($canAccess(['reports.results']))
    <a href="{{ route('reports.results') }}" class="nav-link {{ request()->routeIs('reports.results') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i> Exam Results
    </a>
    @endif
    @if($canAccess(['reports.attendance']))
    <a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i> Attendance
    </a>
    @endif
    @if($canAccess(['reports.fees']))
    <a href="{{ route('reports.fees') }}" class="nav-link {{ request()->routeIs('reports.fees') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i> Fee Collection
    </a>
    @endif
@endif
