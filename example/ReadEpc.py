from uhf.reader import *
from time import *
from datetime import datetime
import os
import urllib.request
import json

ACTIVE_ANTENNAS = [1, 2, 3, 4]
COOLDOWN_SECONDS = 5

SEEN_TAGS = {}

def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        tag_id = epcInfo.epc
        current_time = time()
        
        if tag_id in SEEN_TAGS:
            if (current_time - SEEN_TAGS[tag_id]) < COOLDOWN_SECONDS:
                return
                
        SEEN_TAGS[tag_id] = current_time

        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        print(f"[!] Scanned: {tag_id} (Antenna: {epcInfo.antId}) at {timestamp}")
        
        file_exists = os.path.isfile("scanned_tags.csv")
        with open("scanned_tags.csv", mode="a", encoding="utf-8") as f:
            if not file_exists:
                f.write("Timestamp,Tag_ID\n")
            f.write(f"{timestamp},{tag_id}\n")

        try:
            payload = json.dumps({
                "protocol": "epc",
                "epc": epcInfo.epc,
                "tid": epcInfo.tid,
                "rssi": epcInfo.rssi,
                "antenna": epcInfo.antId
            }).encode('utf-8')

            req = urllib.request.Request(
                "http://127.0.0.1:8000/api/scans/standalone-ingest", 
                data=payload, 
                headers={
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            )
            urllib.request.urlopen(req, timeout=2.0)
        except urllib.error.HTTPError as e:
            error_body = e.read().decode('utf-8')
            print(f"[!] Failed to send tag {tag_id} to Laravel: HTTP {e.code}")
            print(f"    Reason: {error_body}")
        except Exception as e:
            print(f"[!] Failed to connect to Laravel: {e}")

def receivedEpcOver(epcOver: LogBaseEpcOver):
    print("--- Batch Scan Cycle Ended ---")

if __name__ == '__main__':
    g_client = GClient()
    
    # Connect directly to your network reader
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

        print(f"[*] Starting round-robin scan on Antennas: {ACTIVE_ANTENNAS}")
        print("[*] Press Ctrl+C to stop anytime.")

        try:
            while True:
                for ant in ACTIVE_ANTENNAS:
                    try:
                        # Dynamically grab EnumG.AntennaNo_1, AntennaNo_2, etc.
                        ant_enum_val = getattr(EnumG, f"AntennaNo_{ant}").value
                    except AttributeError:
                        print(f"[!] Invalid Antenna ID configured: {ant}. Skipping.")
                        continue

                    msg = MsgBaseInventoryEpc(antennaEnable=ant_enum_val,
                                              inventoryMode=EnumG.InventoryMode_Inventory.value)
                    
                    if g_client.sendSynMsg(msg) == 0:
                        # Let the hardware scan this specific antenna for 500ms
                        sleep(0.5)
                        
                        # Stop the scan before switching to the next antenna
                        stop = MsgBaseStop()
                        g_client.sendSynMsg(stop)
                    else:
                        print(f"[!] Hardware failed to start Antenna {ant}")
                        sleep(0.5)

        except KeyboardInterrupt:
            print("\n[*] Stopping scanner manually...")

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)

        g_client.close()