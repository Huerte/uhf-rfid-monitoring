from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # If the content being written is less than one byte, pad it with zeros on the right.
        data = "12"
        # Antenna 1, write to the user area, starting address 8 (bytes). The first eight bytes are its own TID.
        epc = MsgBaseWrite6b(antennaEnable=1, hexMatchTid="E0040000F8B3E808", start=8, hexWriteData=data)
        if g_client.sendSynMsg(epc) == 0:
            print(epc.rtMsg)

        # Disconnect
        g_client.close()
