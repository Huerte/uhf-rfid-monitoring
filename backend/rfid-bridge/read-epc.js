import { ReaderClient } from './reader-client.js';
import { buildAntennaMask } from './protocol.js';
import { sendTagToLaravel, appendToCsv } from './ingest.js';
import { config } from './config.js';

async function main() {
  const activeAntennas = config.antennas;
  const client = new ReaderClient();

  client.on('epcInfo', async (epcInfo) => {
    const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
    console.log(`[!] Scanned: ${epcInfo.epc} (Antenna: ${epcInfo.antenna}) at ${timestamp}`);

    appendToCsv(epcInfo, config.csvFilename);
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
