<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TeacherIncrement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherIncrementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id'     => ['required', 'exists:tenant.teachers,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'effective_date' => ['required', 'date'],
            'reason'         => ['nullable', 'string'],
        ]);

        TeacherIncrement::create($data);

        return back()->with('success', 'Increment added.');
    }

    public function destroy(TeacherIncrement $teacherIncrement): RedirectResponse
    {
        $teacherIncrement->delete();
        return back()->with('success', 'Increment deleted.');
    }
}
