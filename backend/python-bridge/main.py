import asyncio
from fastapi import FastAPI, BackgroundTasks, Request
from pydantic import BaseModel
from typing import Optional, Dict, Any
from uhf.reader import *
import httpx
import threading
import time

app = FastAPI()

g_client = GClient()
reader_lock = threading.Lock()

LARAVEL_API_URL = "http://127.0.0.1:8000/api"
CURRENT_SESSION_ID = None

def send_tag_to_laravel(tag_data: Dict[str, Any]):
    try:
        if CURRENT_SESSION_ID:
            tag_data["session_id"] = CURRENT_SESSION_ID
        
        httpx.post(
            f"{LARAVEL_API_URL}/scans/ingest",
            json={"session_id": CURRENT_SESSION_ID, "tags": [tag_data]},
            timeout=2.0
        )
    except Exception as e:
        print(f"Error sending tag to Laravel: {e}")

def received_epc(epc_info: LogBaseEpcInfo):
    if epc_info.result == 0:
        send_tag_to_laravel({
            "protocol": "epc",
            "epc": epc_info.epc,
            "tid": epc_info.tid,
            "rssi": epc_info.rssi,
            "antenna": epc_info.antId
        })

def received_6b(info: LogBase6bInfo):
    if info.result == 0:
        send_tag_to_laravel({
            "protocol": "6b",
            "tid": info.tid,
            "user_data": info.userData,
            "antenna": info.antId
        })

def received_gb(info: LogBaseGbInfo):
    if info.result == 0:
        send_tag_to_laravel({
            "protocol": "gb",
            "epc": info.epc,
            "tid": info.tid,
            "antenna": info.antId
        })

def received_over(over_info):
    pass

class ConnectReq(BaseModel):
    ip: str = "192.168.1.168"
    port: int = 8160

@app.post("/connect")
def connect(req: ConnectReq):
    with reader_lock:
        if g_client.openTcp((req.ip, req.port)):
            g_client.callEpcInfo = received_epc
            g_client.callEpcOver = received_over
            g_client.call6bInfo = received_6b
            g_client.call6bOver = received_over
            g_client.callGbInfo = received_gb
            g_client.callGbOver = received_over
            return {"status": "connected"}
        return {"status": "error", "message": "Failed to connect"}

@app.post("/disconnect")
def disconnect():
    with reader_lock:
        g_client.close()
        return {"status": "disconnected"}

@app.get("/status")
def status():
    return {"status": "connected" if g_client.isConnect else "disconnected"}

class ScanReq(BaseModel):
    antenna: int = 1
    session_id: int
    read_tid: bool = False
    read_user_data: bool = False
    filter_tid: Optional[str] = None

@app.post("/scan/epc/start")
def scan_epc_start(req: ScanReq):
    global CURRENT_SESSION_ID
    CURRENT_SESSION_ID = req.session_id
    
    msg = MsgBaseInventoryEpc(antennaEnable=req.antenna, inventoryMode=EnumG.InventoryMode_Inventory.value)
    
    if req.filter_tid:
        msg.filter = ParamEpcFilter(EnumG.ParamFilterArea_TID.value, 0, req.filter_tid)
        
    if req.read_tid:
        msg.readTid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        
    if req.read_user_data:
        msg.readUserData = ParamEpcReadUserData(start=0, dataLen=4)
        
    res = g_client.sendSynMsg(msg)
    return {"status": "started" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/scan/6b/start")
def scan_6b_start(req: ScanReq):
    global CURRENT_SESSION_ID
    CURRENT_SESSION_ID = req.session_id
    
    area = EnumG.ReadMode6b_TidAndUserData.value if req.read_user_data else EnumG.ReadMode6b_Tid.value
    msg = MsgBaseInventory6b(antennaEnable=req.antenna, inventoryMode=EnumG.InventoryMode_Inventory.value, area=area)
    
    if req.read_user_data:
        msg.readUserData = Param6bReadUserData(start=0, dataLen=10)
        
    res = g_client.sendSynMsg(msg)
    return {"status": "started" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/scan/gb/start")
def scan_gb_start(req: ScanReq):
    global CURRENT_SESSION_ID
    CURRENT_SESSION_ID = req.session_id
    
    msg = MsgBaseInventoryGb(antennaEnable=req.antenna, inventoryMode=EnumG.InventoryMode_Inventory.value)
    
    if req.filter_tid:
        msg.filter = ParamEpcFilter(area=0x00, bitStart=0, hexData=req.filter_tid)
        
    if req.read_tid:
        msg.readTid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        
    res = g_client.sendSynMsg(msg)
    return {"status": "started" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/scan/stop")
def scan_stop():
    stop_msg = MsgBaseStop()
    res = g_client.sendSynMsg(stop_msg)
    return {"status": "stopped" if res == 0 else "error", "message": stop_msg.rtMsg}

class WriteReq(BaseModel):
    antenna: int = 1
    data: str
    start: int = 1
    filter_tid: Optional[str] = None
    area: Optional[int] = None

@app.post("/write/epc")
def write_epc(req: WriteReq):
    value = getEpcData(req.data)
    msg = MsgBaseWriteEpc(antennaEnable=req.antenna, area=EnumG.WriteArea_Epc.value, start=req.start, hexWriteData=value)
    if req.filter_tid:
        msg.filter = ParamEpcFilter(area=EnumG.ParamFilterArea_TID.value, bitStart=0, hexData=req.filter_tid)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/write/epc/userdata")
def write_epc_userdata(req: WriteReq):
    msg = MsgBaseWriteEpc(antennaEnable=req.antenna, area=EnumG.WriteArea_UserData.value, start=req.start, hexWriteData=req.data)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/write/epc/reserved")
def write_epc_reserved(req: WriteReq):
    msg = MsgBaseWriteEpc(antennaEnable=req.antenna, area=EnumG.WriteArea_Reserved.value, start=req.start, hexWriteData=req.data)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

class Write6BReq(BaseModel):
    antenna: int = 1
    match_tid: str
    data: str
    start: int = 8

@app.post("/write/6b/userdata")
def write_6b_userdata(req: Write6BReq):
    msg = MsgBaseWrite6b(antennaEnable=req.antenna, hexMatchTid=req.match_tid, start=req.start, hexWriteData=req.data)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/write/gb/epc")
def write_gb_epc(req: WriteReq):
    value = getGbData(req.data)
    msg = MsgBaseWriteGb(antennaEnable=req.antenna, area=0x10, start=req.start, hexWriteData=value)
    if req.filter_tid:
        msg.filter = ParamEpcFilter(area=0x00, bitStart=0, hexData=req.filter_tid)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/write/gb/userdata")
def write_gb_userdata(req: WriteReq):
    msg = MsgBaseWriteGb(antennaEnable=req.antenna, area=0x30, start=req.start, hexWriteData=req.data)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}

@app.post("/write/gb/safe")
def write_gb_safe(req: WriteReq):
    msg = MsgBaseWriteGb(antennaEnable=req.antenna, area=0x20, start=req.start, hexWriteData=req.data)
    res = g_client.sendSynMsg(msg)
    return {"status": "success" if res == 0 else "error", "message": msg.rtMsg}
