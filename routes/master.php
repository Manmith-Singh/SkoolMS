<?php

use App\Http\Controllers\Master\ApiKeyController;
use App\Http\Controllers\Master\AuditController;
use App\Http\Controllers\Master\AuthController;
use App\Http\Controllers\Master\InvoiceController;
use App\Http\Controllers\Master\MasterDashboardController;
use App\Http\Controllers\Master\PaymentController;
use App\Http\Controllers\Master\ReportController;
use App\Http\Controllers\Master\SecurityController;
use App\Http\Controllers\Master\SubscriptionPlanController;
use App\Http\Controllers\Master\SupportTicketController;
use App\Http\Controllers\Master\SystemSettingController;
use App\Http\Controllers\Master\AcademicYearMasterController;
use App\Http\Controllers\Master\TenantController;
use App\Http\Controllers\Master\TenantRegistrationController;
use App\Http\Controllers\Master\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master (apex) domain routes
|--------------------------------------------------------------------------
| Loaded when there is NO subdomain (i.e. school.test itself).
| Handles login + school self-registration + the SuperAdmin SaaS control
| plane.
|
| IMPORTANT — the master dashboard is mounted at `/admin` (not `/dashboard`)
| to avoid a URI collision with the tenant `dashboard` route.  Laravel's
| router uses a single route collection for the whole app regardless of
| subdomain, so two routes with the same URI+method would overwrite each
| other and the named-route lookup would fail.
*/

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->tenant) {
            return redirect()->away($user->tenant->url() . '/dashboard');
        }
        return redirect()->route('master.dashboard');
    }
    return redirect()->route('master.login');
})->name('master.home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('master.login');
    Route::post('/login', [AuthController::class, 'login'])->name('master.login.attempt')->middleware('throttle:5,1');
});



/*
|--------------------------------------------------------------------------
| SuperAdmin-only SaaS control plane
|--------------------------------------------------------------------------
| Gated by the `super_admin` middleware alias (see SuperAdminOnly.php):
|  - unauthenticated  → redirected to master.login
|  - tenant users     → 403
|  - super_admin only → access
*/
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('master.')->group(function () {
    // Dashboard
    Route::get('/', [MasterDashboardController::class, 'index'])->name('dashboard');

    // Tenant management
    Route::get   ('/tenants',                          [TenantController::class, 'index'])->name('tenants.index');
    Route::get   ('/tenants/create',                   [TenantController::class, 'create'])->name('tenants.create');
    Route::post  ('/tenants',                          [TenantController::class, 'store'])->name('tenants.store');
    Route::get   ('/tenants/{tenant}',                 [TenantController::class, 'show'])->name('tenants.show');
    Route::get   ('/tenants/{tenant}/edit',            [TenantController::class, 'edit'])->name('tenants.edit');
    Route::put   ('/tenants/{tenant}',                 [TenantController::class, 'update'])->name('tenants.update');
    Route::post  ('/tenants/{tenant}/suspend',         [TenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post  ('/tenants/{tenant}/activate',        [TenantController::class, 'activate'])->name('tenants.activate');
    Route::delete('/tenants/{tenant}',                 [TenantController::class, 'destroy'])->name('tenants.destroy');

    // Academic Year Management (per-tenant)
    Route::get   ('/tenants/{tenant}/academic-years',                          [AcademicYearMasterController::class, 'index'])->name('tenants.academic-years');
    Route::get   ('/tenants/{tenant}/academic-years/create',                   [AcademicYearMasterController::class, 'create'])->name('tenants.academic-years.create');
    Route::post  ('/tenants/{tenant}/academic-years',                          [AcademicYearMasterController::class, 'store'])->name('tenants.academic-years.store');
    Route::post  ('/tenants/{tenant}/academic-years/set-active',               [AcademicYearMasterController::class, 'setActive'])->name('tenants.academic-years.set-active');
    Route::post  ('/tenants/{tenant}/academic-years/duplicate',                [AcademicYearMasterController::class, 'duplicate'])->name('tenants.academic-years.duplicate');
    Route::post  ('/tenants/{tenant}/academic-years/promote',                  [AcademicYearMasterController::class, 'promote'])->name('tenants.academic-years.promote');

    // School registration (superadmin-only on shared hosting)
    Route::get   ('/register-school',                  [TenantRegistrationController::class, 'showForm'])->name('register');
    Route::post  ('/register-school',                  [TenantRegistrationController::class, 'register'])->name('register.store');
    Route::get   ('/register-school/success/{subdomain}', [TenantRegistrationController::class, 'success'])->name('register.success');

    // User management
    Route::get   ('/users',                  [UserController::class, 'index'])->name('users.index');
    Route::get   ('/users/create',           [UserController::class, 'create'])->name('users.create');
    Route::post  ('/users',                  [UserController::class, 'store'])->name('users.store');
    Route::get   ('/users/{user}/edit',      [UserController::class, 'edit'])->name('users.edit');
    Route::put   ('/users/{user}',           [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}',           [UserController::class, 'destroy'])->name('users.destroy');

    // Subscription plans
    Route::get   ('/plans',                  [SubscriptionPlanController::class, 'index'])->name('plans.index');
    Route::get   ('/plans/create',           [SubscriptionPlanController::class, 'create'])->name('plans.create');
    Route::post  ('/plans',                  [SubscriptionPlanController::class, 'store'])->name('plans.store');
    Route::get   ('/plans/{plan}/edit',      [SubscriptionPlanController::class, 'edit'])->name('plans.edit');
    Route::put   ('/plans/{plan}',           [SubscriptionPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}',           [SubscriptionPlanController::class, 'destroy'])->name('plans.destroy');

    // Invoices
    Route::get   ('/invoices',                       [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get   ('/invoices/create',                [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post  ('/invoices',                       [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get   ('/invoices/{invoice}',             [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post  ('/invoices/{invoice}/mark-paid',   [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::delete('/invoices/{invoice}',             [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Payments
    Route::get ('/payments',              [PaymentController::class, 'index'])->name('payments.index');
    Route::get ('/payments/create',       [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments',              [PaymentController::class, 'store'])->name('payments.store');

    // System settings
    Route::get ('/settings',              [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings',              [SystemSettingController::class, 'update'])->name('settings.update');

    // API keys
    Route::get   ('/api-keys',                  [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::get   ('/api-keys/create',           [ApiKeyController::class, 'create'])->name('api-keys.create');
    Route::post  ('/api-keys',                  [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::get   ('/api-keys/{apiKey}',         [ApiKeyController::class, 'show'])->name('api-keys.show');
    Route::delete('/api-keys/{apiKey}',         [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

    // Reports
    Route::get('/reports',           [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/revenue',   [ReportController::class, 'revenue'])->name('reports.revenue');

    // Support tickets
    Route::get   ('/tickets',                 [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get   ('/tickets/create',          [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::post  ('/tickets',                 [SupportTicketController::class, 'store'])->name('tickets.store');
    Route::get   ('/tickets/{ticket}',        [SupportTicketController::class, 'show'])->name('tickets.show');
    Route::put   ('/tickets/{ticket}',        [SupportTicketController::class, 'update'])->name('tickets.update');
    Route::post  ('/tickets/{ticket}/reply',  [SupportTicketController::class, 'reply'])->name('tickets.reply');

    // Security
    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::put('/security/users/{user}/role', [SecurityController::class, 'updateRole'])->name('security.role');

    // Audit log
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
