<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Tenant;
use App\Services\AcademicYearDuplicationService;
use App\Services\StudentPromotionService;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearMasterController extends Controller
{
    protected TenantDatabaseManager $tenantDb;

    public function __construct(TenantDatabaseManager $tenantDb)
    {
        $this->tenantDb = $tenantDb;
    }

    protected function switchToTenant(Tenant $tenant): void
    {
        $this->tenantDb->switchConnection($tenant);
    }

    public function index(Tenant $tenant): View
    {
        $this->switchToTenant($tenant);

        $years = \App\Models\Tenant\AcademicYear::orderByDesc('start_date')->get();
        $currentYear = $years->firstWhere('is_active', true);

        return view('master.academic-years.index', compact('tenant', 'years', 'currentYear'));
    }

    public function create(Tenant $tenant): View
    {
        $this->switchToTenant($tenant);

        $previousYear = \App\Models\Tenant\AcademicYear::orderByDesc('start_date')->first();

        return view('master.academic-years.create', compact('tenant', 'previousYear'));
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->switchToTenant($tenant);

        $data = $request->validate([
            'year_label'  => ['required', 'string', 'max:20'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after:start_date'],
            'duplicate'   => ['nullable', 'boolean'],
            'promote'     => ['nullable', 'boolean'],
        ]);

        $previousYear = \App\Models\Tenant\AcademicYear::orderByDesc('start_date')->first();
        $previousId = $previousYear?->id;

        // Deactivate previous active year
        \App\Models\Tenant\AcademicYear::where('is_active', true)->update(['is_active' => false]);

        // Create new year
        $year = \App\Models\Tenant\AcademicYear::create([
            'year_label' => $data['year_label'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'is_active'  => true,
        ]);

        $stats = [];

        // Duplicate previous year data
        if (! empty($data['duplicate']) && $previousId) {
            $duplicationService = app(AcademicYearDuplicationService::class);
            $stats['duplicate'] = $duplicationService->duplicate($previousId, $year->id);
        }

        // Promote students
        if (! empty($data['promote']) && $previousId) {
            $promotionService = app(StudentPromotionService::class);
            $stats['promote'] = $promotionService->promote($previousId, $year->id);
        }

        return redirect()
            ->route('master.tenants.academic-years', $tenant)
            ->with('success', 'Academic year created.')
            ->with('stats', $stats);
    }

    public function setActive(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->switchToTenant($tenant);

        $data = $request->validate([
            'year_id' => ['required', 'exists:tenant.academic_years,id'],
        ]);

        \App\Models\Tenant\AcademicYear::where('is_active', true)->update(['is_active' => false]);
        \App\Models\Tenant\AcademicYear::where('id', $data['year_id'])->update(['is_active' => true]);

        return redirect()
            ->route('master.tenants.academic-years', $tenant)
            ->with('success', 'Active academic year changed.');
    }

    public function duplicate(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->switchToTenant($tenant);

        $data = $request->validate([
            'from_year_id' => ['required', 'exists:tenant.academic_years,id'],
            'to_year_id'   => ['required', 'exists:tenant.academic_years,id', 'different:from_year_id'],
        ]);

        $service = app(AcademicYearDuplicationService::class);
        $stats = $service->duplicate($data['from_year_id'], $data['to_year_id']);

        return redirect()
            ->route('master.tenants.academic-years', $tenant)
            ->with('success', 'Data duplicated: ' . json_encode($stats));
    }

    public function promote(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->switchToTenant($tenant);

        $data = $request->validate([
            'from_year_id' => ['required', 'exists:tenant.academic_years,id'],
            'to_year_id'   => ['required', 'exists:tenant.academic_years,id', 'different:from_year_id'],
        ]);

        $service = app(StudentPromotionService::class);
        $stats = $service->promote($data['from_year_id'], $data['to_year_id']);

        return redirect()
            ->route('master.tenants.academic-years', $tenant)
            ->with('success', 'Promotion complete: ' . json_encode($stats));
    }
}
