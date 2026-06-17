@php
    $isMaster = !($currentTenant ?? null) || request()->is('admin', 'admin/*');
    $user = auth()->user();
    $isAdmin = $user && in_array($user->role, config('permissions.admin_roles', ['super_admin', 'admin']), true);
@endphp

@if(!$isMaster && $isAdmin && isset($currentTenant))
    @php
        $years = \App\Models\Tenant\AcademicYear::all();
        $currentYearId = session('current_academic_year_id');
    @endphp
    @if($years->isNotEmpty())
    <form method="POST" action="{{ route('academic-years.switch') }}" class="d-inline me-3" id="year-switch-form">
        @csrf
        <select name="year_id" class="form-select form-select-sm d-inline-block w-auto" onchange="document.getElementById('year-switch-form').submit()" style="background-color:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.3);">
            @foreach($years as $year)
                <option value="{{ $year->id }}" style="color:#000;" {{ $year->id === $currentYearId ? 'selected' : '' }}>{{ $year->year_label }}</option>
            @endforeach
        </select>
    </form>
    @endif
@endif
