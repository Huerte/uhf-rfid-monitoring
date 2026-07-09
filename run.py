import subprocess
import json
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent

PID_FILE = BASE_DIR / "running_pids.json"

jobs = [
    {"cwd": BASE_DIR / "backend", "cmd": "npm run dev"},
    {"cwd": BASE_DIR / "backend", "cmd": "php artisan reverb:start"},
    {"cwd": BASE_DIR / "backend", "cmd": "php artisan serve --port=8000"},
    {"cwd": BASE_DIR / "backend/python-bridge", "cmd": r".\venv\Scripts\activate; uvicorn main:app --port 8001"},
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

with open(PID_FILE, "w") as f:
    json.dump(pids, f)

print(f"\nSaved {len(pids)} PIDs to {PID_FILE}")