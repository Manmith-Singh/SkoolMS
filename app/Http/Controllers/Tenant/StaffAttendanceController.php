<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\StaffAttendance;
use App\Models\Tenant\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = StaffAttendance::with('teacher')->orderByDesc('date');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date('date'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        $records = $query->paginate(30)->withQueryString();
        $teachers = Teacher::orderBy('first_name')->get();

        return view('staff_attendance.index', compact('records', 'teachers'));
    }

    public function mark(Request $request): View
    {
        $date = $request->input('date', today()->toDateString());
        $teachers = Teacher::where('status', 'working')->orderBy('first_name')->get();

        $attendances = StaffAttendance::whereDate('date', $date)
            ->get()
            ->keyBy('teacher_id');

        return view('staff_attendance.mark', compact('teachers', 'attendances', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date'         => ['required', 'date'],
            'attendances'  => ['required', 'array'],
            'attendances.*'=> ['required', 'in:present,absent,late,half_day,leave'],
        ]);

        foreach ($data['attendances'] as $teacherId => $status) {
            StaffAttendance::updateOrCreate(
                ['teacher_id' => $teacherId, 'date' => $data['date']],
                [
                    'status'    => $status,
                    'marked_by' => Auth::user()?->name ?? 'system',
                ]
            );
        }

        return redirect()
            ->route('staff-attendance.index', ['date' => $data['date']])
            ->with('success', 'Staff attendance saved.');
    }
}
