<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\ExamType;
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
        $exams = Exam::with(['classes', 'examType', 'subjects'])->orderByDesc('from_date')->paginate(20);
        return view('exams.index', compact('exams'));
    }

    public function create(): View
    {
        $classes   = SchoolClass::orderBy('id')->get();
        $subjects  = Subject::with('classes')->orderBy('name')->get();
        $examTypes = ExamType::orderBy('name')->get();
        return view('exams.create', compact('classes', 'subjects', 'examTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'exam_type_id'  => ['required', 'exists:tenant.exam_types,id'],
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'class_id'      => ['required', 'array', 'min:1'],
            'class_id.*'    => ['required', 'exists:tenant.classes,id'],
            'max_marks'     => ['required', 'numeric', 'min:1'],
            'pass_marks'    => ['required', 'numeric', 'min:0', 'lte:max_marks'],
            'description'   => ['nullable', 'string'],
            'subjects'      => ['required', 'array', 'min:1'],
            'subjects.*.date'  => ['required', 'date'],
            'subjects.*.notes' => ['nullable', 'string', 'max:1000'],
            'subjects.*.order' => ['nullable', 'integer', 'min:0'],
        ]);

        $classIds = $data['class_id'];

        $exam = Exam::create([
            'name'         => $data['name'],
            'exam_type_id' => $data['exam_type_id'],
            'from_date'    => $data['from_date'],
            'to_date'      => $data['to_date'],
            'class_id'     => (int) collect($classIds)->first(),
            'max_marks'    => $data['max_marks'],
            'pass_marks'   => $data['pass_marks'],
            'description'  => $data['description'] ?? null,
        ]);

        $exam->classes()->sync($classIds);

        $subjectSync = [];
        foreach ($data['subjects'] as $subjectId => $details) {
            $subjectSync[$subjectId] = [
                'date'  => $details['date'],
                'notes' => $details['notes'] ?? null,
                'order' => $details['order'] ?? 0,
            ];
        }
        $exam->subjects()->sync($subjectSync);

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
        $exam->load(['classes', 'examType', 'subjects', 'results.student']);
        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam): View
    {
        $classes   = SchoolClass::orderBy('id')->get();
        $subjects  = Subject::with('classes')->orderBy('name')->get();
        $examTypes = ExamType::orderBy('name')->get();
        $exam->load('classes', 'subjects');
        return view('exams.edit', compact('exam', 'classes', 'subjects', 'examTypes'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'exam_type_id'  => ['required', 'exists:tenant.exam_types,id'],
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'class_id'      => ['required', 'array', 'min:1'],
            'class_id.*'    => ['required', 'exists:tenant.classes,id'],
            'max_marks'     => ['required', 'numeric', 'min:1'],
            'pass_marks'    => ['required', 'numeric', 'min:0', 'lte:max_marks'],
            'description'   => ['nullable', 'string'],
            'subjects'      => ['required', 'array', 'min:1'],
            'subjects.*.date'  => ['required', 'date'],
            'subjects.*.notes' => ['nullable', 'string', 'max:1000'],
            'subjects.*.order' => ['nullable', 'integer', 'min:0'],
        ]);

        $classIds = $data['class_id'];

        $exam->update([
            'name'         => $data['name'],
            'exam_type_id' => $data['exam_type_id'],
            'from_date'    => $data['from_date'],
            'to_date'      => $data['to_date'],
            'class_id'     => (int) collect($classIds)->first(),
            'max_marks'    => $data['max_marks'],
            'pass_marks'   => $data['pass_marks'],
            'description'  => $data['description'] ?? null,
        ]);

        $exam->classes()->sync($classIds);

        $subjectSync = [];
        foreach ($data['subjects'] as $subjectId => $details) {
            $subjectSync[$subjectId] = [
                'date'  => $details['date'],
                'notes' => $details['notes'] ?? null,
                'order' => $details['order'] ?? 0,
            ];
        }
        $exam->subjects()->sync($subjectSync);

        return redirect()->route('exams.index')->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam removed.');
    }
}
