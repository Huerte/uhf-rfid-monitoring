from uhf.reader import *
from time import *

ACTIVE_ANTENNAS = [1, 2, 3, 4]


def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        print(epcInfo.epc + "-->" + epcInfo.tid)


def receivedEpcOver(epcOver: LogBaseEpcOver):
    print("LogBaseEpcOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

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

        tid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        msg.readTid = tid

        if g_client.sendSynMsg(msg) == 0:
            print(f"[*] Scanning on Antennas: {ACTIVE_ANTENNAS} (mask=0x{antenna_mask:02X})")

        try:
            sleep(3600)
        except KeyboardInterrupt:
            pass

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)
        g_client.close()
