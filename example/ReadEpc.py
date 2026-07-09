from uhf.reader import *
from time import *
from datetime import datetime
import os
import urllib.request
import json

# This function triggers EVERY time a tag passes the antenna
def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        tag_id = epcInfo.epc
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        print(f"[!] Scanned: {tag_id} at {timestamp}")
        
        # 1. Save to CSV locally (Original Behavior)
        file_exists = os.path.isfile("scanned_tags.csv")
        with open("scanned_tags.csv", mode="a", encoding="utf-8") as f:
            if not file_exists:
                f.write("Timestamp,Tag_ID\n")
            f.write(f"{timestamp},{tag_id}\n")

        # 2. Push directly to Laravel Backend
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
                headers={'Content-Type': 'application/json'}
            )
            urllib.request.urlopen(req, timeout=2.0)
        except Exception as e:
            print(f"[!] Failed to send tag {tag_id} to Laravel: {e}")

def receivedEpcOver(epcOver: LogBaseEpcOver):
    print("--- Batch Scan Cycle Ended ---")

if __name__ == '__main__':
    g_client = GClient()
    
    # Connect directly to your network reader
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

        # Start Reading
        msg = MsgBaseInventoryEpc(antennaEnable=EnumG.AntennaNo_1.value,
                                  inventoryMode=EnumG.InventoryMode_Inventory.value)
        if g_client.sendSynMsg(msg) == 0:
            print("[*] Scan engine started successfully.")
            print("[*] Keeping scanner alive for 1 hour. Press Ctrl+C to stop anytime.")

        try:
            # Let it run for 1 hour (5 seconds) instead of cutting off immediately
            sleep(3600)  # 1 hour in seconds
        except KeyboardInterrupt:
            print("\n[*] Stopping scanner manually...")

        # Clean shutdown sequence
        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print("[*] Scanning Stopped.")

        g_client.close()