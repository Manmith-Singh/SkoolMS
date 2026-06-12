<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Teacher::with('subject');

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
        return view('teachers.create', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTeacher($request);

        if (empty($data['employee_id'])) {
            $data['employee_id'] = 'T-' . date('Y') . '-' . str_pad((string) (Teacher::max('id') + 1), 4, '0', STR_PAD_LEFT);
        }

        Teacher::create($data);
        return redirect()->route('teachers.index')->with('success', 'Teacher added.');
    }

    public function show(Teacher $teacher): View
    {
        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $subjects = Subject::orderBy('name')->get();
        return view('teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $this->validateTeacher($request, $teacher->id);
        $teacher->update($data);
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
            'employee_id'   => ['nullable', 'string', 'max:50', 'unique:tenant.teachers,employee_id,' . ($id ?? 'NULL') . ',id'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:191', 'unique:tenant.teachers,email,' . ($id ?? 'NULL') . ',id'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'qualification' => ['nullable', 'string', 'max:191'],
            'hire_date'     => ['nullable', 'date'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'address'       => ['nullable', 'string'],
            'salary'        => ['nullable', 'numeric', 'min:0'],
            'subject_id'    => ['nullable', 'exists:tenant.subjects,id'],
        ]);
    }
}
