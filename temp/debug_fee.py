import re, sys, time, http.cookiejar, urllib.request, urllib.error
from urllib.parse import urlencode

BASE = "http://greenfield.school.test:8000"
CJAR = http.cookiejar.CookieJar()
OPENER = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CJAR))

r = OPENER.open(urllib.request.Request(BASE + "/login")).read().decode()
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
body = urlencode({"_token": token, "email": "admin@greenfield.school.test", "password": "greenfield123"}).encode()
OPENER.open(urllib.request.Request(BASE + "/login", data=body, headers={"Content-Type": "application/x-www-form-urlencoded"}))

r = OPENER.open(urllib.request.Request(BASE + "/fees/create")).read().decode()
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
r2 = OPENER.open(urllib.request.Request(BASE + "/fees/categories")).read().decode()
opt = re.search(r'<option value="(\d+)"[^>]*data-amount', r2) or re.search(r'<option value="(\d+)"', r2)
cat_id = opt.group(1) if opt else "1"
print("cat_id:", cat_id)

data = {"_token": token, "category_id": cat_id, "amount": 250, "due_date": "2024-07-01",
        "assignment": "class", "class_id[]": "1", "notes": "test"}
body = urlencode(data, doseq=True).encode()
try:
    resp = OPENER.open(urllib.request.Request(BASE + "/fees", data=body, headers={"Content-Type": "application/x-www-form-urlencoded"}))
    r = resp.read().decode()
    print("URL:", resp.url)
    print("Status:", resp.status)
    print("Length:", len(r))
    m = re.search(r'<div[^>]*alert[^>]*>(.*?)</div>', r, re.S)
    if m:
        print("Flash:", m.group(1)[:300])
    else:
        print("No alert div")
    print("Has 'Fee assigned':", "Fee assigned" in r)
    print("Has 'student(s)':", "student(s)" in r)
    print("--- head ---")
    print(r[:1500])
except urllib.error.HTTPError as e:
    print("HTTPError:", e.code)
    print(e.read().decode()[:1500])
