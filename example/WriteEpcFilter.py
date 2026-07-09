from uhf.reader import *
from time import *

if __name__ == '__main__':
    g_client = GClient()
    # if g_client.openSerial(("COM7", 115200)):
    if g_client.openTcp(("192.168.1.168", 8160)):
        # Write content
        data = "1234"
        # 
        value = getEpcData(data)
        print(value)
        # Returns the PC value plus the content, padded with characters to complete the length if necessary.
        msg = MsgBaseWriteEpc(antennaEnable=1, area=EnumG.WriteArea_Epc.value, start=1, hexWriteData=value)

        # Write EPC to match the specified tid tag
        tid = "E200F3A54450363539320009"
        epc_filter = ParamEpcFilter(area=EnumG.ParamFilterArea_TID.value, bitStart=0,
                                    hexData=tid)
        msg.filter = epc_filter

        if g_client.sendSynMsg(msg) == 0:
            print(msg.rtMsg)
        else:
            print(msg.rtMsg)

        # Disconnect
        g_client.close()
