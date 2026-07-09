from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Write content
        data = "1234"
        # Returns the PC value plus the content, padded with characters to complete the length if necessary.
        value = getEpcData(data)
        print(value)
        # Antenna No. 1 Write epc area starting address 1 (0 is crc and cannot be written)
        epc = MsgBaseWriteEpc(antennaEnable=1, area=EnumG.WriteArea_Epc.value, start=1, hexWriteData=value)
        if g_client.sendSynMsg(epc) == 0:
            print(epc.rtMsg)

        # Disconnect
        g_client.close()
