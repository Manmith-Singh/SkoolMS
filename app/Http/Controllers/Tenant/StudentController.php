<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with('schoolClass');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }
        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('admission_no', 'like', $term);
            });
        }

        $students = $query->orderBy('admission_no')->get();
        $classes  = SchoolClass::orderBy('id')->get();

        return view('students.index', compact('students', 'classes'));
    }

    public function create(): View
    {
        $classes = SchoolClass::orderBy('id')->get();
        return view('students.create', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStudent($request);

        if (empty($data['admission_no'])) {
            $data['admission_no'] = 'ADM-' . now()->format('Ymd') . '-' . str_pad((string) (Student::max('id') + 1), 4, '0', STR_PAD_LEFT);
        }

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Student added successfully.');
    }

    public function show(Student $student): View
    {
        $student->load(['schoolClass', 'attendance', 'results.exam', 'fees.category', 'feePayments']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $classes = SchoolClass::orderBy('id')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validateStudent($request);
        $student->update($data);
        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student removed.');
    }

    protected function validateStudent(Request $request): array
    {
        $studentId = $request->route('student')?->id;

        $data = $request->validate([
            'admission_no'   => ['nullable', 'string', 'max:50', 'unique:tenant.students,admission_no,' . ($studentId ?? 'NULL') . ',id'],
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'roll_no'        => ['nullable', 'string', 'max:20'],
            'dob'            => ['nullable', 'date'],
            'gender'         => ['nullable', 'in:male,female,other'],
            'email'          => ['nullable', 'email', 'max:191'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'address'        => ['nullable', 'string'],
            'guardian_name'  => ['nullable', 'string', 'max:191'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'father_name'    => ['nullable', 'string', 'max:191'],
            'mother_name'    => ['nullable', 'string', 'max:191'],
            'pen_id'         => ['nullable', 'string', 'max:50'],
            'caste'          => ['nullable', 'string', 'in:' . implode(',', \App\Models\Tenant\Student::CASTES)],
            'aadhaar_number' => ['nullable', 'string', 'size:12'],
            'status'         => ['nullable', 'string', 'in:active,inactive,drop'],
            'admission_date' => ['nullable', 'date'],
            'class_id'       => ['nullable', 'array'],
            'class_id.*'     => ['nullable', 'exists:tenant.classes,id'],
        ]);

        $classIds = (array) ($data['class_id'] ?? []);
        $classIds = array_values(array_filter($classIds));
        $data['class_id'] = ! empty($classIds) ? (int) $classIds[0] : null;

        return $data;
    }
}
