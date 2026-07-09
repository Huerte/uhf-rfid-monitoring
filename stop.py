import json
import os
import subprocess

PID_FILE = "running_pids.json"

if not os.path.exists(PID_FILE):
    print("No running_pids.json found. Nothing to stop.")
    exit()

with open(PID_FILE, "r") as f:
    pids = json.load(f)

for pid in pids:
    try:
        subprocess.run(["taskkill", "/PID", str(pid), "/F", "/T"], check=True)
        print(f"Stopped PID {pid}")
    except subprocess.CalledProcessError:
        print(f"PID {pid} not found or already stopped")

os.remove(PID_FILE)
print("Cleared PID file.")