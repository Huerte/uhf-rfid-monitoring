from uhf.reader import *
from time import *


def receivedGb(gbInfo: LogBaseGbInfo):
    if gbInfo.result == 0:
        print(gbInfo.epc + "-->" + gbInfo.tid)


def receivedGbOver(gbOver: LogBaseGbOver):
    print("LogBaseGbOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Subscription tag callback
        g_client.callGbInfo = receivedGb
        g_client.callGbOver = receivedGbOver

        # Read EPC
        msg = MsgBaseInventoryGb(antennaEnable=EnumG.AntennaNo_1.value,
                                 inventoryMode=EnumG.InventoryMode_Inventory.value)

        # Matching TID Read E280110520007993A8F708A8 Optional Parameters
        # epc_filter = ParamEpcFilter(area=0x00, bitStart=0, hexData="E280110520007993A8F708A8")
        # msg.filter = epc_filter

        # Read TID Default is read-only EPC Optional parameter
        tid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        msg.readTid = tid

        # Read UserData Optional parameters: User sub-segment 1, Starting address 4, Read length 4
        # userData = ParamGbReadUserData(childArea=0x30, start=4, dataLen=4)
        # msg.readUserData = userData

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        # The inventory check will be stopped and the connection closed after 5 seconds.
        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()
