from uhf.reader import *
from time import *
from datetime import datetime
import os
import urllib.request
import json

ACTIVE_ANTENNAS = [1, 2, 3, 4]

def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        tag_id = epcInfo.epc

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
    
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

        # OR all selected antenna enum values into one bitmask (same strategy as Demo FFFFFFFF)
        antenna_mask = 0
        for ant in ACTIVE_ANTENNAS:
            try:
                antenna_mask |= getattr(EnumG, f"AntennaNo_{ant}").value
            except AttributeError:
                print(f"[!] Invalid antenna ID: {ant}. Skipping.")

        if antenna_mask == 0:
            antenna_mask = EnumG.AntennaNo_1.value

        msg = MsgBaseInventoryEpc(antennaEnable=antenna_mask,
                                  inventoryMode=EnumG.InventoryMode_Inventory.value)

        if g_client.sendSynMsg(msg) == 0:
            print(f"[*] Scanning on Antennas: {ACTIVE_ANTENNAS} (mask=0x{antenna_mask:02X})")
            print("[*] Press Ctrl+C to stop anytime.")

        try:
            sleep(3600)
        except KeyboardInterrupt:
            print("\n[*] Stopping scanner manually...")

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)
        g_client.close()