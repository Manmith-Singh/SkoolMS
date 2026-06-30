<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\IncomeTransaction;
use App\Models\Tenant\IncomeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = IncomeTransaction::with('incomeType')->orderByDesc('date');

        if ($request->filled('income_type_id')) {
            $query->where('income_type_id', $request->integer('income_type_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->date('to'));
        }

        $transactions = $query->paginate(25)->withQueryString();
        $types = IncomeType::orderBy('name')->get();
        $total = (clone $query)->sum('amount');

        return view('income.index', compact('transactions', 'types', 'total'));
    }

    public function create(): View
    {
        $types = IncomeType::where('is_active', true)->orderBy('name')->get();
        return view('income.create', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'income_type_id' => ['required', 'exists:tenant.income_types,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'date'           => ['required', 'date'],
            'description'    => ['nullable', 'string'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'received_by'    => ['nullable', 'string', 'max:100'],
        ]);

        IncomeTransaction::create($data);

        return redirect()->route('income.index')->with('success', 'Income recorded.');
    }

    public function edit(IncomeTransaction $income): View
    {
        $types = IncomeType::where('is_active', true)->orderBy('name')->get();
        return view('income.edit', compact('income', 'types'));
    }

    public function update(Request $request, IncomeTransaction $income): RedirectResponse
    {
        $data = $request->validate([
            'income_type_id' => ['required', 'exists:tenant.income_types,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'date'           => ['required', 'date'],
            'description'    => ['nullable', 'string'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'received_by'    => ['nullable', 'string', 'max:100'],
        ]);

        $income->update($data);

        return redirect()->route('income.index')->with('success', 'Income updated.');
    }

    public function destroy(IncomeTransaction $income): RedirectResponse
    {
        $income->delete();
        return redirect()->route('income.index')->with('success', 'Income deleted.');
    }
}
