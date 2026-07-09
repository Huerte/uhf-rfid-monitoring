from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Write content
        data = "12345678"
        # Antenna 1, write reserved area, start address 2
        # The first four bytes represent the destroy password, and the last four bytes represent the access password.
        epc = MsgBaseWriteEpc(antennaEnable=1, area=EnumG.WriteArea_Reserved.value, start=2, hexWriteData=data)
        if g_client.sendSynMsg(epc) == 0:
            print(epc.rtMsg)

        # Disconnect
        g_client.close()
