from uhf.reader import *
from time import *


def received6b(info: LogBase6bInfo):
    if info.result == 0:
        print(info.tid)


def received6bOver(over: LogBase6bOver):
    print("LogBase6bOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Subscription tag callback
        g_client.call6bInfo = received6b
        g_client.call6bOver = received6bOver

        # Read 6b tid
        msg = MsgBaseInventory6b(antennaEnable=EnumG.AntennaNo_1.value,
                                 inventoryMode=EnumG.InventoryMode_Inventory.value,
                                 area=EnumG.ReadMode6b_Tid.value)
        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)
        else:
            print(msg.rtMsg)

        # The inventory check will be stopped and the connection closed after 5 seconds.
        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()
