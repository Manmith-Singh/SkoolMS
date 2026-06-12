"""Verify the SuperAdmin sidebar + every new module renders (HTTP 200) for super admin."""
import re, subprocess, os, sys

BASE = "http://school.test:8000"
COOKIE = "temp/su.txt"

if os.path.exists(COOKIE):
    os.remove(COOKIE)

# --- 1. GET /login
body = subprocess.check_output(["curl.exe", "-s", "-c", COOKIE, f"{BASE}/login"], text=True)
m = re.search(r'name="_token"\s+value="([^"]+)"', body)
token = m.group(1)

# --- 2. POST /login as super admin
result = subprocess.run([
    "curl.exe", "-s", "-b", COOKIE, "-c", COOKIE, "-o", "/dev/null", "-w", "%{http_code}",
    "-X", "POST",
    "-d", f"_token={token}&email=superadmin@school.test&password=superadmin123",
    f"{BASE}/login",
], capture_output=True, text=True)
print(f"[login] HTTP {result.stdout.strip()}")

# --- 3. GET every page in the sidebar
pages = [
    ("/admin",                          "Dashboard"),
    ("/admin/tenants",                  "Tenants"),
    ("/admin/tenants/create",           "Tenants > Create"),
    ("/admin/users",                    "Users"),
    ("/admin/users/create",             "Users > Create"),
    ("/admin/plans",                    "Plans"),
    ("/admin/plans/create",             "Plans > Create"),
    ("/admin/invoices",                 "Invoices"),
    ("/admin/invoices/create",          "Invoices > Create"),
    ("/admin/payments",                 "Payments"),
    ("/admin/payments/create",          "Payments > Create"),
    ("/admin/settings",                 "Settings"),
    ("/admin/api-keys",                 "API Keys"),
    ("/admin/api-keys/create",          "API Keys > Create"),
    ("/admin/reports",                  "Reports"),
    ("/admin/reports/revenue",          "Reports > Revenue"),
    ("/admin/audit",                    "Audit log"),
    ("/admin/tickets",                  "Tickets"),
    ("/admin/tickets/create",           "Tickets > Create"),
    ("/admin/security",                 "Security"),
]

print(f"\n{'Page':<30} {'Status':<8} Result")
print("-" * 60)
ok = 0
for path, label in pages:
    code = subprocess.run([
        "curl.exe", "-s", "-b", COOKIE, "-o", "/dev/null", "-w", "%{http_code}",
        f"{BASE}{path}",
    ], capture_output=True, text=True).stdout.strip()
    status = "PASS" if code == "200" else "FAIL"
    if code == "200":
        ok += 1
    print(f"{label:<30} {code:<8} {status}")
print(f"\nTotal: {ok}/{len(pages)} pages render with 200")

# --- 4. Test sidebar visible on /admin (look for super_admin-only items)
print("\n=== Sidebar content check ===")
body = subprocess.check_output(["curl.exe", "-s", "-b", COOKIE, f"{BASE}/admin"], text=True)
expected = [
    "Tenant Management",
    "User Management",
    "Subscription Plans",
    "Invoices",
    "Payments",
    "System Settings",
    "API Keys",
    "Reports",
    "Audit Logs",
    "Support Tickets",
    "Security",
    "Logout",
]
for item in expected:
    found = item in body
    print(f"  [{'OK' if found else 'MISS'}] '{item}' in sidebar")
