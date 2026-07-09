import subprocess
import json
import os

PID_FILE = "running_pids.json"

commands = [
    "Get-Process | Sort-Object CPU -Descending | Select-Object -First 5",
    "Get-Service | Where-Object {$_.Status -eq 'Running'}",
    "Get-ChildItem C:\\ -Force",
    "Get-Date",
]

pids = []

for cmd in commands:
    p = subprocess.Popen(
        ["powershell", "-NoExit", "-Command", cmd],
        creationflags=subprocess.CREATE_NEW_CONSOLE
    )
    pids.append(p.pid)
    print(f"Started PID {p.pid} -> {cmd}")

with open(PID_FILE, "w") as f:
    json.dump(pids, f)

print(f"\nSaved {len(pids)} PIDs to {PID_FILE}")