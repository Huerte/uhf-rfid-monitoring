import json
import os
import subprocess

PID_FILE = "running_pids.json"
stopped_any = False

print("Stopping RFID monitoring system")

if os.path.exists(PID_FILE):
    try:
        with open(PID_FILE, "r") as f:
            pids = json.load(f)
        
        for pid in pids:
            try:
                subprocess.run(["taskkill", "/PID", str(pid), "/F", "/T"], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
                print(f"Stopped PID {pid}")
                stopped_any = True
            except subprocess.CalledProcessError:
                pass
                
        os.remove(PID_FILE)
    except Exception as e:
        print(f"Binoang di ma-process {PID_FILE}: {e}")

try:
    ps_command = """
    Get-CimInstance Win32_Process | 
    Where-Object { $_.CommandLine -ne $null } | 
    Where-Object { 
        $cmd = $_.CommandLine; 
        ($cmd -match 'npm run dev') -or 
        ($cmd -match 'artisan reverb:start') -or 
        ($cmd -match 'artisan serve') -or 
        ($cmd -match 'read-epc.js') 
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
            print(f"Stopped orphan PID {pid}")
            stopped_any = True
        except subprocess.CalledProcessError:
            pass

except Exception as e:
    print(f"Fallback cleanup error: {e}")

if not stopped_any:
    print("Aguy way nakit-an")
else:
    print("Goods")