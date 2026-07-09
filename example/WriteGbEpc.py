from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Write content
        data = "1234"
        # Returns the PC value plus the content, padded with characters to complete the length if necessary.
        value = getGbData(data)
        print(value)
        # Antenna 1: Write to EPC area, starting address 0. 0x10: Tag encoding area | 0x20: Tag security area | 0x30~0x3F: User sub-area 0~15
        epc = MsgBaseWriteGb(antennaEnable=1, area=0x10, start=0, hexWriteData=value)
        if g_client.sendSynMsg(epc) == 0:
            print(epc.rtMsg)

        # Disconnect
        g_client.close()
