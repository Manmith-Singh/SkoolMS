<?php

use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\BulkImportController;
use App\Http\Controllers\Tenant\ClassController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ExamController;
use App\Http\Controllers\Tenant\ExamTypeController;
use App\Http\Controllers\Tenant\ExpenditureTransactionController;
use App\Http\Controllers\Tenant\ExpenditureTypeController;
use App\Http\Controllers\Tenant\FeeCategoryController;
use App\Http\Controllers\Tenant\FeeController;
use App\Http\Controllers\Tenant\FeePaymentController;
use App\Http\Controllers\Tenant\IncomeTransactionController;
use App\Http\Controllers\Tenant\IncomeTypeController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ResultController;
use App\Http\Controllers\Tenant\StaffAttendanceController;
use App\Http\Controllers\Tenant\StudentController;
use App\Http\Controllers\Tenant\SubjectController;
use App\Http\Controllers\Tenant\TeacherController;
use App\Http\Controllers\Tenant\PayrollController;
use App\Http\Controllers\Tenant\TeacherIncrementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant (subdomain) routes
|--------------------------------------------------------------------------
| Loaded for every {tenant}.school.test request.  Auth required.
*/

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('master.login');
    }

    // If a super-admin somehow lands on a tenant subdomain, send them to the
    // master dashboard.  The IdentifyTenant middleware would normally
    // 404 them, but this is a safety net.
    if (auth()->user()->role === 'super_admin' || ! auth()->user()->tenant_id) {
        return redirect()->route('master.dashboard');
    }

    return redirect()->route('dashboard');
})->name('tenant.home');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students  (custom import routes MUST be declared before the
    // `students/{student}` resource wildcard, otherwise the wildcard
    // matches first and the import form 404s).
    Route::get('students/import',       [BulkImportController::class, 'studentsForm'])->name('students.import');
    Route::post('students/import',      [BulkImportController::class, 'studentsImport'])->name('students.import.store');
    Route::get('students/sample.xlsx',  [BulkImportController::class, 'studentsSample'])->name('students.sample');
    Route::resource('students', StudentController::class);

    // Teachers
    Route::get('teachers/import',       [BulkImportController::class, 'teachersForm'])->name('teachers.import');
    Route::post('teachers/import',      [BulkImportController::class, 'teachersImport'])->name('teachers.import.store');
    Route::get('teachers/sample.xlsx',  [BulkImportController::class, 'teachersSample'])->name('teachers.sample');
    Route::resource('teachers', TeacherController::class);

    // Classes
    Route::resource('classes', ClassController::class)->parameters(['classes' => 'class']);

    // Subjects
    Route::resource('subjects', SubjectController::class);

    // Exam Types
    Route::resource('exam-types', ExamTypeController::class)->except('show');

    // Exams
    Route::resource('exams', ExamController::class);

    // Results
    Route::get('exams/{exam}/marks', [ResultController::class, 'edit'])->name('results.edit');
    Route::put('exams/{exam}/marks', [ResultController::class, 'updateBulk'])->name('results.update');
    Route::get('results', [ResultController::class, 'index'])->name('results.index');

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Fee categories
    Route::resource('fees/categories', FeeCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->except(['create', 'show'])
        ->names('fees.categories');

    // Payments  (must be declared BEFORE the generic fees/{fee} show route,
    // otherwise Laravel matches `fees/payments` against `fees/{fee}` first
    // and 404s when the implicit model binding can't find a row).
    Route::get('fees/payments', [FeePaymentController::class, 'index'])->name('fees.payments.index');
    Route::get('fees/payments/search-student', [FeePaymentController::class, 'searchStudent'])->name('fees.payments.search-student');
    Route::get('fees/payments/create', [FeePaymentController::class, 'create'])->name('fees.payments.create');
    Route::post('fees/payments', [FeePaymentController::class, 'store'])->name('fees.payments.store');
    Route::get('fees/payments/{payment}/receipt', [FeePaymentController::class, 'receipt'])->name('fees.payments.receipt');
    Route::delete('fees/payments/{payment}', [FeePaymentController::class, 'destroy'])->name('fees.payments.destroy');

    // Fees (assignments)
    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('fees/create', [FeeController::class, 'create'])->name('fees.create');
    Route::post('fees', [FeeController::class, 'store'])->name('fees.store');
    Route::get('fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
    Route::delete('fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');

    // Income types
    Route::resource('income-types', IncomeTypeController::class)->except('show');

    // Income transactions
    Route::resource('income', IncomeTransactionController::class)->parameters(['income' => 'income'])->except('show');

    // Expenditure types
    Route::resource('expenditure-types', ExpenditureTypeController::class)->except('show');

    // Expenditure transactions
    Route::resource('expenditure', ExpenditureTransactionController::class)->parameters(['expenditure' => 'expenditure'])->except('show');

    // Staff attendance
    Route::get('staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::get('staff-attendance/mark', [StaffAttendanceController::class, 'mark'])->name('staff-attendance.mark');
    Route::post('staff-attendance', [StaffAttendanceController::class, 'store'])->name('staff-attendance.store');

    // Payroll
    Route::resource('payroll', PayrollController::class)->parameters(['payroll' => 'payroll']);
    Route::post('payroll/bulk-generate', [PayrollController::class, 'bulkGenerate'])->name('payroll.bulk-generate');
    Route::post('payroll/bulk-pay', [PayrollController::class, 'bulkPay'])->name('payroll.bulk-pay');

    // Teacher increments
    Route::post('teacher-increments', [TeacherIncrementController::class, 'store'])->name('teacher-increments.store');
    Route::delete('teacher-increments/{teacherIncrement}', [TeacherIncrementController::class, 'destroy'])->name('teacher-increments.destroy');

    // Academic Year (year selector)
    Route::post('academic-years/switch', [App\Http\Controllers\Tenant\AcademicYearController::class, 'switch'])->name('academic-years.switch');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/results', [ReportController::class, 'results'])->name('reports.results');
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/fees', [ReportController::class, 'fees'])->name('reports.fees');
    Route::get('reports/income', [ReportController::class, 'income'])->name('reports.income');
    Route::get('reports/expenditure', [ReportController::class, 'expenditure'])->name('reports.expenditure');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
});
