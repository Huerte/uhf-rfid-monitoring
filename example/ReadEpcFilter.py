from uhf.reader import *
from time import *


def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        print(epcInfo.epc + "-->" + epcInfo.tid)


def receivedEpcOver(epcOver: LogBaseEpcOver):
    print("LogBaseEpcOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # 订阅标签回调
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

        # Read 
        msg = MsgBaseInventoryEpc(antennaEnable=EnumG.AntennaNo_1.value,
                                  inventoryMode=EnumG.InventoryMode_Inventory.value)

        # Matching TID Read E280110520007993A8F708A8 Optional Parameters
        # epc_filter = ParamEpcFilter(EnumG.ParamFilterArea_TID.value, 0, "E280110520007993A8F708A8")
        # msg.filter = epc_filter

        # Read TID Default is read-only EPC Optional parameter
        tid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        msg.readTid = tid

        # Read UserData Optional Parameters
        # userData = ParamEpcReadUserData(start=0, dataLen=4)  # word
        # msg.readUserData = userData

        # Read reserved area (optional parameters)
        # reserved = ParamEpcReadReserved(start=0, dataLen=4)  # word
        # msg.readReserved = reserved

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        # The inventory check will be stopped and the connection closed after 5 seconds.
        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()
