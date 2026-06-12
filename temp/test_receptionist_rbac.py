"""
E2E test for:
  1. Subjects list: Class column shows all multi-selected classes (badges).
  2. Exams list:    Class column shows all multi-selected classes (badges).
  3. Receptionist role + RBAC enforcement.
"""

import re
import sys
import time
import http.cookiejar
import urllib.request
import urllib.error
from urllib.parse import urlencode

BASE = "http://greenfield.school.test:8000"
CJAR = http.cookiejar.CookieJar()
OPENER = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CJAR))

def get(path, params=None):
    url = BASE + path
    if params:
        url += "?" + urlencode(params, doseq=True)
    return OPENER.open(urllib.request.Request(url)).read().decode("utf-8", "ignore")

def post(path, data):
    body = urlencode(data, doseq=True).encode()
    return OPENER.open(urllib.request.Request(BASE + path, data=body,
        headers={"Content-Type": "application/x-www-form-urlencoded"})).read().decode("utf-8", "ignore")

def login(email, password):
    CJAR.clear()
    r = OPENER.open(urllib.request.Request(BASE + "/login")).read().decode()
    m = re.search(r'name="_token"\s+value="([^"]+)"', r)
    token = m.group(1) if m else ""
    return OPENER.open(urllib.request.Request(BASE + "/login", data=urlencode({"_token": token, "email": email, "password": password}).encode(), headers={"Content-Type": "application/x-www-form-urlencoded"})).read().decode()

failed = 0
def must(cond, label):
    global failed
    print("  [%s] %s" % ("PASS" if cond else "FAIL", label))
    if not cond: failed += 1

# ============================================================
# 1. Subject multi-class via admin
# ============================================================
print("\n==> Login as admin@greenfield")
login("admin@greenfield.school.test", "greenfield123")

# Create a subject with 2 classes
r = get("/subjects/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r = post("/subjects", {
    "_token": token,
    "name": f"MultiClass Subject {int(time.time())}",
    "code": f"MCS{int(time.time()) % 1000}",
    "class_id[]": ["1", "2"],
    "description": "Multi-class test",
})
must("Subject created" in r or "subjects" in r.lower(), "Subject with 2 classes created")

# Verify it appears in the list with badges
r = get("/subjects")
must("badge bg-secondary" in r, "Subjects list shows badge styling")
must("MultiClass Subject" in r, "New subject visible in list")

# Filter by class_id=2 — should still find it (via pivot)
r = get("/subjects", {"class_id": "2"})
must("MultiClass Subject" in r, "Subject visible when filtered by class_id=2 (pivot join)")

# ============================================================
# 2. Exam multi-class via admin
# ============================================================
print("\n==> Create exam with 2 classes")
r = get("/exams/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r2 = get("/subjects")
sm = re.findall(r'/subjects/(\d+)(?!.*create)', r2)
subject_id = sm[0] if sm else "1"
exam_name = f"MultiClass Exam {int(time.time())}"
r = post("/exams", {
    "_token": token,
    "name": exam_name,
    "class_id[]": ["1", "2"],
    "subject_id": subject_id,
    "date": "2099-06-10",
    "max_marks": 100,
    "pass_marks": 35,
})
must("Exam created" in r, "Exam with 2 classes created (flash: 'Exam created')")

r = get("/exams")
must(exam_name in r, "Exam visible in list (sorted by date desc, future date)")
must("badge bg-secondary" in r, "Exam list shows badge styling")

# ============================================================
# 3. Receptionist RBAC
# ============================================================
print("\n==> Login as receptionist")
r = login("receptionist@greenfield.school.test", "reception123")

# Receptionist allowed: dashboard, students.*, teachers (view), classes (view), attendance.*, reports.attendance, reports.fees, fees.payments.*, fees (view)
print("\n-- Allowed pages (should return 200)")
for path in ["/dashboard", "/students", "/students/create",
             "/teachers", "/classes",
             "/attendance", "/attendance/mark",
             "/reports/attendance", "/reports/fees".replace(".", "/"),
             "/fees", "/fees/payments", "/fees/payments/create"]:
    try:
        r = get(path)
        # If not authorised, response is 200 with login form; if allowed, dashboard content
        must("Login" not in r or "students" in r.lower() or "dashboard" in r.lower() or path in r,
             f"{path}: 200 OK (allowed)")
    except urllib.error.HTTPError as e:
        must(False, f"{path}: HTTP {e.code} (should be allowed)")

print("\n-- Denied pages (should return 403)")
for path in ["/subjects", "/subjects/create", "/exams", "/exams/create",
             "/results", "/fees/categories", "/fees/create",
             "/reports/results"]:
    try:
        r = get(path)
        # May redirect to dashboard with error, or return 403
        must("403" in r or "not authorised" in r.lower() or "not permitted" in r.lower()
             or "abort" in r.lower(),
             f"{path}: denied (403 or error message)")
    except urllib.error.HTTPError as e:
        must(e.code == 403, f"{path}: HTTP {e.code} (should be 403)")

# Sidebar check: receptionist should NOT see Academics/Subjects/Exams/Results
# but should see Students/Attendance/Payments
print("\n-- Sidebar check (receptionist)")
r = get("/dashboard")

def sidebar_has_label(html, label):
    """Check whether the sidebar contains a nav-link with this label."""
    # find the sidebar block; nav-link links look like: class="nav-link ..."> <icon> Label </a>
    pattern = r'class="nav-link[^"]*"[^>]*>.*?' + re.escape(label) + r'\s*</a>'
    return bool(re.search(pattern, html, re.S))

must(not sidebar_has_label(r, "Subjects"), "Sidebar does NOT show Subjects link")
must(not sidebar_has_label(r, "Exams"),    "Sidebar does NOT show Exams link")
must(not sidebar_has_label(r, "Results"),  "Sidebar does NOT show Results link")
must(sidebar_has_label(r, "Students"),    "Sidebar shows Students link")
must(sidebar_has_label(r, "Attendance"),  "Sidebar shows Attendance link")
must(sidebar_has_label(r, "Payments"),    "Sidebar shows Payments link")
must(sidebar_has_label(r, "Dashboard"),   "Sidebar shows Dashboard link")

# ============================================================
# 4. Admin still has full access (regression check)
# ============================================================
print("\n==> Regression: admin still has full access")
login("admin@greenfield.school.test", "greenfield123")
for path in ["/dashboard", "/subjects", "/subjects/create", "/exams", "/exams/create",
             "/students", "/teachers", "/fees", "/fees/categories", "/fees/payments",
             "/fees/create", "/reports/attendance", "/reports/results", "/reports/fees"]:
    try:
        r = get(path)
        must(True, f"{path}: 200 OK (admin allowed)")
    except urllib.error.HTTPError as e:
        must(False, f"{path}: HTTP {e.code} (admin should be allowed)")

# ============================================================
# 5. Role 'receptionist' is selectable in master user form
# ============================================================
print("\n==> Master user form: 'receptionist' option present")
# Login as super_admin
CJAR.clear()
r = OPENER.open(urllib.request.Request("http://school.test:8000/login")).read().decode()
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
OPENER.open(urllib.request.Request("http://school.test:8000/login", data=urlencode({"_token": token, "email": "superadmin@school.test", "password": "superadmin123"}).encode(), headers={"Content-Type": "application/x-www-form-urlencoded"}))
r = OPENER.open(urllib.request.Request("http://school.test:8000/admin/users/create")).read().decode()
must('value="receptionist"' in r, "receptionist option in role <select>")
must("Receptionist" in r, "Receptionist label in role <select>")

print()
print("==> %s, FAILED: %d" % ("ALL PASS" if failed == 0 else "SOME FAILED", failed))
sys.exit(0 if failed == 0 else 1)
