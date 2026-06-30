<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Exam;
use App\Models\Tenant\Result;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $query = Result::with(['exam.classes', 'exam.subjects', 'student']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->integer('exam_id'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->integer('student_id'));
        }
        $classIds = $request->input('class_id');
        if (! empty($classIds) && is_array($classIds)) {
            $classIds = array_values(array_filter($classIds));
            if (! empty($classIds)) {
                $query->whereHas('student', fn ($q) => $q->whereIn('class_id', $classIds));
            }
        }

        $results = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $exams   = Exam::orderByDesc('from_date')->limit(50)->get();
        $students = Student::orderBy('first_name')->limit(200)->get();
        $classes = SchoolClass::orderBy('id')->get();

        return view('results.index', compact('results', 'exams', 'students', 'classes'));
    }

    public function edit(Exam $exam): View
    {
        $exam->load(['classes', 'subjects', 'results.student']);
        return view('results.edit', compact('exam'));
    }

    public function updateBulk(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'marks'   => ['required', 'array'],
            'marks.*' => ['nullable', 'numeric', 'min:0', 'max:' . $exam->max_marks],
            'remarks' => ['nullable', 'array'],
        ]);

        foreach ($request->input('marks', []) as $resultId => $marks) {
            $result = Result::where('id', $resultId)->where('exam_id', $exam->id)->first();
            if (! $result) continue;

            $result->marks_obtained = (float) ($marks ?? 0);
            $result->grade          = $this->gradeFor((float) $result->marks_obtained, (float) $exam->max_marks);
            $result->remarks        = $request->input("remarks.{$resultId}");
            $result->save();
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Marks saved.');
    }

    protected function gradeFor(float $marks, float $max): string
    {
        if ($max <= 0) return 'N/A';
        $pct = ($marks / $max) * 100;
        return match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 70 => 'B+',
            $pct >= 60 => 'B',
            $pct >= 50 => 'C',
            $pct >= 33 => 'D',
            default    => 'F',
        };
    }
}
