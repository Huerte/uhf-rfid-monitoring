import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { ReaderClient } from './reader-client.js';
import { buildAntennaMask } from './protocol.js';
import { sendTagToLaravel, appendToCsv } from './ingest.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Read configuration from Laravel .env file if available
function loadEnvConfig() {
  const envPath = path.resolve(__dirname, '../.env');
  const config = {
    ip: '192.168.1.168',
    port: 8160,
    antennas: [1, 2, 3, 4],
    apiUrl: 'http://127.0.0.1:8000/api/scans/standalone-ingest'
  };

  if (fs.existsSync(envPath)) {
    const envContent = fs.readFileSync(envPath, 'utf-8');
    const lines = envContent.split(/\r?\n/);
    for (const line of lines) {
      if (line.startsWith('RFID_READER_IP=')) {
        config.ip = line.split('=')[1].trim();
      } else if (line.startsWith('RFID_READER_PORT=')) {
        config.port = parseInt(line.split('=')[1].trim(), 10) || 8160;
      } else if (line.startsWith('RFID_ANTENNAS=')) {
        const ants = line.split('=')[1].trim().split(',').map(n => parseInt(n.trim(), 10)).filter(n => !isNaN(n));
        if (ants.length > 0) config.antennas = ants;
      }
    }
  }

  return config;
}

async function main() {
  const config = loadEnvConfig();
  const activeAntennas = config.antennas;
  const client = new ReaderClient();

  client.on('epcInfo', async (epcInfo) => {
    const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
    console.log(`[!] Scanned: ${epcInfo.epc} (Antenna: ${epcInfo.antenna}) at ${timestamp}`);

    appendToCsv(epcInfo);
    await sendTagToLaravel(epcInfo, config.apiUrl);
  });

  client.on('epcOver', () => {
    console.log('--- Batch Scan Cycle Ended ---');
  });

  try {
    await client.openTcp(config.ip, config.port);
    const antennaMask = buildAntennaMask(activeAntennas);

    await client.startInventory(antennaMask);
    console.log(`[*] Scanning on Antennas: [${activeAntennas.join(', ')}] (mask=0x${antennaMask.toString(16).padStart(2, '0').toUpperCase()})`);
    console.log('[*] Press Ctrl+C to stop anytime.');
  } catch (err) {
    console.error(`[!] Failed to start RFID reader: ${err.message}`);
    process.exit(1);
  }

  const handleShutdown = async () => {
    console.log('\n[*] Stopping scanner manually...');
    await client.stopInventory();
    client.close();
    process.exit(0);
  };

  process.on('SIGINT', handleShutdown);
  process.on('SIGTERM', handleShutdown);
}

main();
