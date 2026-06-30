<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\IncomeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeTypeController extends Controller
{
    public function index(): View
    {
        $types = IncomeType::orderBy('name')->paginate(25);
        return view('income_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('income_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        IncomeType::create($data);
        return redirect()->route('income-types.index')->with('success', 'Income type created.');
    }

    public function edit(IncomeType $incomeType): View
    {
        return view('income_types.edit', compact('incomeType'));
    }

    public function update(Request $request, IncomeType $incomeType): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $incomeType->update($data);
        return redirect()->route('income-types.index')->with('success', 'Income type updated.');
    }

    public function destroy(IncomeType $incomeType): RedirectResponse
    {
        $incomeType->delete();
        return redirect()->route('income-types.index')->with('success', 'Income type deleted.');
    }
}
