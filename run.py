import subprocess
import json
from pathlib import Path
import webbrowser

BASE_DIR = Path(__file__).resolve().parent

PID_FILE = BASE_DIR / "running_pids.json"

jobs = [
    {"cwd": BASE_DIR / "backend", "cmd": "npm run dev"},
    {"cwd": BASE_DIR / "backend", "cmd": "php artisan reverb:start"},
    {"cwd": BASE_DIR / "backend", "cmd": "php artisan serve --port=8000"},
    {"cwd": BASE_DIR / "backend" / "rfid-bridge", "cmd": "node read-epc.js"},
]

pids = []

for job in jobs:
    p = subprocess.Popen(
        ["powershell", "-NoExit", "-Command", job["cmd"]],
        cwd=str(job["cwd"]),
        creationflags=subprocess.CREATE_NEW_CONSOLE
    )
    pids.append(p.pid)
    print(f"Started PID {p.pid} in {job['cwd']} -> {job['cmd']}")

import time
time.sleep(7)
webbrowser.open("http://127.0.0.1:8000/")

with open(PID_FILE, "w") as f:
    json.dump(pids, f)

print(f"\nNa save an {len(pids)} PIDs to {PID_FILE}")