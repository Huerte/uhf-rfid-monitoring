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

        print(f"[*] Starting round-robin scan on Antennas: {ACTIVE_ANTENNAS}")
        try:
            while True:
                for ant in ACTIVE_ANTENNAS:
                    try:
                        ant_enum_val = getattr(EnumG, f"AntennaNo_{ant}").value
                    except AttributeError:
                        continue

                    msg = MsgBaseInventory6b(antennaEnable=ant_enum_val,
                                             inventoryMode=EnumG.InventoryMode_Inventory.value,
                                             area=EnumG.ReadMode6b_Tid.value)
                    
                    if g_client.sendSynMsg(msg) == 0:
                        sleep(0.5)
                        stop = MsgBaseStop()
                        g_client.sendSynMsg(stop)
                    else:
                        sleep(0.5)

        except KeyboardInterrupt:
            pass

        stop = MsgBaseStop()
        g_client.sendSynMsg(stop)

        g_client.close()
