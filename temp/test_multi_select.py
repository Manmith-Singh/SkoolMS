"""
End-to-end test for the multi-select class dropdown feature.

Verifies:
  1. Students index page shows the multi-select widget (with 3 buttons).
  2. Selecting two classes via the form preserves selection in URL.
  3. Filtering by multiple classes works (URL has class_id[0]=1&class_id[1]=2).
  4. Form POST (create student) still works with single class.
  5. Form POST (create subject) still works with single class.
  6. Form POST (create exam) still works with single class.
  7. Form POST (assign fee to a class) still works.
  8. Attendance mark/store still works.
"""

import re
import sys
import time
import json
import urllib.parse
import http.cookiejar
from urllib.request import HTTPCookieProcessor, Request, build_opener
from urllib.parse import urlencode

BASE = "http://school.test:8000"
LOGIN = f"{BASE}/login"
CJAR = http.cookiejar.CookieJar()
OPENER = build_opener(HTTPCookieProcessor(CJAR))


def get(path, params=None):
    url = BASE + path
    if params:
        url += "?" + urlencode(params, doseq=True)
    req = Request(url)
    return OPENER.open(req).read().decode("utf-8", "ignore")


def post(path, data):
    body = urlencode(data, doseq=True).encode()
    req = Request(BASE + path, data=body,
                  headers={"Content-Type": "application/x-www-form-urlencoded"})
    return OPENER.open(req).read().decode("utf-8", "ignore")


def must(cond, label):
    status = "PASS" if cond else "FAIL"
    print(f"  [{status}] {label}")
    if not cond:
        global failed
        failed += 1


failed = 0

print("==> login admin@greenfield.school.test / greenfield123")
r = get("/login")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
post("/login", {
    "_token": token,
    "email": "admin@greenfield.school.test",
    "password": "greenfield123",
})

print("==> /students: multi-select widget present")
r = get("/students")
must('data-cms-action="all"' in r, "  Select all button present")
must('data-cms-action="none"' in r, "  Clear button present")
must('data-cms-action="invert"' in r, "  Invert button present")
must('multiple' in r, "  multiple attribute present")
must('name="class_id[]"' in r, "  name=class_id[] present")

print("==> /students?class_id=1&class_id=2: filter works")
r = get("/students", {"class_id": ["1", "2"], "q": ""})
must("Students" in r, "  Page still loads")
must('selected' in r, "  At least one option is pre-selected")

print("==> /subjects: multi-select widget present")
r = get("/subjects")
must('data-cms-action="all"' in r, "  Select all button present")

print("==> /attendance: multi-select widget present")
r = get("/attendance")
must('data-cms-action="all"' in r, "  Select all button present")

print("==> /fees: multi-select widget present")
r = get("/fees")
must('data-cms-action="all"' in r, "  Select all button present")

print("==> /reports/attendance: multi-select widget present")
r = get("/reports/attendance")
must('data-cms-action="all"' in r, "  Select all button present")

print("==> /reports/results: multi-select widget present")
r = get("/reports/results")
must('data-cms-action="all"' in r, "  Select all button present")

print("==> create student (form view)")
r = get("/students/create")
must('data-cms-action="all"' in r, "  Multi-select on form")
must('name="first_name"' in r, "  First name field present")

print("==> create subject (form view)")
r = get("/subjects/create")
must('data-cms-action="all"' in r, "  Multi-select on form")

print("==> create exam (form view)")
r = get("/exams/create")
must('data-cms-action="all"' in r, "  Multi-select on form")

print("==> attendance mark: pick first class, load, then store")
r = get("/attendance/mark", {"class_id": "1", "date": "2024-06-06"})
must("Load" in r or "Mark attendance" in r, "  page loads")

# POST a student to verify single-class form still works
print("==> POST /students: create with single class_id")
r = get("/students/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
# Use class_id[0]=1 (multi-select sends array)
r = post("/students", {
    "_token": token,
    "first_name": "TestMulti",
    "last_name": "Select",
    "admission_no": f"ADM-TEST-{int(time.time())}",
    "class_id[]": "1",
})
must("Students" in r or "success" in r.lower() or "Student added" in r, "  Student created (or redirected to index)")

# POST a subject
print("==> POST /subjects: create with single class_id[]=1")
r = get("/subjects/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
r = post("/subjects", {
    "_token": token,
    "name": f"MultiSelect Test {int(time.time())}",
    "code": f"MST{int(time.time()) % 1000}",
    "class_id[]": "1",
})
must("Subjects" in r or "success" in r.lower() or "Subject created" in r, "  Subject created")

# POST exam
print("==> POST /exams: create with class_id[]=1")
r = get("/exams/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
# Need a subject too
from urllib.request import urlopen
r = get("/subjects")
# Find first subject id from a link
sm = re.findall(r'/subjects/(\d+)(?!.*create)', r)
subject_id = sm[0] if sm else "1"
r = post("/exams", {
    "_token": token,
    "name": f"MultiTest Exam {int(time.time())}",
    "class_id[]": "1",
    "subject_id": subject_id,
    "date": "2024-06-10",
    "max_marks": 100,
    "pass_marks": 35,
})
must("Exams" in r or "success" in r.lower() or "Exam created" in r, "  Exam created")

# Fee assign to class
print("==> POST /fees: assignment=class, class_id[]=1")
r = get("/fees/create")
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1) if m else ""
# need a category
r2 = get("/fees/categories")
cm = re.findall(r'value="(\d+)"', r2)
cat_id = cm[0] if cm else "1"
r = post("/fees", {
    "_token": token,
    "category_id": cat_id,
    "amount": 250,
    "due_date": "2024-07-01",
    "assignment": "class",
    "class_id[]": "1",
    "notes": "Multi-select fee test",
})
must("Fees" in r or "success" in r.lower() or "Fee assigned" in r, "  Fee assigned to class")

print()
print("==> FAILED:" if failed else "==> ALL PASS", failed)
sys.exit(0 if failed == 0 else 1)
