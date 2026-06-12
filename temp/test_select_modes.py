"""
E2E test: multi-select class dropdown is ONLY on:
  - /subjects/create  (Add subject)
  - /exams/create     (Add exam)
  - /fees/create      (Whole class option in Assign new fee)
All other class dropdowns should be a plain <select> (not multiple).
"""

import re
import sys
import http.cookiejar
import urllib.request
from urllib.parse import urlencode

# Test on the greenfield tenant directly so session cookies stick on redirects.
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

failed = 0
def must(cond, label):
    global failed
    print("  [%s] %s" % ("PASS" if cond else "FAIL", label))
    if not cond: failed += 1

# Login
r = get("/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
# Some tenants use /admin/login or just /login
post("/login", {"_token": token, "email": "admin@greenfield.school.test", "password": "greenfield123"})
# Verify session
r2 = get("/dashboard")
if "Login" in r2 and "students" not in r2.lower():
    # Try master login
    pass

# === Pages that MUST NOT have multi-select class_id ===
print("\n==> Filter pages: should be single <select>")
for path in ["/students", "/subjects", "/attendance", "/fees",
             "/reports/attendance", "/reports/results"]:
    r = get(path)
    # Check: there should be at least one class_id dropdown, but NONE should be multiple
    has_class = 'name="class_id"' in r
    has_multi = 'name="class_id[]"' in r or 'data-cms-action="all"' in r
    must(has_class, "%s has class_id dropdown" % path)
    must(not has_multi, "%s is NOT multi-select" % path)

# === Form pages that MUST be single select ===
print("\n==> Form pages: student create/edit should be single <select>")
for path in ["/students/create"]:
    r = get(path)
    has_class = 'name="class_id"' in r
    has_multi = 'name="class_id[]"' in r or 'data-ms-action="all"' in r
    must(has_class, "%s has class_id dropdown" % path)
    must(not has_multi, "%s is NOT multi-select" % path)

# === Pages that MUST be multi-select ===
print("\n==> Multi-select forms: subjects/create, exams/create, fees/create")
for path in ["/subjects/create", "/exams/create", "/fees/create"]:
    r = get(path)
    has_multi = 'data-ms-action="all"' in r
    has_class_array = 'name="class_id[]"' in r
    must(has_multi, "%s has multi-select widget" % path)
    must(has_class_array, "%s sends class_id[]" % path)

# === Attendance mark: single select ===
print("\n==> Attendance mark: single select")
r = get("/attendance/mark")
has_class = 'name="class_id"' in r
has_multi = 'name="class_id[]"' in r or 'data-ms-action="all"' in r
must(has_class, "/attendance/mark has class_id dropdown")
must(not has_multi, "/attendance/mark is NOT multi-select")

# === POST tests ===
print("\n==> POST /students with class_id=1 (scalar)")
r = get("/students/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
import time
r = post("/students", {
    "_token": token,
    "first_name": "TestSingle",
    "last_name": "Select",
    "admission_no": f"ADM-S-{int(time.time())}",
    "class_id": "1",
})
must("Students" in r or "Student added" in r, "Student created (or redirected)")

# POST subject (multi - class_id[])
print("\n==> POST /subjects with class_id[]=1")
r = get("/subjects/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
r = post("/subjects", {
    "_token": token,
    "name": f"SingleSel {int(time.time())}",
    "code": f"SS{int(time.time()) % 1000}",
    "class_id[]": "1",
})
must("Subjects" in r or "Subject created" in r, "Subject created (multi)")

# POST exam (multi)
print("\n==> POST /exams with class_id[]=1")
r = get("/exams/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
r2 = get("/subjects")
sm = re.findall(r'/subjects/(\d+)(?!.*create)', r2)
subject_id = sm[0] if sm else "1"
r = post("/exams", {
    "_token": token,
    "name": f"SingleSel Exam {int(time.time())}",
    "class_id[]": "1",
    "subject_id": subject_id,
    "date": "2024-06-10",
    "max_marks": 100,
    "pass_marks": 35,
})
must("Exams" in r or "Exam created" in r, "Exam created (multi)")

# POST fee assign to class (multi)
print("\n==> POST /fees with assignment=class, class_id[]=1")
r = get("/fees/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
# Extract the first category_id from the <select name="category_id"> in the create form.
m2 = re.search(r'<select name="category_id"[^>]*>(.*?)</select>', r, re.S)
if m2:
    cm = re.findall(r'<option value="(\d+)"', m2.group(1))
    cat_id = cm[0] if cm else "1"
else:
    cat_id = "1"
r = post("/fees", {
    "_token": token,
    "category_id": cat_id,
    "amount": 250,
    "due_date": "2024-07-01",
    "assignment": "class",
    "class_id[]": "1",
    "notes": "Multi-select fee test",
})
# Index page after redirect; flash should show "Fee assigned to N student(s)"
must("fees" in r.lower(), "Fee index page (lowercase 'fees')")
must("Fee assigned" in r or "student(s)" in r, "Fee assigned (multi)")

# Attendance mark single
print("\n==> GET /attendance/mark?class_id=1 (single)")
r = get("/attendance/mark", {"class_id": "1", "date": "2024-06-06"})
must("Load" in r or "Mark attendance" in r, "Mark page loads")

# Filter test - single class_id=1
print("\n==> GET /students?class_id=1 (single value filter)")
r = get("/students", {"class_id": "1"})
must("Students" in r, "Students page loads")

print()
print("==> %s, FAILED: %d" % ("ALL PASS" if failed == 0 else "SOME FAILED", failed))
sys.exit(0 if failed == 0 else 1)
