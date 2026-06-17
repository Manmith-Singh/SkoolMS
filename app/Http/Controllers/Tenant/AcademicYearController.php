<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year_id' => ['required', 'exists:tenant.academic_years,id'],
        ]);

        session(['current_academic_year_id' => (int) $data['year_id']]);

        return redirect()->back()->with('success', 'Academic year switched.');
    }
}
