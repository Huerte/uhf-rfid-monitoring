import http from 'http';
import fs from 'fs';

export async function sendTagToLaravel(epcInfo, targetUrl) {
  const url = new URL(targetUrl);
  const payload = JSON.stringify({
    protocol: 'epc',
    epc: epcInfo.epc,
    tid: epcInfo.tid || null,
    rssi: epcInfo.rssi || null,
    antenna: epcInfo.antenna || 1
  });

  const options = {
    hostname: url.hostname,
    port: url.port || 80,
    path: url.pathname,
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Content-Length': Buffer.byteLength(payload)
    },
    timeout: 2000
  };

  return new Promise((resolve) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve(true);
        } else {
          console.error(`[!] Failed to send tag ${epcInfo.epc} to Laravel: HTTP ${res.statusCode} - ${body}`);
          resolve(false);
        }
      });
    });

    req.on('error', (e) => {
      console.error(`[!] Failed to connect to Laravel: ${e.message}`);
      resolve(false);
    });

    req.on('timeout', () => {
      req.destroy();
      console.error(`[!] Request to Laravel timed out for tag ${epcInfo.epc}`);
      resolve(false);
    });

    req.write(payload);
    req.end();
  });
}

export function appendToCsv(epcInfo, filename = 'scanned_tags.csv') {
  const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
  const line = `${timestamp},${epcInfo.epc}\n`;
  const fileExists = fs.existsSync(filename);

  if (!fileExists) {
    fs.writeFileSync(filename, 'Timestamp,Tag_ID\n', 'utf-8');
  }
  fs.appendFileSync(filename, line, 'utf-8');
}
