from uhf.reader import *
from time import *

ACTIVE_ANTENNAS = [1, 2, 3, 4]


def receivedGb(gbInfo: LogBaseGbInfo):
    if gbInfo.result == 0:
        print(gbInfo.epc + "-->" + gbInfo.tid)


def receivedGbOver(gbOver: LogBaseGbOver):
    print("LogBaseGbOver")


if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        g_client.callGbInfo = receivedGb
        g_client.callGbOver = receivedGbOver

        antenna_mask = 0
        for ant in ACTIVE_ANTENNAS:
            try:
                antenna_mask |= getattr(EnumG, f"AntennaNo_{ant}").value
            except AttributeError:
                print(f"[!] Invalid antenna ID: {ant}. Skipping.")

        if antenna_mask == 0:
            antenna_mask = EnumG.AntennaNo_1.value

        msg = MsgBaseInventoryGb(antennaEnable=antenna_mask,
                                 inventoryMode=EnumG.InventoryMode_Inventory.value)

        # epc_filter = ParamEpcFilter(area=0x00, bitStart=0, hexData="E280110520007993A8F708A8")
        # msg.filter = epc_filter

        tid = ParamEpcReadTid(mode=EnumG.ParamTidMode_Auto.value, dataLen=6)
        msg.readTid = tid

        # userData = ParamGbReadUserData(childArea=0x30, start=4, dataLen=4)
        # msg.readUserData = userData

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        try:
            sleep(3600)
        except KeyboardInterrupt:
            pass

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)
        g_client.close()
