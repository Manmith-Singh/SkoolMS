<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attendance::with(['student', 'schoolClass'])->orderByDesc('date');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date('date'));
        }
        $classIds = $request->input('class_id');
        if (! empty($classIds) && is_array($classIds)) {
            $classIds = array_filter($classIds);
            if (! empty($classIds)) {
                $query->whereIn('class_id', $classIds);
            }
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $records = $query->paginate(30)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('attendance.index', compact('records', 'classes'));
    }

    public function mark(): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        $students = collect();
        $selectedClass = null;
        $date = request('date', today()->toDateString());

        $classIds = request()->input('class_id');
        if (! empty($classIds) && is_array($classIds)) {
            $classIds = array_filter($classIds);
            if (! empty($classIds)) {
                $selectedClass = SchoolClass::find((int) $classIds[0]);
                $students = Student::whereIn('class_id', $classIds)->orderBy('roll_no')->orderBy('first_name')->get();
            }
        }

        return view('attendance.mark', compact('classes', 'students', 'selectedClass', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id'     => ['required', 'exists:tenant.classes,id'],
            'date'         => ['required', 'date'],
            'attendances'  => ['required', 'array'],
            'attendances.*'=> ['required', 'in:present,absent,late,half_day'],
        ]);

        foreach ($data['attendances'] as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $data['date']],
                [
                    'class_id' => $data['class_id'],
                    'status'   => $status,
                ]
            );
        }

        return redirect()
            ->route('attendance.index', ['date' => $data['date'], 'class_id' => [$data['class_id']]])
            ->with('success', 'Attendance saved.');
    }
}
