@php
    $masterSubdomain = config('tenancy.master_subdomain');
    $onMasterSubdomain = $masterSubdomain && request()->getHost() === $masterSubdomain . '.' . config('tenancy.app_domain');
    $isMaster = !($currentTenant ?? null) || $onMasterSubdomain || request()->is('admin', 'admin/*');
    $user     = auth()->user();
    $role     = $user?->role;

    $canAccess = function (array $patterns) use ($role) {
        if (! $role) return false;
        if (in_array($role, config('permissions.admin_roles', ['super_admin', 'admin']), true)) {
            return true;
        }
        $allowed = (array) config("permissions.roles.$role", []);
        if (in_array('*', $allowed, true)) return true;

        foreach ($patterns as $p) {
            if (in_array($p, $allowed, true)) return true;
            if (str_ends_with($p, '.*')) {
                $prefix = substr($p, 0, -2);
                foreach ($allowed as $a) {
                    if ($a === $prefix . '.*') return true;
                    if (str_starts_with($a, $prefix . '.')) return true;
                }
            }
        }
        return false;
    };

    $inAcademics = request()->routeIs('classes.*') || request()->routeIs('subjects.*') || request()->routeIs('exams.*') || request()->routeIs('exam-types.*') || request()->routeIs('results.*') || request()->routeIs('attendance.*');
    $inExams = request()->routeIs('exams.*') || request()->routeIs('exam-types.*') || request()->routeIs('results.*');
    $inPeople = request()->routeIs('students.*') || request()->routeIs('teachers.*');
    $inFinance = request()->routeIs('fees.*');
    $inReports = request()->routeIs('reports.*');

    $inOps = request()->routeIs('master.tenants.*') || request()->routeIs('master.users.*');
    $inBilling = request()->routeIs('master.plans.*') || request()->routeIs('master.invoices.*') || request()->routeIs('master.payments.*');
    $inConfig = request()->routeIs('master.settings.*') || request()->routeIs('master.api-keys.*');
    $inInsights = request()->routeIs('master.reports.*') || request()->routeIs('master.audit.*');
    $inHelp = request()->routeIs('master.tickets.*') || request()->routeIs('master.security.*');
@endphp

@if($user && $user->isSuperAdmin() && $isMaster)
    {{-- SuperAdmin sidebar --}}
    <div class="sidebar-inner">
        <div class="nav-section">Overview</div>
        <a href="{{ route('master.dashboard') }}" class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>

        <div x-data="{ open: {{ $inOps ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="ops-panel" id="ops-btn">
                <i class="fas fa-wrench"></i><span>Operations</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="ops-panel" role="region" aria-labelledby="ops-btn" class="accordion-body" :class="open ? 'open' : ''">
                <a href="{{ route('master.tenants.index') }}" class="nav-link {{ request()->routeIs('master.tenants.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i><span>Tenant Management</span>
                </a>
                <a href="{{ route('master.users.index') }}" class="nav-link {{ request()->routeIs('master.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i><span>User Management</span>
                </a>
            </div>
        </div>

        <div x-data="{ open: {{ $inBilling ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="billing-panel" id="billing-btn">
                <i class="fas fa-credit-card"></i><span>Billing</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="billing-panel" role="region" aria-labelledby="billing-btn" class="accordion-body" :class="open ? 'open' : ''">
                <a href="{{ route('master.plans.index') }}" class="nav-link {{ request()->routeIs('master.plans.*') ? 'active' : '' }}">
                    <i class="fas fa-cubes"></i><span>Subscription Plans</span>
                </a>
                <a href="{{ route('master.invoices.index') }}" class="nav-link {{ request()->routeIs('master.invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i><span>Invoices</span>
                </a>
                <a href="{{ route('master.payments.index') }}" class="nav-link {{ request()->routeIs('master.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i><span>Payments</span>
                </a>
            </div>
        </div>

        <div x-data="{ open: {{ $inConfig ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="config-panel" id="config-btn">
                <i class="fas fa-sliders-h"></i><span>Configuration</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="config-panel" role="region" aria-labelledby="config-btn" class="accordion-body" :class="open ? 'open' : ''">
                <a href="{{ route('master.settings.index') }}" class="nav-link {{ request()->routeIs('master.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i><span>System Settings</span>
                </a>
                <a href="{{ route('master.api-keys.index') }}" class="nav-link {{ request()->routeIs('master.api-keys.*') ? 'active' : '' }}">
                    <i class="fas fa-key"></i><span>API Keys</span>
                </a>
            </div>
        </div>

        <div x-data="{ open: {{ $inInsights ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="insights-panel" id="insights-btn">
                <i class="fas fa-chart-line"></i><span>Insights</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="insights-panel" role="region" aria-labelledby="insights-btn" class="accordion-body" :class="open ? 'open' : ''">
                <a href="{{ route('master.reports.index') }}" class="nav-link {{ request()->routeIs('master.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i><span>Reports</span>
                </a>
                <a href="{{ route('master.audit.index') }}" class="nav-link {{ request()->routeIs('master.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i><span>Audit Logs</span>
                </a>
            </div>
        </div>

        <div x-data="{ open: {{ $inHelp ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="help-panel" id="help-btn">
                <i class="fas fa-life-ring"></i><span>Help</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="help-panel" role="region" aria-labelledby="help-btn" class="accordion-body" :class="open ? 'open' : ''">
                <a href="{{ route('master.tickets.index') }}" class="nav-link {{ request()->routeIs('master.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-life-ring"></i><span>Support Tickets</span>
                </a>
                <a href="{{ route('master.security.index') }}" class="nav-link {{ request()->routeIs('master.security.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i><span>Security</span>
                </a>
            </div>
        </div>

        <div class="nav-section">Account</div>
        <a href="{{ route('master.register') }}" class="nav-link {{ request()->routeIs('master.register') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i><span>Register School</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </form>
    </div>

@else
    {{-- Tenant sidebar (role-filtered) --}}
    <div class="sidebar-inner">
        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>

        @if($canAccess(['classes.*', 'subjects.*', 'exams.*', 'exam-types.*', 'results.*', 'attendance.*']))
        <div x-data="{ open: {{ $inAcademics ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="academics-panel" id="academics-btn">
                <i class="fas fa-graduation-cap"></i><span>Academics</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="academics-panel" role="region" aria-labelledby="academics-btn" class="accordion-body" :class="open ? 'open' : ''">
                @if($canAccess(['classes.*']))
                <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard"></i><span>Classes</span>
                </a>
                @endif
                @if($canAccess(['subjects.*']))
                <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i><span>Subjects</span>
                </a>
                @endif
                @if($canAccess(['exams.*', 'exam-types.index', 'results.*']))
                <div x-data="{ open: {{ $inExams ? 'true' : 'false' }} }" class="accordion-group ms-3">
                    <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="exams-panel" id="exams-btn">
                        <i class="fas fa-file-alt"></i><span>Exams</span>
                        <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
                    </button>
                    <div id="exams-panel" role="region" aria-labelledby="exams-btn" class="accordion-body" :class="open ? 'open' : ''">
                        @if($canAccess(['exams.*']))
                        <a href="{{ route('exams.index') }}" class="nav-link {{ request()->routeIs('exams.*') && !request()->routeIs('results.*') && !request()->routeIs('exam-types.*') ? 'active' : '' }}">
                            <i class="fas fa-list"></i><span>All Exams</span>
                        </a>
                        @endif
                        @if($canAccess(['results.*']))
                        <a href="{{ route('results.index') }}" class="nav-link {{ request()->routeIs('results.*') ? 'active' : '' }}">
                            <i class="fas fa-pen"></i><span>Enter Marks</span>
                        </a>
                        @endif
                        @if($canAccess(['exam-types.index']))
                        <a href="{{ route('exam-types.index') }}" class="nav-link {{ request()->routeIs('exam-types.*') ? 'active' : '' }}">
                            <i class="fas fa-tag"></i><span>Exam Types</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                @if($canAccess(['attendance.*']))
                <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i><span>Attendance</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($canAccess(['students.*', 'teachers.*']))
        <div x-data="{ open: {{ $inPeople ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="people-panel" id="people-btn">
                <i class="fas fa-users"></i><span>People</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="people-panel" role="region" aria-labelledby="people-btn" class="accordion-body" :class="open ? 'open' : ''">
                @if($canAccess(['students.*']))
                <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i><span>Students</span>
                </a>
                @endif
                @if($canAccess(['teachers.*']))
                <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i><span>Teachers</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($canAccess(['fees.index', 'fees.payments.*', 'fees.categories.*']))
        <div x-data="{ open: {{ $inFinance ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="finance-panel" id="finance-btn">
                <i class="fas fa-coins"></i><span>Finance</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="finance-panel" role="region" aria-labelledby="finance-btn" class="accordion-body" :class="open ? 'open' : ''">
                @if($canAccess(['fees.index', 'fees.show']))
                <a href="{{ route('fees.index') }}" class="nav-link {{ request()->routeIs('fees.*') && !request()->routeIs('fees.payments.*') && !request()->routeIs('fees.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i><span>Fee Assignments</span>
                </a>
                @endif
                @if($canAccess(['fees.payments.*']))
                <a href="{{ route('fees.payments.index') }}" class="nav-link {{ request()->routeIs('fees.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i><span>Payments</span>
                </a>
                @endif
                @if($canAccess(['fees.categories.*']))
                <a href="{{ route('fees.categories.index') }}" class="nav-link {{ request()->routeIs('fees.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i><span>Fee Categories</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($canAccess(['reports.results', 'reports.attendance', 'reports.fees']))
        <div x-data="{ open: {{ $inReports ? 'true' : 'false' }} }" class="accordion-group">
            <button @click="open = !open" :aria-expanded="open" class="accordion-trigger" aria-controls="reports-panel" id="reports-btn">
                <i class="fas fa-chart-pie"></i><span>Reports</span>
                <i class="fas fa-chevron-down accordion-chevron" :class="open ? 'rotated' : ''"></i>
            </button>
            <div id="reports-panel" role="region" aria-labelledby="reports-btn" class="accordion-body" :class="open ? 'open' : ''">
                @if($canAccess(['reports.results']))
                <a href="{{ route('reports.results') }}" class="nav-link {{ request()->routeIs('reports.results') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i><span>Exam Results</span>
                </a>
                @endif
                @if($canAccess(['reports.attendance']))
                <a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i><span>Attendance</span>
                </a>
                @endif
                @if($canAccess(['reports.fees']))
                <a href="{{ route('reports.fees') }}" class="nav-link {{ request()->routeIs('reports.fees') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i><span>Fee Collection</span>
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
@endif
