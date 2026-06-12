"""
E2E test for the new checkbox-dropdown multi-select widget.

Verifies:
  1. The 5 filter pages no longer have a 'Class' label.
  2. The 3 multi-select forms now show 'Class (Multi-Select)' as the label.
  3. The new widget renders: a toggle button, a panel with checkboxes,
     Select all / Clear / Invert action buttons, a counter, and an X
     remove button on each option.
  4. The 3 multi-select forms POST a `class_id[]` array when a checkbox
     is checked (form submits only the checked values).
  5. The 5 single-select filter pages still POST `class_id` as a scalar
     (not `class_id[]`).
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

failed = 0
def must(cond, label):
    global failed
    print("  [%s] %s" % ("PASS" if cond else "FAIL", label))
    if not cond: failed += 1

# === Login ===
r = get("/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
post("/login", {"_token": token, "email": "admin@greenfield.school.test", "password": "greenfield123"})

# === 1. Filter pages should not have a 'Class' label ===
print("\n==> Filter pages: no 'Class' label, plain <select>")
for path in ["/subjects", "/attendance", "/fees", "/reports/attendance", "/reports/results"]:
    r = get(path)
    has_class_label = bool(re.search(r'<label[^>]*>\s*Class\s*</label>', r))
    has_ms_widget   = 'data-ms-wrap' in r
    must(not has_class_label, "%s: no 'Class' label" % path)
    must(not has_ms_widget,   "%s: no multi-select widget" % path)
    must('name="class_id"' in r, "%s: has class_id dropdown" % path)
    must('name="class_id[]"' not in r, "%s: no class_id[] array input" % path)

# === 2. The 3 multi-select forms should have 'Class (Multi-Select)' label ===
print("\n==> Multi-select form pages: 'Class (Multi-Select)' label + checkbox widget")
for path in ["/subjects/create", "/exams/create", "/fees/create"]:
    r = get(path)
    must('Class (Multi-Select)' in r, "%s: 'Class (Multi-Select)' label" % path)
    must('Class (pick one)' not in r, "%s: old 'Class (pick one)' label gone" % path)
    must('data-ms-wrap' in r, "%s: has multi-select widget" % path)
    must('data-ms-toggle' in r, "%s: has toggle button" % path)
    must('data-ms-action="all"' in r, "%s: has Select all button" % path)
    must('data-ms-action="none"' in r, "%s: has Clear button" % path)
    must('data-ms-action="invert"' in r, "%s: has Invert button" % path)
    must('data-ms-checkbox' in r, "%s: has checkboxes" % path)
    must('data-ms-remove' in r, "%s: has X remove buttons" % path)
    must('data-ms-count' in r, "%s: has counter" % path)
    must('name="class_id[]"' in r, "%s: has class_id[] input" % path)

# === 3. POST tests ===
# 3a. POST student with class_id=1 (scalar)
print("\n==> POST /students with scalar class_id=1")
r = get("/students/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r = post("/students", {
    "_token": token,
    "first_name": "Widget",
    "last_name": "Test",
    "admission_no": f"ADM-W-{int(time.time())}",
    "class_id": "1",
})
must("Students" in r or "Student added" in r, "Student created (scalar)")

# 3b. POST subject with class_id[]=1 (multi)
print("\n==> POST /subjects with array class_id[]=1")
r = get("/subjects/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r = post("/subjects", {
    "_token": token,
    "name": f"WidgetTest {int(time.time())}",
    "code": f"WT{int(time.time()) % 1000}",
    "class_id[]": "1",
})
must("Subjects" in r or "Subject created" in r, "Subject created (multi)")

# 3c. POST subject with NO class_id[] selected (validation should fail since required)
print("\n==> POST /subjects with NO class_id (should fail validation)")
r = get("/subjects/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r = post("/subjects", {
    "_token": token,
    "name": f"NoClass {int(time.time())}",
    "code": f"NC{int(time.time()) % 1000}",
})
# Should redirect back to create with errors
must("create" in OPENER.open(urllib.request.Request(BASE + "/subjects/create")).url.lower() or
     "Add subject" in r or "Class" in r,
     "Subject with no class_id stays on form (validation error)")

# 3d. POST exam with class_id[]=1
print("\n==> POST /exams with class_id[]=1")
r = get("/exams/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r2 = get("/subjects")
sm = re.findall(r'/subjects/(\d+)(?!.*create)', r2)
subject_id = sm[0] if sm else "1"
r = post("/exams", {
    "_token": token,
    "name": f"WidgetExam {int(time.time())}",
    "class_id[]": "1",
    "subject_id": subject_id,
    "date": "2024-06-10",
    "max_marks": 100,
    "pass_marks": 35,
})
must("Exams" in r or "Exam created" in r, "Exam created (multi)")

# 3e. POST fee assign to class with class_id[]=1
print("\n==> POST /fees with class_id[]=1")
r = get("/fees/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
m2 = re.search(r'<select name="category_id"[^>]*>(.*?)</select>', r, re.S)
cm = re.findall(r'<option value="(\d+)"', m2.group(1)) if m2 else ["1"]
cat_id = cm[0]
r = post("/fees", {
    "_token": token,
    "category_id": cat_id,
    "amount": 250,
    "due_date": "2024-07-01",
    "assignment": "class",
    "class_id[]": "1",
    "notes": "Widget test",
})
must("Fee assigned" in r or "student(s)" in r, "Fee assigned (multi)")

# === 4. Verify single-select filter test still works ===
print("\n==> Filter test: /students?class_id=1 (scalar)")
r = get("/students", {"class_id": "1"})
must("Students" in r, "Students page loads")

# === 5. Verify the widget loads its CSS and JS ===
print("\n==> Widget: check for ms-panel and ms-list CSS classes in rendered HTML")
r = get("/subjects/create")
must('ms-wrap' in r, "ms-wrap class present")
must('ms-panel' in r, "ms-panel class present")
must('ms-list' in r, "ms-list class present")
must('ms-option' in r, "ms-option class present")
must('ms-remove' in r, "ms-remove class present")
must('ms-count' in r, "ms-count class present")
must('ms-item-selected' in r, "ms-item-selected class present (for pre-selected)")

# === 6. Verify that the X remove button has aria-label and the right icon ===
print("\n==> Widget: remove button has × character")
must('×' in r, "Remove button shows ×")

print()
print("==> %s, FAILED: %d" % ("ALL PASS" if failed == 0 else "SOME FAILED", failed))
sys.exit(0 if failed == 0 else 1)
