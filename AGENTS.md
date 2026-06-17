# SchoolMS — Agent Guide

## Architecture

- **Custom multi-tenant** (no `stancl/tenancy`). Subdomain-based.
- **Production domains**: Master at `skoolms.msitsols.com`, tenants at `{tenant}.msitsols.com`
- **Dev domains**: `school.test` (master), `{tenant}.school.test` (tenant). Both modes share the same code path.
- Three MySQL connections (inlined in `config/database.php`):
  - `mysql` (default) → `schoolms_master`
  - `tenant_template` → `schoolms_tenant_template`
  - `tenant` → dynamically switched per-request to `schoolms_{subdomain}`
- Per-tenant DB credentials stored in `tenants.db_username` and `tenants.db_password`; `switchConnection()` uses them as first priority, falls back to base config
- Pure Blade + Bootstrap 5 + DataTables + Chart.js (CDN, no build step)
- Windows/XAMPP at `C:\Users\manmi\OneDrive\Desktop\PROJECTS\SchoolMS`
- Hostinger shared hosting: PHP 8.2+, MySQL via cPanel, SSL via Let's Encrypt

## Middleware stack (applied in order)

1. `IdentifyTenant` — parses subdomain from host, binds `currentTenant`
2. `SwitchTenantDatabase` — swaps `tenant` connection to the tenant's DB
3. `EnsureTenantAccess` — cross-tenant & super-admin redirects
4. `role.access` — `RoleAccess` middleware, checks `config/permissions.php`

Registered in `bootstrap/app.php`. Tenant routes in `TenantServiceProvider::mapTenantRoutes()` apply `['web', 'auth', 'tenant.access', 'role.access']`.

## Route structure

- `routes/web.php` — empty (only `/up`)
- `routes/master.php` — login, school registration, SuperAdmin SaaS plane at `/admin/*`
- `routes/tenant.php` — full CRUD for schools, gated by auth+tenant.access+role.access
- Master dashboard at `/admin` (NOT `/dashboard`) to avoid URI collision with tenant `dashboard`

## RBAC (config/permissions.php)

- `admin_roles`: `['super_admin', 'admin']` — bypass `RoleAccess` entirely
- `roles`: `teacher`, `receptionist`, `student` — each lists allowed route name patterns with `.*` wildcard support
- `RoleAccess` middleware matches current route name against allowed patterns
- Sidebar in `partials/sidebar.blade.php` uses inline `$canAccess` closure for role-filtered nav
- SuperAdmin nav uses separate branch (`$user->isSuperAdmin() && $isMaster`)
- **Mobile sidebar** is an off-canvas drawer: Alpine.js toggle on hamburger, slide-in animation, backdrop overlay, Escape key to close
- **Accordion groups** (Academics, People, Finance, Reports) use Alpine.js `x-data` for open state, chevron rotation, and max-height CSS transition
- Active accordion section auto-opens via Blade `$inAcademics`/`$inPeople`/`$inFinance`/`$inReports` route detection
- Sidebar is fixed `16rem` on desktop (`≥768px`), slides from left on mobile; content gets `margin-left: 16rem` on desktop
- **Exams sub-accordion** inside Academics: nested accordion with `$inExams` detection, contains All Exams, Enter Marks, and Exam Types

## Models

- **Master** (`connection = 'mysql'`): `Tenant` (+ `isExpired()`, `reactivateAfterPayment()`), `User` (with `isSuperAdmin()`, `isAdmin()`, `isReceptionist()`, `isTeacher()` helpers), `SubscriptionPlan`, `Subscription`, `Invoice`, `InvoiceItem`, `Payment`, `SupportTicket`, `SupportTicketReply`, `AuditLog`, `SystemSetting`, `ApiKey`
- **Tenant** (`connection = 'tenant'`): `SchoolClass`, `Subject` (+ `classes()` belongsToMany), `Teacher` (+ `subjects()` BelongsToMany via `teacher_subject` pivot, `classTeacher()` BelongsTo), `Student`, `Exam` (+ `classes()` belongsToMany, `examType()` BelongsTo, `subjects()` BelongsToMany with pivot), `ExamType`, `Result`, `Attendance`, `FeeCategory`, `Fee`, `FeePayment`, `AcademicYear`, `StudentEnrollment`

## Academic Year Management

- `academic_years` table in each tenant DB (no `tenant_id` — avoids cross-DB FK issues).
- `student_enrollments` pivot: `student_id, class_id, academic_year_id, roll_no, status (active/graduated/transferred/dropped)`.
- 7 tables have nullable `academic_year_id` FK (`nullOnDelete`): `classes`, `subjects`, `exams`, `attendance`, `fees`, `fee_categories`, `exam_types`.
- Legacy year (2020–2025) seeded; all existing rows assigned to it.
- `AcademicYearScope` global scope filters by `session('current_academic_year_id')`. Can be disabled via `AcademicYearScope::disable()`.
- `Student` model: `enrollments()` HasMany, `currentEnrollment()` HasOne scoped to active academic year.
- `Teacher` model: `subjects()` BelongsToMany via `teacher_subject` pivot, `classTeacher()` BelongsTo.
- **SuperAdmin manages from master dashboard** via `TenantDatabaseManager::switchConnection()`.
- Year creation, duplication (classes, subjects, fee categories, exam types), and promotion done from master at `/admin/tenants/{tenant}/academic-years/*`.
- `AcademicYearDuplicationService`: clones classes (with id map), subjects (+ class sync), fee categories, exam types.
- `StudentPromotionService`: iterates active enrollments, creates new-year enrollment with promoted class_id, marks `SchoolClass.is_final_year` students as graduated. Supports numeric (`Class 5 → Class 6`) and Roman numeral (`X → XI`) class names.
- **Tenant side**: POST `/academic-years/switch` sets `session('current_academic_year_id')`. Year selector dropdown in navbar (visible to admin/superadmin only).
- Academic Years link on tenant show page in master dashboard.

## Multi-select class & section fields

- Used on: `subjects/_form`, `exams/_form`, `fees/fees/_form`, `attendance/index`, `attendance/mark`, `students/_form`, `subjects/index`, `results/index`, `reports/attendance`, `reports/results`
- Uses `partials/_class_section_fields.blade.php`: **two independent multi-select toggle-panel dropdowns**
  - **Classes** dropdown: lists unique class names (`name` column), multi-select
  - **Sections** dropdown: dynamically populates based on checked classes, shows individual sections
  - Section values are split on commas (handles `"A,B"` → `["A", "B"]`)
- Emits hidden `class_id[]` inputs computed from the cross-product of checked classes × sections
- Labels ("Classes *", "Sections *") hidden via `$hideLabels` param on attendance pages
- **Student form**: takes first selected class id (student belongs to one class)
- **Attendance mark**: students loaded from all selected classes; POST uses first class's ID
- **Controller filtering**: uses `whereIn()` for array class_id support

## Subject/Exam pivot tables

- `subject_class`: `id, subject_id, class_id, timestamps, unique(subject_id, class_id)`
- `exam_class`: `id, exam_id, class_id, timestamps, unique(exam_id, class_id)`
- `exam_subject`: `id, exam_id, subject_id, date, notes, order, timestamps, unique(exam_id, subject_id)`
- Controllers sync pivot via `$model->classes()->sync(...)`; back-compat `class_id` column set to first selected value
- BelongsToMany must specify **explicit foreign keys**: e.g. `belongsToMany(SchoolClass::class, 'subject_class', 'subject_id', 'class_id')` — without this Laravel defaults to `school_class_id`

## Exam creation

When an exam is created, `ExamController::store` syncs classes AND pre-creates blank `Result` rows for every student in each selected class.

### New exam architecture (June 2026)

- **`exam_types`** table: standalone lookup; seeded with 8 defaults (Midterm, Final, Quiz, Term, Monthly, Weekly, Pre-Board, Practical)
- **`exams`** table updated: added `exam_type_id` (FK), `from_date`, `to_date`; removed `subject_id` and `date` (subjects are now per-exam via pivot)
- **`exam_subject`** pivot: `exam_id`, `subject_id`, `date`, `notes` (portion), `order` — UNIQUE on `(exam_id, subject_id)`
- **Exam model** has `examType()` BelongsTo, `subjects()` BelongsToMany with pivot data, no more `subject()` BelongsTo
- **Create form flow**: select class(es) → select exam type → pick from/to dates → subjects of chosen classes appear; for each subject set date, notes/portion, and order
- **Subject filtering**: JS event on class checkbox change shows/hides `.subject-row` by `data-classes` attribute; disabled rows omit from submission
- **Exam index** shows Type, Class badges, Subject badges, and date period
- **Exam show** lists subjects sorted by `pivot.order` with date and portion notes

## Auto-suspend & auto-activate

- **`tenants:auto-suspend`** command (`app/Console/Commands/SuspendExpiredTenants.php`): runs daily via scheduler; queries tenants where `status=active` AND `subscription_ends_at < now()` → sets `status=suspended`, clears tenant cache, logs to AuditLog
- **Auto-activate on full payment**: `PaymentController::store` and `InvoiceController::markPaid` check if the tenant's invoice is fully paid (`amountDue() ≤ 0`); if the tenant is suspended and has a `plan_id`, calls `$tenant->reactivateAfterPayment()` which extends `subscription_ends_at` by the plan's billing period (monthly/quarterly/half_yearly/yearly/one_time) and sets `status=active`
- `Tenant` model requires `plan_id` and `subscription_ends_at` in `$fillable` and `$casts` (added June 2026)
- `Invoice::amountDue()` and `amountPaid()` helpers used by the auto-activate check
- `SubscriptionPlan.billing_period` enum: `monthly`, `quarterly`, `half_yearly`, `yearly`, `one_time`

## Exam types

- Full CRUD via `ExamTypeController` at `/exam-types` (resource except `show`)
- Sidebar link nested under Exams section, gated by `exam-types.index` permission
- Only `admin_roles` (super_admin, admin) can access; teachers/receptionists/students have no exam-type permissions
- Seeded with 8 defaults; `Route::resource('exam-types', ...)->except('show')` registered in `routes/tenant.php`

## Route ordering

Specific routes MUST precede resource wildcards:
- `students/import`, `students/sample.xlsx` BEFORE `Route::resource('students', ...)`
- `fees/payments/*`, `fees/categories/*` BEFORE `fees/{fee}`
- Same for teachers import

## Commands

```powershell
# Dev server (kill stale first)
Get-Process php | Stop-Process -Force
Start-Process php artisan serve --host=0.0.0.0 --port=8000 -WindowStyle Hidden

# Clear caches after changes
php artisan view:clear
php artisan config:clear

# Run tenant migrations (must specify the subdirectory path)
php artisan migrate --database=tenant --path=database/migrations/tenant

# Or use the custom command
php artisan tenant:migrate --subdomain={subdomain}

# Auto-suspend expired tenants (also runs daily via scheduler)
php artisan tenants:auto-suspend

# Tinker for DB writes on tenant connection
php artisan tinker
> app(\App\Services\TenantDatabaseManager::class)->switchConnection(\App\Models\Master\Tenant::find(1));
> Artisan::call('migrate', ['--database' => 'tenant']);
```

## Validation rules

Use `exists:tenant.table,column` syntax — `parseTable` in `SwitchTenantDatabase` splits on `.` and uses the named connection.

## URL building

Port-agnostic: `$tenant->url()` builds from `request()` (scheme+host+port). All tenant URLs use `school.test` domain base.

## Tenant provisioning (production)

On Hostinger shared hosting, provisioning is fully manual:
1. cPanel: create MySQL database `PREFIX_skoolms_{subdomain}` via phpMyAdmin
2. cPanel: create subdomain `{subdomain}.msitsols.com` pointing to same document root
3. cPanel: add MySQL user (or re-use existing) to the new database with ALL PRIVILEGES
4. SSH: `php artisan tenant:create "{name}" "{subdomain}" --db-name="PREFIX_skoolms_{subdomain}" --db-user="PREFIX_{subdomain}_admin" --db-password="..." --admin-email="admin@{subdomain}.msitsols.com" --admin-name="School Admin" --admin-password="..."`
5. SSH: `php artisan tenant:migrate --subdomain={subdomain}` to run tenant migrations
6. Tenant is live at `https://{subdomain}.msitsols.com`

### Quick reference — add a new school

```bash
# 1. cPanel: Create MySQL database → u114510322_skoolms_{subdomain}
# 2. cPanel: Create subdomain → {subdomain}.msitsols.com → doc root: public_html/skoolms/public
# 3. cPanel: Add MySQL user to the new DB → ALL PRIVILEGES
# 4. SSH:
cd ~/domains/msitsols.com/public_html/skoolms

php artisan tenant:create "School Name" "{subdomain}" \
    --db-name=u114510322_skoolms_{subdomain} \
    --db-user=u114510322_{subdomain}_admin \
    --db-password="password" \
    --admin-email=admin@{subdomain}.msitsols.com \
    --admin-name="School Admin" \
    --admin-password="password"

php artisan tenant:migrate --subdomain={subdomain}
```

### Fix existing tenant record (if created without DB credentials)

```bash
cd ~/domains/msitsols.com/public_html/skoolms

cat > fix.php << 'EOF'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$t = App\Models\Master\Tenant::where('subdomain', 'bmhs')->first();
if ($t) { $t->update(['db_username' => 'u114510322_bmhs_admin', 'db_password' => 'skoolMS@123']); echo "Done\n"; }
EOF
php fix.php && rm fix.php
```

## Test infrastructure

- Python E2E tests in `temp/`: `test_widget.py`, `test_select_modes.py`, `test_receptionist_rbac.py`, `test_bulk_import.py`, etc.
- BASE = `http://greenfield.school.test:8000` (tenant), `http://school.test:8000` (master)
- Tests use `urllib` with `HTTPCookieProcessor`; POST with `urlencode(doseq=True)` for array params
- Logout is POST only (GET returns 405)

## Key credentials

| Role | Email | Password |
|------|-------|----------|
| super_admin | superadmin@school.test | superadmin123 |
| admin | admin@greenfield.school.test | greenfield123 |
| receptionist | receptionist@greenfield.school.test | reception123 |

## Known gotchas

- Multiple `php artisan serve` processes accumulate → always kill first
- `users.role` is MySQL `ENUM('super_admin','admin','receptionist','teacher','student')` — extending it requires `ALTER TABLE MODIFY COLUMN`
- PowerShell `&` is reserved; avoid running commands that need it in bash tool — use tinker or Python scripts instead
- Checkbox-dropdown only sends checked values (native `<input type="checkbox">` behavior); unchecked values are absent from `class_id[]`
- Every controller write action calls `AuditLog::record()`
- `hosts` file entries required: `127.0.0.1 school.test`, `127.0.0.1 greenfield.school.test`, `127.0.0.1 lba.school.test`
- **Master subdomain** (`skoolms.msitsols.com` / `skoolms.school.test`) is excluded from tenant lookup in `IdentifyTenant` via `config('tenancy.master_subdomain')`
- **Production env**: `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` (no Redis on shared hosting)
- **HTTPS forced** in `AppServiceProvider` (`boot()`) for URL generation; `.htaccess` redirects HTTP → HTTPS
- **Single login** via `SESSION_DOMAIN=.msitsols.com` — session cookie shared across all subdomains. Tenant users log in at `skoolms.msitsols.com` and get redirected to their tenant subdomain.
- **Login throttled** at 5 attempts/minute via `throttle:5,1` middleware
- **Security headers** enforced by `SecureHeaders` middleware (X-Frame-Options, X-Content-Type-Options, Referrer-Policy) + `.htaccess` blocks hidden/sensitive files
- **DB provisioning** is fully manual on Hostinger: pre-create the MySQL database in cPanel (e.g. `PREFIX_skoolms_lba`) and create the subdomain (e.g. `lba.msitsols.com`) before adding a tenant record. `CreateTenantCommand` requires `--db-name` and does NOT attempt `CREATE DATABASE`.
- **School self-registration** disabled on production (`/register-school` moved under `super_admin` middleware). Only superadmins can create tenant records.
- **Tenant migration workflow**: after creating tenant record, run `php artisan tenant:migrate --subdomain={subdomain}` via SSH to run tenant migrations on the pre-created DB.
- **AcademicYearScope**: Global scope on 7 models; disabled via static method `AcademicYearScope::disable()` for cross-year queries.
