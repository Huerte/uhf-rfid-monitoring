from uhf.reader import *
from time import *

ACTIVE_ANTENNAS = [1, 2, 3, 4]


def received6b(info: LogBase6bInfo):
    if info.result == 0:
        print(info.tid + "-->" + info.userData)


def received6bOver(over: LogBase6bOver):
    print("LogBase6bOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.call6bInfo = received6b
        g_client.call6bOver = received6bOver

        antenna_mask = 0
        for ant in ACTIVE_ANTENNAS:
            if 1 <= ant <= 8:
                antenna_mask |= (1 << (ant - 1))
        
        if antenna_mask == 0:
            antenna_mask = 1

        msg = MsgBaseInventory6b(antennaEnable=antenna_mask,
                                 inventoryMode=EnumG.InventoryMode_Inventory.value,
                                 area=EnumG.ReadMode6b_TidAndUserData.value)
        userData = Param6bReadUserData(start=0, dataLen=10)
        msg.readUserData = userData

        # msg.hexMatchTid("E0040000B6B3E808")

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)
        else:
            print(msg.rtMsg)

        sleep(5)

        stop = MsgBaseStop()
        if g_client.sendSynMsg(stop) == 0:
            print(stop.rtMsg)

        g_client.close()
