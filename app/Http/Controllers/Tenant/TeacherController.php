<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Teacher::with('subject', 'subjects', 'classTeacher');

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('employee_id', 'like', $term);
            });
        }

        $teachers = $query->orderBy('employee_id')->paginate(20)->withQueryString();
        return view('teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('name')->get();
        $classes  = SchoolClass::orderBy('id')->get();
        return view('teachers.create', compact('subjects', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTeacher($request);

        if (empty($data['employee_id'])) {
            $data['employee_id'] = 'T-' . date('Y') . '-' . str_pad((string) (Teacher::max('id') + 1), 4, '0', STR_PAD_LEFT);
        }

        $subjectIds = $data['subject_id'] ?? [];
        unset($data['subject_id']);

        $teacher = Teacher::create($data);
        $teacher->subjects()->sync($subjectIds);

        return redirect()->route('teachers.index')->with('success', 'Teacher added.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->load('subject', 'subjects', 'classTeacher', 'increments');
        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $subjects = Subject::orderBy('name')->get();
        $classes  = SchoolClass::orderBy('id')->get();
        $teacher->load('subjects');
        return view('teachers.edit', compact('teacher', 'subjects', 'classes'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $this->validateTeacher($request, $teacher->id);

        $subjectIds = $data['subject_id'] ?? [];
        unset($data['subject_id']);

        $teacher->update($data);
        $teacher->subjects()->sync($subjectIds);

        return redirect()->route('teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Teacher removed.');
    }

    protected function validateTeacher(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'employee_id'     => ['nullable', 'string', 'max:50', 'unique:tenant.teachers,employee_id,' . ($id ?? 'NULL') . ',id'],
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:191', 'unique:tenant.teachers,email,' . ($id ?? 'NULL') . ',id'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'qualification'   => ['nullable', 'string', 'max:191'],
            'hire_date'       => ['nullable', 'date'],
            'gender'          => ['nullable', 'in:male,female,other'],
            'address'         => ['nullable', 'string'],
            'salary'          => ['nullable', 'numeric', 'min:0'],
            'subject_id'      => ['nullable', 'array'],
            'subject_id.*'    => ['required', 'exists:tenant.subjects,id'],
            'class_teacher_id'=> ['nullable', 'exists:tenant.classes,id'],
            'status'          => ['nullable', 'string', 'in:working,resigned,transfer'],
            'pf_number'       => ['nullable', 'string', 'max:50'],
            'esi_number'      => ['nullable', 'string', 'max:50'],
            'uan_number'      => ['nullable', 'string', 'max:50'],
            'bank_account'    => ['nullable', 'string', 'max:30'],
            'ifsc_code'       => ['nullable', 'string', 'max:20'],
            'basic_pay'       => ['nullable', 'numeric', 'min:0'],
            'hra'             => ['nullable', 'numeric', 'min:0'],
            'da'              => ['nullable', 'numeric', 'min:0'],
            'conveyance'      => ['nullable', 'numeric', 'min:0'],
            'medical_allowance'=> ['nullable', 'numeric', 'min:0'],
            'other_allowances' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
