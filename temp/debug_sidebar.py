import re, http.cookiejar, urllib.request
from urllib.parse import urlencode
BASE = "http://greenfield.school.test:8000"
CJAR = http.cookiejar.CookieJar()
OPENER = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CJAR))
r = OPENER.open(urllib.request.Request(BASE + "/login")).read().decode()
m = re.search(r'name="_token"\s+value="([^"]+)"', r)
token = m.group(1)
OPENER.open(urllib.request.Request(BASE + "/login", data=urlencode({"_token": token, "email": "receptionist@greenfield.school.test", "password": "reception123"}).encode(), headers={"Content-Type": "application/x-www-form-urlencoded"}))
r = OPENER.open(urllib.request.Request(BASE + "/dashboard")).read().decode()
# extract sidebar
sb_start = r.find('<aside')
sb_end = r.find('</aside>', sb_start)
if sb_start < 0 or sb_end < 0:
    sb_start = r.find('sidebar')
    sb_end = r.find('</div>', sb_start)
print("Sidebar length:", sb_end - sb_start if sb_start >= 0 else "no sidebar")
sidebar = r[sb_start:sb_end + 8] if sb_start >= 0 else r
# find all nav-links
for m in re.finditer(r'<a[^>]*class="nav-link[^"]*"[^>]*>.*?</a>', sidebar, re.S):
    print("LINK:", m.group(0)[:200])
