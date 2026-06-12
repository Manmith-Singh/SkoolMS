<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::withCount('tenants')->orderBy('sort_order')->get();

        return view('master.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('master.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePlan($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['features'] = $this->parseFeatures($request->input('features'));

        $plan = SubscriptionPlan::create($data);
        AuditLog::record('plan.created', $plan);

        return redirect()->route('master.plans.index')->with('success', "Plan '{$plan->name}' created.");
    }

    public function edit(SubscriptionPlan $plan): View
    {
        return view('master.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $this->validatePlan($request, $plan->id);
        $data['features'] = $this->parseFeatures($request->input('features'));
        $plan->update($data);

        AuditLog::record('plan.updated', $plan);

        return redirect()->route('master.plans.index')->with('success', "Plan '{$plan->name}' updated.");
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        $name = $plan->name;
        $plan->delete();
        AuditLog::record('plan.deleted', null, ['name' => $name]);

        return redirect()->route('master.plans.index')->with('success', "Plan '{$name}' deleted.");
    }

    private function validatePlan(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:191'],
            'slug'             => ['nullable', 'string', 'max:191', 'unique:subscription_plans,slug' . ($id ? ",{$id}" : '')],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price'            => ['required', 'numeric', 'min:0'],
            'currency'         => ['required', 'string', 'size:3'],
            'billing_period'   => ['required', 'in:monthly,quarterly,yearly'],
            'max_students'     => ['required', 'integer', 'min:0'],
            'max_teachers'     => ['required', 'integer', 'min:0'],
            'max_storage_mb'   => ['required', 'integer', 'min:0'],
            'sort_order'       => ['nullable', 'integer'],
            'is_active'        => ['nullable', 'boolean'],
        ]) + [
            'is_active' => (bool) $request->boolean('is_active'),
        ];
    }

    private function parseFeatures(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw))));
        return $features ?: null;
    }
}
