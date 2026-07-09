from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Wire content
        data = "1234"
        # Antenna 1, write to UserData area, starting address 0
        epc = MsgBaseWriteEpc(antennaEnable=1, area=EnumG.WriteArea_UserData.value, start=0, hexWriteData=data)
        if g_client.sendSynMsg(epc) == 0:
            print(epc.rtMsg)

        # Disconnect
        g_client.close()
