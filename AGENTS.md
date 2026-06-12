# SchoolMS — Agent Guide

## Architecture

- **Custom multi-tenant** (no `stancl/tenancy`). Subdomain-based.
- **Production domains**: Master at `skoolms.msitsols.com`, tenants at `{tenant}.msitsols.com`
- **Dev domains**: `school.test` (master), `{tenant}.school.test` (tenant). Both modes share the same code path.
- **Dev domains**: `school.test` (master), `{tenant}.school.test` (tenant). Both modes share the same code path.
- Three MySQL connections (inlined in `config/database.php`):
  - `mysql` (default) → `schoolms_master`
  - `tenant_template` → `schoolms_tenant_template`
  - `tenant` → dynamically switched per-request to `schoolms_{subdomain}`
- Shared MySQL user for all tenant DBs; no per-tenant credentials
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

## Models

- **Master** (`connection = 'mysql'`): `Tenant`, `User` (with `isSuperAdmin()`, `isAdmin()`, `isReceptionist()`, `isTeacher()` helpers), `SubscriptionPlan`, `Subscription`, `Invoice`, `InvoiceItem`, `Payment`, `SupportTicket`, `SupportTicketReply`, `AuditLog`, `SystemSetting`, `ApiKey`
- **Tenant** (`connection = 'tenant'`): `SchoolClass`, `Subject` (+ `classes()` belongsToMany), `Teacher`, `Student`, `Exam` (+ `classes()` belongsToMany), `Result`, `Attendance`, `FeeCategory`, `Fee`, `FeePayment`

## Multi-select class dropdown

- Only on 3 forms: `subjects/_form`, `exams/_form`, `fees/fees/_form`
- Uses `partials/_class_select.blade.php` checkbox-dropdown widget (not `<select multiple>`)
- Native checkboxes with name `class_id[]`; uses `data-ms-action`, `data-ms-toggle`, `data-ms-wrap` attributes
- All other class dropdowns are plain `<select name="class_id">` (single)
- Filter/index pages have **no "Class" label** (label removed)
- Form multi-select pages show **"Class (Multi-Select)"** label

## Subject/Exam pivot tables

- `subject_class`: `id, subject_id, class_id, timestamps, unique(subject_id, class_id)`
- `exam_class`: `id, exam_id, class_id, timestamps, unique(exam_id, class_id)`
- Controllers sync pivot via `$model->classes()->sync(...)`; back-compat `class_id` column set to first selected value
- BelongsToMany must specify **explicit foreign keys**: e.g. `belongsToMany(SchoolClass::class, 'subject_class', 'subject_id', 'class_id')` — without this Laravel defaults to `school_class_id`

## Exam creation

When an exam is created, `ExamController::store` syncs classes AND pre-creates blank `Result` rows for every student in each selected class.

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

# Run tenant migrations
php artisan migrate --database=tenant

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
- **DB provisioning** is fully manual on Hostinger: pre-create the MySQL database in cPanel (e.g. `PREFIX_skoolms_lba`) and create the subdomain (e.g. `lba.msitsols.com`) before adding a tenant record. `CreateTenantCommand` requires `--db-name` and does NOT attempt `CREATE DATABASE`.
- **School self-registration** disabled on production (`/register-school` moved under `super_admin` middleware). Only superadmins can create tenant records.
- **Tenant migration workflow**: after creating tenant record, run `php artisan tenant:migrate --subdomain={subdomain}` via SSH to run tenant migrations on the pre-created DB.
