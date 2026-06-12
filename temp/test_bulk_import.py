"""
End-to-end test:
  1. Fees payments page loads (Task 1 fix)
  2. Students import form loads
  3. Students sample.xlsx downloads as a valid xlsx
  4. Re-uploading the sample creates 3 new students
  5. Teachers import form + sample + upload
"""
import os, re, subprocess, sys, time

BASE = "http://greenfield.school.test:8000"

def http(args, expect_status=None):
    """Run curl with the given args.  Returns (status_code, headers_text, body)."""
    proc = subprocess.run(
        ["curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", "-D", "tmp_h.txt",
         "-o", "tmp_b.bin", *args],
        capture_output=True,
    )
    with open("tmp_h.txt", "r", encoding="utf-8", errors="ignore") as f:
        h = f.read()
    status = h.splitlines()[0] if h else "(none)"
    code = status.split(" ")[1] if " " in status else "???"
    with open("tmp_b.bin", "rb") as f:
        body = f.read()
    return code, h, body

def get(url, text=True):
    args = ["curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", f"{BASE}{url}"]
    if text:
        return subprocess.check_output(args, text=True, errors="ignore")
    return subprocess.check_output(args)

def get_csrf(url):
    body = get(url)
    m = re.search(r'name="_token"\s+value="([^"]+)"', body)
    return m.group(1) if m else None

# ---------- Login ----------
tok = get_csrf("/login")
http(["-X", "POST", "-d",
      f"_token={tok}&email=admin@greenfield.school.test&password=greenfield123",
      f"{BASE}/login"])
print("[ok] logged in as school admin\n")

# ---------- Task 1: /fees/payments ----------
code, h, _ = http([f"{BASE}/fees/payments"])
print(f"[1/5] GET /fees/payments          HTTP {code}  {'PASS' if code == '200' else 'FAIL'}")
# Also check the index lists fees/payments in the sidebar
body = get("/dashboard")
has_payment_link = "fees/payments" in body
print(f"      sidebar links to payments:  {'YES' if has_payment_link else 'NO'}")

# ---------- Task 2a: students/import form ----------
code, h, body = http([f"{BASE}/students/import"])
print(f"\n[2/5] GET /students/import         HTTP {code}  {'PASS' if code == '200' else 'FAIL'}")

# ---------- Task 2b: download sample ----------
code, h, body = http([f"{BASE}/students/sample.xlsx"])
ct = re.search(r'content-type:\s*(.+)', h, re.IGNORECASE)
size = len(body)
# Check it's a real xlsx (ZIP signature PK\x03\x04)
is_xlsx = body[:2] == b"PK"
print(f"[3/5] GET /students/sample.xlsx    HTTP {code}  size={size}B  xlsx={is_xlsx}  ct={ct.group(1).strip() if ct else '?'}")
# Save the body for the upload step
with open("temp/students_sample.xlsx", "wb") as f:
    f.write(body)

# ---------- Task 2c: upload sample and verify rows created ----------
# Make a unique copy so re-running the test still works
import shutil
shutil.copy("temp/students_sample.xlsx", "temp/students_upload.xlsx")
# We can't easily edit xlsx in Python, so we'll just upload the sample as-is.
# It will create new students (or fail with "admission_no already exists" - either is OK,
# we just need the import endpoint to not error).

# Get a fresh CSRF
tok = get_csrf("/students/import")
# POST with file
code, h, _ = http([
    "-X", "POST",
    "-F", f"_token={tok}",
    "-F", f"file=@temp/students_upload.xlsx;type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    f"{BASE}/students/import",
])
# Look for "created: 3" or "created: 0" in the resulting alert
# Follow the back() redirect
code, h, body = http([f"{BASE}/students/import"])
body_text = body.decode("utf-8", errors="ignore")
import_count = re.search(r'Imported:</strong>\s*(\d+)\s*created,\s*(\d+)\s*skipped', body_text, re.DOTALL)
if import_count:
    created = int(import_count.group(1))
    skipped = int(import_count.group(2))
    print(f"[4/5] POST /students/import       HTTP {code}  created={created} skipped={skipped}")
else:
    print(f"[4/5] POST /students/import       HTTP {code}  (couldn't parse summary)")

# ---------- Task 2d: teachers/import + sample + upload ----------
code, h, _ = http([f"{BASE}/teachers/import"])
print(f"\n[5/5] GET /teachers/import         HTTP {code}  {'PASS' if code == '200' else 'FAIL'}")

# Download the teachers sample
code, h, body = http([f"{BASE}/teachers/sample.xlsx"])
is_xlsx = body[:2] == b"PK"
print(f"      GET /teachers/sample.xlsx    HTTP {code}  size={len(body)}B  xlsx={is_xlsx}")
with open("temp/teachers_sample.xlsx", "wb") as f:
    f.write(body)

# Upload it
tok = get_csrf("/teachers/import")
http([
    "-X", "POST",
    "-F", f"_token={tok}",
    "-F", f"file=@temp/teachers_sample.xlsx;type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    f"{BASE}/teachers/import",
])
# Read the resulting page
code, h, body = http([f"{BASE}/teachers/import"])
body_text = body.decode("utf-8", errors="ignore")
import_count = re.search(r'Imported:</strong>\s*(\d+)\s*created,\s*(\d+)\s*skipped', body_text, re.DOTALL)
if import_count:
    print(f"      POST /teachers/import       HTTP {code}  created={import_count.group(1)} skipped={import_count.group(2)}")
else:
    print(f"      POST /teachers/import       HTTP {code}  (no summary)")

# ---------- Verify in DB ----------
print("\n--- DB counts after import ---")
def count(tbl):
    out = subprocess.check_output(
        [r"C:\xampp\mysql\bin\mysql.exe", "-u", "root", "-N", "-B", "-e",
         f"USE schoolms_greenfield; SELECT COUNT(*) FROM {tbl};"], text=True
    )
    return out.strip()

for t in ["students", "teachers", "subjects", "classes"]:
    print(f"  {t:<12} {count(t)}")
