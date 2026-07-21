import assert from 'assert';
import { buildMsgInventoryEpc, buildMsgStop, buildMsgWriteEpc, parseFrame, parseLogBaseEpcInfo, crc16 } from './protocol.js';

console.log('Running RFID Bridge Protocol Tests...');

// 1. Inventory EPC Frame (Antenna 1)
const frame1 = buildMsgInventoryEpc(1, 1);
const expected1 = '5A0001021000050000000101F487';
assert.strictEqual(frame1.toString('hex').toUpperCase(), expected1, 'Inventory EPC (Antenna 1) frame mismatch');
console.log('✓ Inventory EPC (Antenna 1) frame verified');

// 2. Inventory EPC Frame (Antennas 1..4 = mask 15 = 0x0F)
const frame15 = buildMsgInventoryEpc(15, 1);
const expected15 = '5A0001021000050000000F01D788';
assert.strictEqual(frame15.toString('hex').toUpperCase(), expected15, 'Inventory EPC (Antennas 1-4) frame mismatch');
console.log('✓ Inventory EPC (Antennas 1-4) frame verified');

// 3. Stop Frame
const stopFrame = buildMsgStop();
const expectedStop = '5A000102FF0000885A';
assert.strictEqual(stopFrame.toString('hex').toUpperCase(), expectedStop, 'Stop frame mismatch');
console.log('✓ Stop frame verified');

// 4. Frame Parser & CRC Validation
const parsedStop = parseFrame(stopFrame);
assert.notStrictEqual(parsedStop, null);
assert.strictEqual(parsedStop.isValidCrc, true);
assert.strictEqual(parsedStop.msgId, 0xFF);
console.log('✓ Frame parser and CRC-16 check verified');

// 5. Parse LogBaseEpcInfo Payload
// Payload: Result(0), PC(0x3000), EPC Len(12), EPC(E28011223344556677889900), AntId(1), RSSI(75)
const logPayloadHex = '0030000CE28011223344556677889900014B';
const logPayload = Buffer.from(logPayloadHex, 'hex');
const epcInfo = parseLogBaseEpcInfo(logPayload);

assert.notStrictEqual(epcInfo, null);
assert.strictEqual(epcInfo.result, 0);
assert.strictEqual(epcInfo.epc, 'E28011223344556677889900');
assert.strictEqual(epcInfo.antenna, 1);
assert.strictEqual(epcInfo.rssi, 75);
console.log('✓ LogBaseEpcInfo payload parsing verified');

console.log('\nAll protocol unit tests passed successfully!');
