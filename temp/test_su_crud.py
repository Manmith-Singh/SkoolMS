"""Test CRUD operations on the new SuperAdmin modules."""
import re, subprocess, os

BASE = "http://school.test:8000"
COOKIE = "temp/su.txt"

if os.path.exists(COOKIE):
    os.remove(COOKIE)

def get(path, **kwargs):
    return subprocess.run([
        "curl.exe", "-s", "-b", COOKIE, "-c", COOKIE,
        "-o", "/dev/null", "-w", "%{http_code}",
        f"{BASE}{path}",
    ], capture_output=True, text=True, **kwargs).stdout.strip()

def get_body(path, **kwargs):
    return subprocess.check_output([
        "curl.exe", "-s", "-b", COOKIE, "-c", COOKIE, f"{BASE}{path}"
    ], text=True, **kwargs)

def get_csrf(path):
    body = get_body(path)
    m = re.search(r'name="_token"\s+value="([^"]+)"', body)
    return m.group(1) if m else None

def post(path, fields, files=None, follow=False):
    args = ["curl.exe", "-s", "-b", COOKIE, "-c", COOKIE, "-o", "/dev/null",
            "-w", "%{http_code}", "-X", "POST", f"{BASE}{path}"]
    if follow:
        args.insert(3, "-L")
    for k, v in (fields or {}).items():
        args.extend(["-d", f"{k}={v}"])
    for k, v in (files or {}).items():
        args.extend(["-F", v])
    return subprocess.run(args, capture_output=True, text=True).stdout.strip()

# 1. Login
csrf = get_csrf("/login")
code = post("/login", {
    "_token": csrf,
    "email": "superadmin@school.test",
    "password": "superadmin123",
})
print(f"[login] HTTP {code}")

# 2. Create a subscription plan
csrf = get_csrf("/admin/plans/create")
code = post("/admin/plans", {
    "_token": csrf,
    "name": "Pro Test Plan",
    "slug": "pro-test",
    "price": 49.99,
    "currency": "USD",
    "billing_period": "monthly",
    "max_students": 500,
    "max_teachers": 50,
    "max_storage_mb": 5000,
    "features": "Online exams\nBulk import\nEmail reports",
    "is_active": "1",
    "sort_order": 10,
})
print(f"[plan create] HTTP {code}")

# Verify it shows up
code = get("/admin/plans")
print(f"[plan list]   HTTP {code}")
body = get_body("/admin/plans")
print(f"  contains 'Pro Test Plan': {('Pro Test Plan' in body)}")

# 3. Create a user
csrf = get_csrf("/admin/users/create")
code = post("/admin/users", {
    "_token": csrf,
    "name": "Test User",
    "email": "testuser@schoolms.test",
    "password": "testuser123",
    "password_confirmation": "testuser123",
    "role": "admin",
    "tenant_id": "",
})
print(f"[user create] HTTP {code}")

# Verify
body = get_body("/admin/users")
print(f"  contains 'testuser@schoolms.test': {('testuser@schoolms.test' in body)}")

# 4. Create a support ticket
csrf = get_csrf("/admin/tickets/create")
code = post("/admin/tickets", {
    "_token": csrf,
    "subject": "Schools cannot reset passwords",
    "message": "Multiple tenants reported that the password reset email is not being delivered.",
    "priority": "high",
    "category": "bug",
    "tenant_id": "",
})
print(f"[ticket create] HTTP {code}")

# Verify
body = get_body("/admin/tickets")
print(f"  contains 'passwords': {('passwords' in body)}")

# 5. Create an invoice (need a tenant)
csrf = get_csrf("/admin/invoices/create")
code = post("/admin/invoices", {
    "_token": csrf,
    "tenant_id": "1",
    "currency": "USD",
    "issue_date": "2026-06-01",
    "due_date": "2026-06-15",
    "items[0][description]": "Pro Plan — June 2026",
    "items[0][quantity]": "1",
    "items[0][unit_price]": "49.99",
    "notes": "Test invoice",
})
print(f"[invoice create] HTTP {code}")

# 6. Record a payment
csrf = get_csrf("/admin/payments/create")
code = post("/admin/payments", {
    "_token": csrf,
    "tenant_id": "1",
    "amount": "49.99",
    "currency": "USD",
    "method": "bank_transfer",
    "reference": "TXN-12345",
    "status": "succeeded",
    "paid_at": "2026-06-05 10:00",
    "notes": "Test payment",
})
print(f"[payment create] HTTP {code}")

# 7. Update system settings
csrf = get_csrf("/admin/settings")
code = subprocess.run([
    "curl.exe", "-s", "-b", COOKIE, "-c", COOKIE, "-o", "/dev/null",
    "-w", "%{http_code}", "-X", "PUT",
    "-d", f"_token={csrf}&settings[app_name]=SchoolMS+Test&settings[default_currency]=EUR",
    f"{BASE}/admin/settings",
], capture_output=True, text=True).stdout.strip()
print(f"[settings update] HTTP {code}")

# 8. Generate API key
csrf = get_csrf("/admin/api-keys/create")
code = post("/admin/api-keys", {
    "_token": csrf,
    "name": "Test Integration",
    "scopes": "tenants:read, reports:read",
    "ttl_days": "30",
})
print(f"[api-key create] HTTP {code}")

# 9. Reports page renders chart
body = get_body("/admin/reports")
print(f"[reports page] contains 'revChart': {('revChart' in body)}")
print(f"[reports page] contains 'Top tenants': {('Top tenants' in body)}")

# 10. Audit log captures all this
body = get_body("/admin/audit")
print(f"[audit page] contains 'plan.created': {('plan.created' in body)}")
print(f"[audit page] contains 'ticket.created': {('ticket.created' in body)}")
print(f"[audit page] contains 'payment.recorded': {('payment.recorded' in body)}")
print(f"[audit page] contains 'settings.updated': {('settings.updated' in body)}")
