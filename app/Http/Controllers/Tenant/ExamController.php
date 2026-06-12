<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\Result;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with(['classes', 'subject'])->orderByDesc('date')->paginate(20);
        return view('exams.index', compact('exams'));
    }

    public function create(): View
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        return view('exams.create', compact('classes', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'class_id'  => ['required', 'array', 'min:1'],
            'class_id.*'=> ['required', 'exists:tenant.classes,id'],
            'subject_id'=> ['required', 'exists:tenant.subjects,id'],
            'date'      => ['required', 'date'],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'pass_marks'=> ['required', 'numeric', 'min:0', 'lte:max_marks'],
            'description' => ['nullable', 'string'],
        ]);
        $classIds = $data['class_id'];
        // Back-compat: keep the legacy class_id column populated with the first class.
        $data['class_id'] = (int) collect($classIds)->first();

        $exam = Exam::create($data);
        $exam->classes()->sync($classIds);

        // Pre-create blank results for every student in each selected class so
        // the teacher only has to fill in marks.
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        foreach ($studentIds as $sid) {
            Result::firstOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $sid],
                ['marks_obtained' => 0, 'grade' => null]
            );
        }

        return redirect()->route('exams.index')->with('success', 'Exam created and result sheet ready.');
    }

    public function show(Exam $exam): View
    {
        $exam->load(['classes', 'subject', 'results.student']);
        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam): View
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $exam->load('classes');
        return view('exams.edit', compact('exam', 'classes', 'subjects'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'class_id'  => ['required', 'array', 'min:1'],
            'class_id.*'=> ['required', 'exists:tenant.classes,id'],
            'subject_id'=> ['required', 'exists:tenant.subjects,id'],
            'date'      => ['required', 'date'],
            'max_marks' => ['required', 'numeric', 'min:1'],
            'pass_marks'=> ['required', 'numeric', 'min:0', 'lte:max_marks'],
            'description' => ['nullable', 'string'],
        ]);
        $classIds = $data['class_id'];
        $data['class_id'] = (int) collect($classIds)->first();

        $exam->update($data);
        $exam->classes()->sync($classIds);

        return redirect()->route('exams.index')->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam removed.');
    }
}
