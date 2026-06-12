# SchoolMS — Multi-tenant SaaS School Management System

A complete **Laravel 12 + MySQL** SaaS application for running multiple schools on a single code base.  Each school gets its own MySQL database, subdomain, admin account, and isolated data — all provisioned automatically when someone signs up.

---

## ✨ Features

| Area | Modules |
|---|---|
| **Multi-tenancy** | Master DB (tenants, users) → per-tenant DB (everything else). Subdomain-based routing with dynamic connection switching. |
| **Auth** | Login, school self-registration, logout, password reset hooks, role-based access (`super_admin` / `admin` / `teacher` / `student`). |
| **SuperAdmin SaaS control plane** | Tenant CRUD with DB provisioning + activate/suspend/delete, User management (SuperAdmins + tenant admins), Subscription plans, Invoices + Payments, System settings (general, email, SMS, branding), API keys with scopes/TTL, Reports (usage, revenue, 12-month chart), Support tickets with threaded replies, Security & roles, Audit log. All routes gated by `super_admin` middleware. |
| **Academics** | Classes, Subjects, Exams, Results (with bulk marks entry + auto-grading), Attendance (per-class daily marking). |
| **People** | Students, Teachers — full CRUD with search, filters, pagination, plus **bulk import** (.xlsx / .xls / .csv via phpoffice). |
| **Finance** | **Fee categories** (tuition, transport, library, exam, lab, sports, admission…). **Assign fees** to one student, a whole class, or every student. **Record payments** (cash, cheque, bank, card, online, other) with auto-generated receipts. **Track pending dues** per student, per category. Printable **fee receipt** with school letterhead. |
| **Reports** | Exam results (with pass/fail counts, averages), attendance (monthly by class with rate), fee collection vs pending (by category and period). |
| **Dashboard** | Tenant-scoped overview with stat cards, recent payments, recent students, 6-month fee collection chart. SuperAdmin dashboard shows MRR, active schools, overdue invoices, recent audit. |
| **UX** | Bootstrap 5 + Font Awesome 6 + DataTables + Chart.js, fully responsive, mobile sidebar (offcanvas), **two sidebars** (tenant vs SuperAdmin selected automatically by role), **multi-select class dropdowns** on every filter and form (Select all / Clear / Invert + live counter), clean colour palette. |

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                       Browser / User                         │
└──────────────┬─────────────────────────────┬─────────────────┘
               │                             │
   school.test │                    greenfield.school.test
               │                             │
               ▼                             ▼
       ┌────────────────┐         ┌────────────────────┐
       │   Master App   │         │   IdentifyTenant    │
       │  (apex domain) │         │   middleware        │
       │                │         │   ↓                 │
       │  - login       │         │   SwitchTenantDB    │
       │  - register    │         │   middleware        │
       │  - dashboard   │         │   ↓                 │
       └───────┬────────┘         │   Tenant App        │
               │                  │  - dashboard        │
               │                  │  - students / etc.  │
               ▼                  └───────┬─────────────┘
       ┌────────────────┐                 │
       │ schoolms_master│                 │
       │   - tenants    │                 ▼
       │   - users      │         ┌────────────────────┐
       └────────────────┘         │ schoolms_greenfield│
                                  │   - students        │
                                  │   - teachers        │
                                  │   - classes         │
                                  │   - subjects        │
                                  │   - exams           │
                                  │   - results         │
                                  │   - attendance      │
                                  │   - fees            │
                                  │   - fee_payments    │
                                  └─────────────────────┘
```

**Connection flow** (per request):

1. Browser hits `{subdomain}.school.test`
2. `IdentifyTenant` middleware parses the subdomain, looks up the `tenants` row (cached for 10 min), and binds the `Tenant` model as `currentTenant`
3. `SwitchTenantDatabase` middleware reconfigures Laravel's `tenant` DB connection (host/port/db/user/password) and reconnects
4. The rest of the request uses the tenant's database transparently — controllers and models just say `Student::query()` and it works

---

## 🚀 Installation

### 1. Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- MySQL **5.7+** / MariaDB **10.3+**
- Composer 2.x
- A web server (Apache / Nginx) or use `php artisan serve` for dev

### 2. Clone & install dependencies

```bash
git clone <repo-url> SchoolMS
cd SchoolMS
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Create the SuperAdmin

After migrations, seed your first SuperAdmin via tinker or SQL:

```php
App\Models\Master\User::create([
    'name'      => 'Super Admin',
    'email'     => 'superadmin@school.test',
    'password'  => 'superadmin123',
    'role'      => 'super_admin',
    'tenant_id' => null,
]);
```

Login at `http://school.test:8000/login` and you'll see the full SuperAdmin sidebar.

### 4. Configure `.env`

Edit the database section of `.env`:

```dotenv
APP_URL=http://school.test
APP_DOMAIN=school.test
TENANT_DOMAIN_BASE=school.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schoolms_master
DB_USERNAME=schoolms_app
DB_PASSWORD=secret

TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_DATABASE=schoolms_tenant_template
TENANT_DB_USERNAME=schoolms_app
TENANT_DB_PASSWORD=secret

# Privileged credentials used by `tenant:create` to issue CREATE DATABASE.
# On shared hosting leave these blank and pre-create the DBs manually.
DB_ROOT_USERNAME=root
DB_ROOT_PASSWORD=root
```

### 4. Create the MySQL user & master database

```sql
CREATE DATABASE schoolms_master DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'schoolms_app'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON schoolms_master.* TO 'schoolms_app'@'localhost';
GRANT ALL PRIVILEGES ON `schoolms\_%`.* TO 'schoolms_app'@'localhost';
GRANT CREATE, DROP ON *.* TO 'schoolms_app'@'localhost';
FLUSH PRIVILEGES;
```

> If your MySQL user can't `CREATE DATABASE` (e.g. on shared hosting), see the **Shared-hosting fallback** section below.

### 5. Run migrations

```bash
# Master database
php artisan migrate --path=database/migrations/master --force
php artisan db:seed --class=Database\\Seeders\\MasterSeeder --force
```

### 6. Provision your first school

#### Option A — Self-registration form (recommended for demos)

1. Add `school.test` to your hosts file pointing at `127.0.0.1` (see step 7)
2. Browse to `http://school.test/register-school`
3. Fill in the form → a new database is created automatically and the admin account is provisioned

#### Option B — CLI

```bash
php artisan tenant:create "Greenfield Academy" greenfield \
    --admin-email="admin@greenfield.school.test" \
    --admin-name="Jane Admin" \
    --admin-password="strongpassword"
```

The command prints a summary table with the new school's URL and credentials.

---

## 🛡️ SuperAdmin SaaS Control Plane

The apex domain (`school.test` itself, no subdomain) hosts a complete SaaS control plane under `/admin/*`. Login as a `super_admin` user to access it. Tenant users who hit `/admin/*` get a 403.

### Sidebar modules (10)

| # | Module | Routes | What it does |
|---|---|---|---|
| 1 | **Dashboard** | `/admin` | MRR, active schools, open tickets, overdue invoices, recent activity |
| 2 | **Tenant Management** | `/admin/tenants` | List / search / filter schools; create with auto DB provisioning; edit details; **suspend** / **activate** / **delete** (drops the database) |
| 3 | **User Management** | `/admin/users` | List all users (super-admins + tenant admins); create / edit / delete; bulk search by role |
| 4 | **Subscription Plans** | `/admin/plans` | CRUD on plans — name, price, currency, billing period, max students/teachers/storage, feature list |
| 5 | **Invoices** | `/admin/invoices` | Create invoices with line items, mark paid, view payments |
| 6 | **Payments** | `/admin/payments` | Record manual payments (cash, bank, cheque, card, online); auto-marks invoice as paid when fully covered |
| 7 | **System Settings** | `/admin/settings` | Key/value editor for `general`, `email`, `sms`, `branding` groups; seeds sensible defaults on first visit |
| 8 | **API Keys** | `/admin/api-keys` | Generate keys with name / scopes / TTL; one-time secret reveal; revoke |
| 9 | **Reports** | `/admin/reports`, `/admin/reports/revenue` | 12-month invoice + revenue chart, top tenants by user count, revenue summary |
| 10 | **Audit Logs** | `/admin/audit` | Searchable history of every SuperAdmin action with IP, user agent, JSON metadata |
| 11 | **Support Tickets** | `/admin/tickets` | Create, view, reply (threaded), assign, change priority/status |
| 12 | **Security** | `/admin/security` | User-role table with inline role changes; permission catalogue |
| 13 | **Logout** | (in sidebar) | POST to `/logout` |

### Master DB tables added (June 2026)

| Table | Purpose |
|---|---|
| `subscription_plans` | Plan catalog (Starter / Pro / Enterprise …) |
| `subscriptions` | Per-tenant plan history |
| `invoices` + `invoice_items` | Billable line-item invoices |
| `payments` | Manual payment records |
| `support_tickets` + `support_ticket_replies` | Threaded help-desk |
| `audit_logs` | Every privileged action recorded |
| `system_settings` | Key/value global config |
| `api_keys` | Programmatic access tokens |
| `tenants.plan_id`, `tenants.subscription_ends_at` | Linked the tenants table to plans |

All migrations are idempotent and live in `database/migrations/master/2024_06_06_*`.

### RBAC

The whole `/admin/*` namespace is gated by the `super_admin` middleware alias
(`app/Http/Middleware/SuperAdminOnly.php`):

- unauthenticated → redirect to `master.login`
- tenant user (role ∈ admin/teacher/student) → **403**
- super_admin user with non-null `tenant_id` → **403** (misconfig guard)
- super_admin user with `tenant_id = null` → access granted

The same user can be a SuperAdmin and a tenant admin in *different* records, but
the same record cannot be both — `tenant_id` is forced to `null` whenever
`role = 'super_admin'` (in `UserController::store/update` and
`SecurityController::updateRole`).

### Audit logging

All controller write actions call `AuditLog::record($action, $entity, $metadata)`
which captures the user, IP, user agent, and entity reference. The audit log
view at `/admin/audit` lets you filter by action substring, user, or tenant.

### Sidebar behaviour

`resources/views/partials/sidebar.blade.php` branches on
`auth()->user()->isSuperAdmin() && $isMaster` (where `$isMaster` is true when
`currentTenant` is null or the URL is `/admin/*`). Super-admins get the full
SaaS sidebar; tenant users keep the original academic/finance sidebar.

---

### 7. Configure subdomain routing

#### Local development — `hosts` file

Add these lines to `C:\Windows\System32\drivers\etc\hosts` (Windows) or `/etc/hosts` (macOS/Linux):

```
127.0.0.1   school.test
127.0.0.1   greenfield.school.test
127.0.0.2   bluefield.school.test
```

(Use different IPs only if you have multiple loops; localhost is fine for one school at a time.)

#### Serve with `php artisan serve`

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Then visit:

- `http://school.test:8000` — master login & registration
- `http://greenfield.school.test:8000` — Greenfield Academy

> Browsers ignore ports in subdomain matching; if you use port 8000, just append `:8000` to every URL.

#### Production — DNS

Add a wildcard `*.school.test` A-record pointing at your server IP:

```
school.test.        A   203.0.113.10
*.school.test.      A   203.0.113.10
```

For Apache, enable `mod_rewrite` and set `ServerAlias *.school.test`.  For Nginx:

```nginx
server {
    listen 80;
    server_name school.test *.school.test;
    root /var/www/SchoolMS/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Apache (XAMPP) — virtual host

The wildcard `ServerAlias` is the key bit — without it Apache returns `404` for `greenfield.school.test` because no vhost matches.

Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName  school.test
    ServerAlias *.school.test
    DocumentRoot "C:/xampp/htdocs/SchoolMS/public"

    <Directory "C:/xampp/htdocs/SchoolMS/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  "logs/schoolms-error.log"
    CustomLog "logs/schoolms-access.log" common
</VirtualHost>
```

Then enable vhosts in `C:\xampp\apache\conf\httpd.conf` by uncommenting (or adding):

```
Include conf/extra/httpd-vhosts.conf
LoadModule rewrite_module modules/mod_rewrite.so
```

Restart Apache, then visit:

- `http://school.test/login` — master login
- `http://greenfield.school.test/dashboard` — Greenfield Academy

### 8. Done!

- **Super-admin** login: `http://school.test/login` → `superadmin@school.test` / `password`
- **Super-admin** dashboard: `http://school.test/admin`
- **School admin** login: `http://greenfield.school.test/login` → use the credentials you set when creating the school
- **School admin** dashboard: `http://greenfield.school.test/dashboard`

---

## 📂 Project structure

```
SchoolMS/
├── app/
│   ├── Console/Commands/
│   │   ├── CreateTenantCommand.php       (php artisan tenant:create)
│   │   └── TenantMigrateCommand.php      (php artisan tenant:migrate)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── Master/                   (apex domain — auth & registration)
│   │   │   └── Tenant/                   (subdomain — CRUD modules)
│   │   └── Middleware/
│   │       ├── IdentifyTenant.php        (parses subdomain → loads tenant)
│   │       ├── SwitchTenantDatabase.php  (DB::purge + reconnect)
│   │       └── RoleMiddleware.php
│   ├── Models/
│   │   ├── Master/                       (Tenant, User)
│   │   └── Tenant/                       (Student, Teacher, …)
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── TenantServiceProvider.php     (maps master + tenant routes)
│   └── Services/
│       └── TenantDatabaseManager.php
├── bootstrap/
│   ├── app.php                           (Laravel 12 app bootstrap)
│   ├── providers.php
│   └── cache/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php
│   ├── database.php                      (mysql = master, tenant = dynamic)
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── session.php
│   └── tenancy.php                       (★ custom tenancy config)
├── database/
│   ├── migrations/
│   │   ├── master/                       (tenants, users, sessions…)
│   │   └── tenant/                       (10 tenant tables)
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── MasterSeeder.php              (super-admin)
│   │   └── TenantSeeder.php              (default fee categories)
│   ├── factories/
│   │   └── UserFactory.php
│   └── sql/
│       ├── master_db.sql                 (★ raw master schema)
│       ├── master_sample_data.sql
│       ├── tenant_template.sql           (★ raw tenant schema)
│       └── sample_data.sql               (★ demo data for any tenant DB)
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── robots.txt
├── resources/views/
│   ├── layouts/{app,auth}.blade.php
│   ├── partials/{sidebar,_class_select}.blade.php
│   ├── dashboard.blade.php
│   ├── master/{login,register,register-success,dashboard}.blade.php
│   ├── students/, teachers/, classes/, subjects/
│   ├── exams/, results/, attendance/
│   ├── fees/{categories,fees,payments}/…
│   └── reports/{index,results,attendance,fees}.blade.php
├── routes/
│   ├── console.php
│   ├── web.php
│   ├── master.php                        (apex domain)
│   └── tenant.php                        (subdomain)
├── storage/                              (logs, sessions, cache)
├── .env.example
├── .gitignore
├── artisan
├── composer.json
└── README.md
```

---

## 🛠️ Artisan commands

| Command | Description |
|---|---|
| `php artisan tenant:create "School Name" subdomain` | Provision a new school (DB + admin). |
| `php artisan tenant:create "School" sub --admin-email=...` | Same, with custom admin credentials. |
| `php artisan tenant:migrate` | Run migrations against every active tenant. |
| `php artisan tenant:migrate --subdomain=greenfield` | Migrate a single tenant. |
| `php artisan tenant:migrate --fresh --seed` | Wipe and re-seed one or all tenants. |
| `php artisan migrate --path=database/migrations/master` | Migrate the master DB. |
| `php artisan db:seed --class=Database\\Seeders\\MasterSeeder` | Seed the super-admin. |

---

## 🗄️ Database schema

### Master DB (`schoolms_master`)

| Table | Purpose |
|---|---|
| `tenants` | One row per registered school: name, subdomain, db_name, db_username, db_password, status, contact info, trial end date. |
| `users` | Global login accounts (super_admin / admin / teacher / student) with `tenant_id` FK. |
| `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations` | Standard Laravel tables. |

### Tenant DB (`schoolms_<subdomain>`)

| Table | Purpose |
|---|---|
| `classes` | Class name + section, capacity, description. |
| `subjects` | Belongs to a class. |
| `students` | admission_no, name, roll, dob, guardian info, class_id FK, optional `user_id` link. |
| `teachers` | employee_id, name, email, subject_id FK, salary, optional `user_id` link. |
| `exams` | name, class_id, subject_id, date, max_marks, pass_marks. |
| `results` | exam_id, student_id, marks_obtained, grade, remarks. UNIQUE(exam, student). |
| `attendance` | student_id, class_id, date, status, remarks. UNIQUE(student, date). |
| `fee_categories` | name, default_amount, frequency, is_active. |
| `fees` | student_id, category_id, amount, paid_amount, due_date, status. |
| `fee_payments` | fee_id, student_id, amount_paid, payment_date, mode, receipt_no UNIQUE, transaction_ref. |

> **Note on `user_id`** — both `students` and `teachers` tables include an optional `user_id BIGINT UNSIGNED` column meant to link a student/teacher record to a master `users` row (for student/teacher portal logins). Because master and tenant tables live in **different databases**, MySQL cannot enforce a cross-database FK, so the column is just a regular indexed integer — your application code is responsible for keeping it consistent.

---

## 💰 Fee module — how it works

1. **Admin** creates **fee categories** (`Fee categories` → `Add category`) — e.g. Tuition, Transport, Library. Each category has a name, frequency (monthly/term/annual/one-time), default amount, and an `is_active` flag. **Edit** is a full-page form (mirrors the Classes/Subjects edit pages).
2. **Admin assigns fees** (`Fee assignments` → `Assign new fee`):
   - Pick a category
   - Enter amount + due date
   - Choose scope: **single student**, **whole class**, or **all students**
   - One click creates the rows in the `fees` table with `status = pending`
3. **Admin records payments** (`Payments` → `Record payment`):
   - Select a pending fee (or click the cash-register icon next to one)
   - Enter amount, date, mode (cash/cheque/bank/card/online)
   - Optional transaction reference / notes
   - On save: a `fee_payments` row is created with an **auto-generated receipt number** (`RCP-YYYYMMDD-####`), the fee's `paid_amount` is incremented, and its `status` is recomputed (`pending` / `partial` / `paid`)
4. **Receipt** can be printed (`/fees/payments/{id}/receipt`) — printable HTML with school letterhead, student info, payment breakdown, signature line.
5. **Reports** (`Reports` → `Fee collection`) shows total collected vs pending in a date range, broken down by category.

---

## 🔐 Role-based access

The `role` column in the `users` master table accepts:

- `super_admin` — manages the SaaS platform itself (master dashboard)
- `admin` — school-level admin with full access to all tenant modules
- `teacher` — read-only on classes, enter marks/attendance
- `student` — view-only on their own data

Enforcement is via the `role` middleware:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // admin-only routes
});
```

The current implementation only enforces `auth` on tenant routes.  To lock down individual modules add `->middleware('role:admin')` to the relevant `Route::resource` lines in `routes/tenant.php`.

---

## 🧪 Sample data

To populate a fresh tenant DB with demo data:

```bash
mysql -u schoolms_app -p schoolms_greenfield < database/sql/sample_data.sql
```

It includes 5 classes, 12 subjects, 12 students, 5 teachers, 5 exams, 12 results, 18 fees, 10 payments, 12 attendance records.

---

## 📥 Bulk import (Excel / CSV)

Schools can add dozens of students or teachers in one go:

- **Students**: `/students/import` — columns: `admission_no, first_name, last_name, roll_no, dob, gender, email, phone, guardian_name, guardian_phone, address, admission_date, class_name`
- **Teachers**: `/teachers/import` — columns: `employee_id, first_name, last_name, email, phone, qualification, hire_date, gender, address, salary, subject_code`
- Accepts `.xlsx`, `.xls`, `.csv` via `phpoffice/phpspreadsheet`
- Per-row validation: invalid rows are skipped and reported in a flash message; valid rows are inserted
- Class/subject lookups are case-insensitive by **name** (students) or **code** (teachers)
- A **Download template** button on the import page generates a pre-filled `.xlsx` with the correct headers

---

## 🗂️ Multi-select class dropdown

Every "Class" picker throughout the tenant app is a **multi-select** widget:

![multi-select]()

- **Filter forms** (`/students`, `/subjects`, `/attendance`, `/fees`, `/reports/attendance`, `/reports/results`) let you pick 1, several, or all classes — the query is built with `whereIn`.
- **Form fields** (`/students/create`, `/subjects/create`, `/exams/create`, `/attendance/mark`, `/fees/create` → "Whole class") accept multiple classes; the controller stores the first selected value (since each row is linked to one class).
- Three buttons appear above the picker: **Select all** · **Clear** · **Invert**.
- A live counter shows `N of M selected`.
- The partial lives at `resources/views/partials/_class_select.blade.php` and is registered once via `@once @push('scripts')` — use it like this:

  ```blade
  @include('partials._class_select', [
      'name'     => 'class_id',
      'classes'  => $classes,
      'selected' => (array) request('class_id', []),
      'size'     => 4,
  ])
  ```

  Or with a required single value:

  ```blade
  @include('partials._class_select', [
      'name'     => 'class_id',
      'classes'  => $classes,
      'selected' => old('class_id') !== null ? (array) old('class_id') : [],
      'required' => true,
      'size'     => 4,
  ])
  ```

  The form input name is always `class_id[]` — Laravel's `request->input('class_id')` returns either a single value or an array, so controllers normalise it with a small helper:

  ```php
  // Filter: keep all values for whereIn
  $classIds = array_values(array_filter((array) $request->input('class_id', [])));
  if (! empty($classIds)) { $query->whereIn('class_id', $classIds); }

  // Form: collapse to first non-empty value
  $raw = array_values(array_filter((array) $request->input('class_id', [])));
  $data['class_id'] = (int) ($raw[0] ?? 0);
  ```

  Validation rules accept arrays:

  ```php
  'class_id'   => ['required', 'array', 'min:1'],
  'class_id.*' => ['required', 'exists:tenant.classes,id'],
  ```

---

## 🆘 Shared-hosting fallback

If your MySQL user can't run `CREATE DATABASE`, pre-create the DBs manually:

```sql
CREATE DATABASE schoolms_greenfield DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON schoolms_greenfield.* TO 'schoolms_app'@'localhost';
```

Then insert the tenant row by hand and run `tenant:migrate` against it:

```bash
php artisan tinker
>>> App\Models\Master\Tenant::create([
...   'name' => 'Greenfield Academy',
...   'subdomain' => 'greenfield',
...   'db_name' => 'schoolms_greenfield',
...   'db_username' => 'schoolms_app',
...   'db_password' => 'secret',
...   'status' => 'active',
... ]);

# Then:
php artisan tenant:migrate --subdomain=greenfield --seed
```

---

## 🧰 Troubleshooting

| Problem | Fix |
|---|---|
| `SQLSTATE[HY000] [1049] Unknown database 'schoolms_master'` | Create the master DB first (`CREATE DATABASE schoolms_master …`). |
| `Access denied for user 'schoolms_app'@'localhost'` | Either match the username/password in `.env`, or grant privileges: `GRANT ALL ON schoolms_master.* TO 'schoolms_app'@'localhost'`. |
| `SQLSTATE[42000]: Syntax error … near 'CREATE DATABASE'` | The MySQL user can't `CREATE DATABASE`.  Pre-create the DB or set `DB_ROOT_USERNAME` to a privileged user. |
| Subdomain not detected | Make sure your `APP_DOMAIN` matches the apex domain, and the subdomain is correctly in DNS or your `hosts` file. |
| 404 on `greenfield.school.test` | Confirm the `tenants` row exists with that subdomain and `status='active'`. Clear the cache: `php artisan cache:clear`. |
| Sessions cross between schools | Sessions are stored in the **master** DB by default (configured in `config/session.php` via `SESSION_DRIVER=database`), which is correct — the same user can be logged into the master and any tenant at the same time. |

---

## 📜 License

MIT — use it, modify it, sell it.  No warranty.

---

## 🤝 Credits

Built with:

- [Laravel 12](https://laravel.com)
- [Bootstrap 5](https://getbootstrap.com)
- [Font Awesome 6](https://fontawesome.com)
- [DataTables](https://datatables.net)
- [Chart.js](https://www.chartjs.org)
