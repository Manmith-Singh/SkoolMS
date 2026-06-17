<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ExamType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamTypeController extends Controller
{
    public function index(): View
    {
        $examTypes = ExamType::orderBy('name')->paginate(20);
        return view('exam_types.index', compact('examTypes'));
    }

    public function create(): View
    {
        return view('exam_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:tenant.exam_types,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        ExamType::create($data);

        return redirect()->route('exam-types.index')->with('success', 'Exam type created.');
    }

    public function edit(ExamType $examType): View
    {
        return view('exam_types.edit', compact('examType'));
    }

    public function update(Request $request, ExamType $examType): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:tenant.exam_types,name,' . $examType->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $examType->update($data);

        return redirect()->route('exam-types.index')->with('success', 'Exam type updated.');
    }

    public function destroy(ExamType $examType): RedirectResponse
    {
        $examType->delete();
        return redirect()->route('exam-types.index')->with('success', 'Exam type removed.');
    }
}
