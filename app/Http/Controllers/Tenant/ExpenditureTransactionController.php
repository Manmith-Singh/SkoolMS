<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ExpenditureTransaction;
use App\Models\Tenant\ExpenditureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenditureTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExpenditureTransaction::with('expenditureType')->orderByDesc('date');

        if ($request->filled('expenditure_type_id')) {
            $query->where('expenditure_type_id', $request->integer('expenditure_type_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->date('to'));
        }

        $transactions = $query->paginate(25)->withQueryString();
        $types = ExpenditureType::orderBy('name')->get();
        $total = (clone $query)->sum('amount');

        return view('expenditure.index', compact('transactions', 'types', 'total'));
    }

    public function create(): View
    {
        $types = ExpenditureType::where('is_active', true)->orderBy('name')->get();
        return view('expenditure.create', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expenditure_type_id' => ['required', 'exists:tenant.expenditure_types,id'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'date'                => ['required', 'date'],
            'description'         => ['nullable', 'string'],
            'reference'           => ['nullable', 'string', 'max:100'],
            'paid_by'             => ['nullable', 'string', 'max:100'],
            'approved_by'         => ['nullable', 'string', 'max:100'],
        ]);

        ExpenditureTransaction::create($data);

        return redirect()->route('expenditure.index')->with('success', 'Expenditure recorded.');
    }

    public function edit(ExpenditureTransaction $expenditure): View
    {
        $types = ExpenditureType::where('is_active', true)->orderBy('name')->get();
        return view('expenditure.edit', compact('expenditure', 'types'));
    }

    public function update(Request $request, ExpenditureTransaction $expenditure): RedirectResponse
    {
        $data = $request->validate([
            'expenditure_type_id' => ['required', 'exists:tenant.expenditure_types,id'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'date'                => ['required', 'date'],
            'description'         => ['nullable', 'string'],
            'reference'           => ['nullable', 'string', 'max:100'],
            'paid_by'             => ['nullable', 'string', 'max:100'],
            'approved_by'         => ['nullable', 'string', 'max:100'],
        ]);

        $expenditure->update($data);

        return redirect()->route('expenditure.index')->with('success', 'Expenditure updated.');
    }

    public function destroy(ExpenditureTransaction $expenditure): RedirectResponse
    {
        $expenditure->delete();
        return redirect()->route('expenditure.index')->with('success', 'Expenditure deleted.');
    }
}
