<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subject::with('classes');
        if ($request->filled('class_id')) {
            $cid = $request->integer('class_id');
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $cid));
        }
        $subjects = $query->orderBy('name')->paginate(20)->withQueryString();
        $classes  = SchoolClass::orderBy('name')->get();
        return view('subjects.index', compact('subjects', 'classes'));
    }

    public function create(): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('subjects.create', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSubject($request);
        $classIds = $data['class_id'];
        // Back-compat: keep the legacy class_id column populated with the first class.
        $data['class_id'] = (int) collect($classIds)->first();

        $subject = Subject::create($data);
        $subject->classes()->sync($classIds);

        return redirect()->route('subjects.index')->with('success', 'Subject created.');
    }

    public function show(Subject $subject): View
    {
        $subject->load('classes', 'teachers');
        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        $subject->load('classes');
        return view('subjects.edit', compact('subject', 'classes'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $this->validateSubject($request);
        $classIds = $data['class_id'];
        $data['class_id'] = (int) collect($classIds)->first();

        $subject->update($data);
        $subject->classes()->sync($classIds);

        return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Subject removed.');
    }

    protected function validateSubject(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['nullable', 'string', 'max:20'],
            'class_id'    => ['required', 'array', 'min:1'],
            'class_id.*'  => ['required', 'exists:tenant.classes,id'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
