"""Upload a freshly-generated xlsx with unique data, prove 3 new students get created."""
import re, subprocess, time, sys

BASE = "http://greenfield.school.test:8000"

# Use phpspreadsheet to generate a fresh xlsx via Laravel's autoloader
ts = str(int(time.time()))[-6:]

generate_php = f'''
require __DIR__ . '/vendor/autoload.php';
$ss = new \\PhpOffice\\PhpSpreadsheet\\Spreadsheet();
$ss->getActiveSheet()->fromArray([
    ['admission_no', 'first_name', 'last_name', 'roll_no', 'dob', 'gender',
     'email', 'phone', 'guardian_name', 'guardian_phone', 'address',
     'admission_date', 'class_name'],
    ['NEW{ts}1', 'Fresh', 'One',   '1', '2015-01-01', 'male',
     'f1-{ts}@x.test', '1111', 'G1', '1112', '1 Test St', '2026-04-01', ''],
    ['NEW{ts}2', 'Fresh', 'Two',   '2', '2015-02-02', 'female',
     'f2-{ts}@x.test', '2222', 'G2', '2222', '2 Test St', '2026-04-01', ''],
    ['NEW{ts}3', 'Fresh', 'Three', '3', '2015-03-03', 'male',
     'f3-{ts}@x.test', '3333', 'G3', '3333', '3 Test St', '2026-04-01', ''],
]);
(new \\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx($ss))->save('temp/fresh_students.xlsx');
echo "ok";
'''

with open("temp/gen.php", "w") as f:
    f.write(generate_php)

# Run the generator
result = subprocess.run(["php", "temp/gen.php"], capture_output=True, text=True)
print("generate:", result.stdout.strip(), result.stderr.strip()[:200])

# Login
import os
if os.path.exists("temp/c.txt"):
    os.remove("temp/c.txt")
tok = subprocess.check_output(
    ["curl.exe", "-s", "-c", "temp/c.txt", f"{BASE}/login"], text=True
)
m = re.search(r'name="_token"\s+value="([^"]+)"', tok)
subprocess.run([
    "curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", "-o", "/dev/null",
    "-X", "POST",
    "-d", f"_token={m.group(1)}&email=admin@greenfield.school.test&password=greenfield123",
    f"{BASE}/login",
])

# Get fresh CSRF
body = subprocess.check_output(
    ["curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", f"{BASE}/students/import"], text=True
)
m = re.search(r'name="_token"\s+value="([^"]+)"', body)
csrf = m.group(1)

# Upload the fresh xlsx
subprocess.run([
    "curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", "-o", "/dev/null",
    "-X", "POST",
    "-F", f"_token={csrf}",
    "-F", "file=@temp/fresh_students.xlsx;type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    f"{BASE}/students/import",
])

# Read the result
body = subprocess.check_output(
    ["curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", f"{BASE}/students/import"], text=True
)
m = re.search(r'Imported:</strong>\s*(\d+)\s*created,\s*(\d+)\s*skipped', body)
if m:
    print(f"created={m.group(1)} skipped={m.group(2)}")
else:
    print("no summary found")
    # dump the error block
    m2 = re.search(r'<ul class="mb-0 small">(.*?)</ul>', body, re.DOTALL)
    if m2:
        print("Errors:", m2.group(1)[:1000])
