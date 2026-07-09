from uhf.reader import *
from time import *

ACTIVE_ANTENNAS = [1, 2, 3, 4]


def received6b(info: LogBase6bInfo):
    if info.result == 0:
        print(info.tid)


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
            try:
                antenna_mask |= getattr(EnumG, f"AntennaNo_{ant}").value
            except AttributeError:
                print(f"[!] Invalid antenna ID: {ant}. Skipping.")

        if antenna_mask == 0:
            antenna_mask = EnumG.AntennaNo_1.value

        msg = MsgBaseInventory6b(antennaEnable=antenna_mask,
                                 inventoryMode=EnumG.InventoryMode_Inventory.value,
                                 area=EnumG.ReadMode6b_Tid.value)

        if g_client.sendSynMsg(msg) == 0:
            print(f"[*] Scanning on Antennas: {ACTIVE_ANTENNAS} (mask=0x{antenna_mask:02X})")

        try:
            sleep(3600)
        except KeyboardInterrupt:
            pass

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)
        g_client.close()
