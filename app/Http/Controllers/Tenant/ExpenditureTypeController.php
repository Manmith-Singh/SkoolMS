<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ExpenditureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenditureTypeController extends Controller
{
    public function index(): View
    {
        $types = ExpenditureType::orderBy('name')->paginate(25);
        return view('expenditure_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('expenditure_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        ExpenditureType::create($data);
        return redirect()->route('expenditure-types.index')->with('success', 'Expenditure type created.');
    }

    public function edit(ExpenditureType $expenditureType): View
    {
        return view('expenditure_types.edit', compact('expenditureType'));
    }

    public function update(Request $request, ExpenditureType $expenditureType): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $expenditureType->update($data);
        return redirect()->route('expenditure-types.index')->with('success', 'Expenditure type updated.');
    }

    public function destroy(ExpenditureType $expenditureType): RedirectResponse
    {
        $expenditureType->delete();
        return redirect()->route('expenditure-types.index')->with('success', 'Expenditure type deleted.');
    }
}
