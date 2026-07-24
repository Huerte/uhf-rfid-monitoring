import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

dotenv.config({ path: path.resolve(__dirname, '../.env') });

export const config = {
  ip: process.env.RFID_READER_IP || '192.168.1.168',
  port: parseInt(process.env.RFID_READER_PORT, 10) || 8160,
  antennas: process.env.RFID_ANTENNAS
    ? process.env.RFID_ANTENNAS.split(',').map((n) => parseInt(n.trim(), 10)).filter((n) => !isNaN(n))
    : [1, 2, 3, 4],
  apiUrl: process.env.RFID_TARGET_URL || 'http://127.0.0.1:8000/api/scans/standalone-ingest',
  csvFilename: process.env.RFID_CSV_FILENAME || 'scanned_tags.csv',
};
