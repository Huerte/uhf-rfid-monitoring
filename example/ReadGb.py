from uhf.reader import *
from time import *


def receivedEpc(epcInfo: LogBaseEpcInfo):
    if epcInfo.result == 0:
        print(epcInfo.epc)


def receivedEpcOver(epcOver: LogBaseEpcOver):
    print("LogBaseEpcOver")


if __name__ == 'main':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Subscription tag callback
        g_client.callEpcInfo = receivedEpc
        g_client.callEpcOver = receivedEpcOver

        # Read EPC
        msg = MsgBaseInventoryEpc(antennaEnable=EnumG.AntennaNo_1.value,
                                  inventoryMode=EnumG.InventoryMode_Inventory.value)
        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        # The inventory check will be stopped and the connection closed after 5 seconds.
        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()