"""Check the starter's working paths, without solving or grading the two TODOs."""

import html
import sys
import time
import uuid
from urllib.error import URLError
from urllib.parse import urlencode
from urllib.request import Request, urlopen


base = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8080").rstrip("/")


def request(path="/", data=None):
    body = urlencode(data).encode() if data is not None else None
    with urlopen(Request(base + path, data=body), timeout=10) as response:
        assert response.status == 200, response.status
        return response.read().decode("utf-8")


for attempt in range(60):
    try:
        page = request()
        if "Network Security" in page:
            break
    except (URLError, TimeoutError):
        pass
    time.sleep(1)
else:
    raise SystemExit("Web/DB did not become ready within 60 attempts")

for field in ["title", "author", "category", "price", "isbn", "publish_year", "stock"]:
    assert f'name="{field}"' in page, f"Missing form field: {field}"

title = "Smoke 'หนังสือ " + uuid.uuid4().hex[:10]
payload = {
    "title": title,
    "author": "Test Author",
    "category": "Database",
    "price": "100.00",
    "isbn": "9780131873254",
    "publish_year": "2022",
    "stock": "0",
}
result = request(data=payload)
assert 'data-testid="success"' in result, "Valid book with stock=0 was rejected"
assert title in html.unescape(request()), "Inserted book was not read back from DB"

payload["title"] = "Error test " + uuid.uuid4().hex[:10]
result = request("/?database_error_test=1", payload)
assert 'data-testid="database-error"' in result, "DB error test did not reach catch"
assert 'data-testid="success"' not in result, "DB error incorrectly reported success"
assert payload["title"] not in html.unescape(request()), "Failed INSERT persisted data"

print("PASS: form, DB initialization, valid INSERT (including stock=0), DB read, and DB error path")
