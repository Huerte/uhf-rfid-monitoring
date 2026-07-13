import json
import os
import subprocess

PID_FILE = "running_pids.json"
stopped_any = False

print("=== Stopping RFID Monitoring System ===")

if os.path.exists(PID_FILE):
    try:
        with open(PID_FILE, "r") as f:
            pids = json.load(f)
        
        for pid in pids:
            try:
                subprocess.run(["taskkill", "/PID", str(pid), "/F", "/T"], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
                print(f"[OK] Stopped process tree for PID {pid} (from json)")
                stopped_any = True
            except subprocess.CalledProcessError:
                pass
                
        os.remove(PID_FILE)
        print("[INFO] Cleared running_pids.json")
    except Exception as e:
        print(f"[WARN] Could not process {PID_FILE}: {e}")
else:
    print("[INFO] No running_pids.json found. Proceeding to fallback cleanup...")

print("[INFO] Scanning for any orphan project processes in the background...")

try:
    ps_command = """
    Get-CimInstance Win32_Process | 
    Where-Object { $_.CommandLine -ne $null } | 
    Where-Object { 
        $cmd = $_.CommandLine; 
        ($cmd -match 'npm run dev') -or 
        ($cmd -match 'artisan reverb:start') -or 
        ($cmd -match 'artisan serve') -or 
        ($cmd -match 'ReadEpc.py') 
    } | 
    Select-Object -ExpandProperty ProcessId
    """
    
    output = subprocess.check_output(["powershell", "-NoProfile", "-Command", ps_command], text=True)
    orphan_pids = [line.strip() for line in output.strip().split('\n') if line.strip()]
    
    for pid in orphan_pids:
        if str(pid) == str(os.getpid()):
            continue
            
        try:
            subprocess.run(["taskkill", "/PID", str(pid), "/F", "/T"], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            print(f"[OK] Stopped orphan project process (PID {pid})")
            stopped_any = True
        except subprocess.CalledProcessError:
            pass

except Exception as e:
    print(f"[WARN] Fallback cleanup encountered an issue: {e}")

if not stopped_any:
    print("[INFO] No running instances found. Everything is already closed!")
else:
    print("[SUCCESS] All project instances stopped successfully.")