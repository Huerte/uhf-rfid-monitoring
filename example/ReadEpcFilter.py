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
            if 1 <= ant <= 8:
                antenna_mask |= (1 << (ant - 1))
        
        if antenna_mask == 0:
            antenna_mask = 1

        msg = MsgBaseInventoryEpc(antennaEnable=antenna_mask,
                                  inventoryMode=EnumG.InventoryMode_Inventory.value)

        # epc_filter = ParamEpcFilter(EnumG.ParamFilterArea_TID.value, 0, "E280110520007993A8F708A8")
        # msg.filter = epc_filter

        tid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        msg.readTid = tid

        # userData = ParamEpcReadUserData(start=0, dataLen=4)
        # msg.readUserData = userData

        # reserved = ParamEpcReadReserved(start=0, dataLen=4)
        # msg.readReserved = reserved

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()
