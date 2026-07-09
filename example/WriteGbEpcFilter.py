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
        msg = MsgBaseWriteGb(antennaEnable=1, area=0x10, start=0, hexWriteData=value)

        # Write EPC to match the specified tid tag
        tid = "E200F3A54450363539320009"
        epc_filter = ParamEpcFilter(area=0x00, bitStart=0,
                                    hexData=tid)
        msg.filter = epc_filter

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)

        # Disconnect
        g_client.close()
