<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::withCount('students')->orderBy('id')->paginate(20);
        return view('classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('classes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'section'     => ['nullable', 'string', 'max:20'],
            'capacity'    => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $data['capacity'] = $data['capacity'] ?? 40;
        SchoolClass::create($data);
        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    public function show(SchoolClass $class): View
    {
        $class->load(['students', 'subjects']);
        return view('classes.show', compact('class'));
    }

    public function edit(SchoolClass $class): View
    {
        return view('classes.edit', compact('class'));
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'section'     => ['nullable', 'string', 'max:20'],
            'capacity'    => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);
        $class->update($data);
        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class removed.');
    }
}
