<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Fee;
use App\Models\Tenant\FeeCategory;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Fee::with(['student.schoolClass', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->integer('class_id')));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $fees = $query->orderBy('due_date')->paginate(25)->withQueryString();
        $classes    = SchoolClass::orderBy('id')->get();
        $categories = FeeCategory::orderBy('name')->get();

        return view('fees.fees.index', compact('fees', 'classes', 'categories'));
    }

    public function create(): View
    {
        $students    = Student::with('schoolClass')->orderBy('first_name')->get();
        $categories  = FeeCategory::where('is_active', true)->orderBy('name')->get();
        $classes     = SchoolClass::orderBy('id')->get();
        return view('fees.fees.create', compact('students', 'categories', 'classes'));
    }

    /**
     * Assign a fee to a single student, to a whole class, or to every student.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:tenant.fee_categories,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'due_date'     => ['required', 'date'],
            'assignment'   => ['required', 'in:student,class,all'],
            'student_id'   => ['nullable', 'required_if:assignment,student', 'exists:tenant.students,id'],
            'class_id'     => ['nullable', 'required_if:assignment,class', 'array', 'min:1'],
            'class_id.*'   => ['required', 'exists:tenant.classes,id'],
            'notes'        => ['nullable', 'string'],
        ]);

        if ($data['assignment'] === 'class') {
            $data['class_id'] = (int) collect($data['class_id'])->first();
        }

        $students = match ($data['assignment']) {
            'student' => Student::where('id', $data['student_id'])->get(),
            'class'   => Student::where('class_id', $data['class_id'])->get(),
            'all'     => Student::all(),
        };

        if ($students->isEmpty()) {
            return back()->withErrors(['student_id' => 'No matching students found.']);
        }

        foreach ($students as $student) {
            Fee::create([
                'student_id'  => $student->id,
                'category_id' => $data['category_id'],
                'amount'      => $data['amount'],
                'paid_amount' => 0,
                'due_date'    => $data['due_date'],
                'status'      => 'pending',
                'notes'       => $data['notes'] ?? null,
            ]);
        }

        return redirect()->route('fees.index')->with('success', "Fee assigned to {$students->count()} student(s).");
    }

    public function show(Fee $fee): View
    {
        $fee->load(['student.schoolClass', 'category', 'payments']);
        return view('fees.fees.show', compact('fee'));
    }

    public function destroy(Fee $fee): RedirectResponse
    {
        $fee->delete();
        return back()->with('success', 'Fee removed.');
    }
}
