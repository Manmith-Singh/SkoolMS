<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FeeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeCategoryController extends Controller
{
    public function index(): View
    {
        $categories = FeeCategory::orderBy('name')->paginate(25);
        return view('fees.categories.index', compact('categories'));
    }

    public function edit(FeeCategory $category): View
    {
        return view('fees.categories.edit', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'frequency'      => ['required', 'in:one_time,monthly,quarterly,half_yearly,annually'],
            'is_active'      => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        FeeCategory::create($data);
        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, FeeCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'frequency'      => ['required', 'in:one_time,monthly,quarterly,half_yearly,annually'],
            'is_active'      => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function destroy(FeeCategory $category): RedirectResponse
    {
        $category->delete();
        return back()->with('success', 'Category removed.');
    }
}
