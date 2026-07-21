import net from 'net';
import { EventEmitter } from 'events';
import { HEADER_BYTE, parseFrame, parseLogBaseEpcInfo, buildMsgInventoryEpc, buildMsgStop } from './protocol.js';

export class ReaderClient extends EventEmitter {
  constructor() {
    super();
    this.socket = null;
    this.buffer = Buffer.alloc(0);
    this.connected = false;
  }

  openTcp(ip = '192.168.1.168', port = 8160) {
    return new Promise((resolve, reject) => {
      this.socket = new net.Socket();

      this.socket.connect(port, ip, () => {
        this.connected = true;
        console.log(`[*] Connected to RFID Reader at ${ip}:${port}`);
        resolve(true);
      });

      this.socket.on('data', (chunk) => {
        this.buffer = Buffer.concat([this.buffer, chunk]);
        this.processBuffer();
      });

      this.socket.on('error', (err) => {
        console.error(`[!] Reader socket error: ${err.message}`);
        this.emit('error', err);
        reject(err);
      });

      this.socket.on('close', () => {
        this.connected = false;
        console.log('[*] Reader connection closed');
        this.emit('close');
      });
    });
  }

  processBuffer() {
    while (this.buffer.length >= 9) {
      // Find sync header byte
      const headerIdx = this.buffer.indexOf(HEADER_BYTE);
      if (headerIdx === -1) {
        this.buffer = Buffer.alloc(0);
        break;
      }
      if (headerIdx > 0) {
        this.buffer = this.buffer.subarray(headerIdx);
      }

      const frame = parseFrame(this.buffer);
      if (!frame) {
        // Incomplete frame, wait for more data
        break;
      }

      // Advance buffer past this frame
      this.buffer = this.buffer.subarray(frame.totalLength);

      if (frame.isValidCrc) {
        if (process.env.DEBUG_FRAMES) {
          console.log(`[DBG] Frame msgId=0x${frame.msgId.toString(16).padStart(2,'0')} baseType=0x${frame.baseType.toString(16).padStart(2,'0')} dataLen=${frame.dataLen} payload=${frame.payload.toString('hex').toUpperCase()}`);
        }
        this.handleFrame(frame);
      } else {
        // CRC mismatch — log raw bytes to help diagnose framing issues
        console.warn(`[!] Bad CRC frame (raw): ${this.buffer.subarray(0, Math.min(frame.totalLength, 64)).toString('hex').toUpperCase()}`);
      }
    }
  }

  handleFrame(frame) {
    // byte 3 of frame is the mt nibble byte: lower 4 bits = mt_8_11
    // Msg_Type_Bit_Base = 2 identifies reader-pushed log events
    const isBaseLog = (frame.baseType & 0x0F) === 2;

    if (isBaseLog && frame.msgId === 0x00) { // BaseLogMid_Epc
      const epcInfo = parseLogBaseEpcInfo(frame.payload);
      if (process.env.DEBUG_FRAMES) {
        console.log(`[DBG] Parsed epcInfo:`, JSON.stringify(epcInfo));
      }
      if (epcInfo) {
        if (!epcInfo.epc) {
          console.warn(`[!] Empty EPC. Raw payload: ${frame.payload.toString('hex').toUpperCase()}`);
        }
        if (epcInfo.result === 0) {
          this.emit('epcInfo', epcInfo);
        }
      }
    } else if (isBaseLog && frame.msgId === 0x01) { // BaseLogMid_EpcOver
      this.emit('epcOver');
    }
  }

  sendSynMsg(frameBytes) {
    return new Promise((resolve, reject) => {
      if (!this.socket || !this.connected) {
        return reject(new Error('Socket not connected'));
      }
      this.socket.write(frameBytes, (err) => {
        if (err) return reject(err);
        resolve(0);
      });
    });
  }

  startInventory(antennaMask, options = {}) {
    const msg = buildMsgInventoryEpc(antennaMask, 1, options);
    return this.sendSynMsg(msg);
  }

  async stopInventory() {
    if (this.connected) {
      try {
        const stopMsg = buildMsgStop();
        await this.sendSynMsg(stopMsg);
      } catch (e) {
        console.warn(`[!] Error sending stop message: ${e.message}`);
      }
    }
  }

  close() {
    if (this.socket) {
      this.socket.destroy();
      this.connected = false;
    }
  }
}
