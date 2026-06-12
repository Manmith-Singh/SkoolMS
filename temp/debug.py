import subprocess
# Just hit /students/import and dump the response
code = subprocess.run(
    ["curl.exe", "-s", "-b", "temp/c.txt", "-c", "temp/c.txt", "-D", "tmp_h.txt",
     "-o", "tmp_b.bin",
     "http://greenfield.school.test:8000/students/import"],
    capture_output=True,
)
print("=== HEADERS ===")
print(open("tmp_h.txt").read())
print("=== BODY (first 2000 chars) ===")
print(open("tmp_b.bin", "rb").read()[:2000].decode("utf-8", errors="ignore"))
