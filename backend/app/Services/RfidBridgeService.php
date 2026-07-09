<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RfidBridgeService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('rfid.bridge_url', 'http://127.0.0.1:8000'), '/');
        $this->timeout = config('rfid.bridge_timeout', 10);
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)->timeout($this->timeout);
    }

    public function connect(string $ip, int $port): array
    {
        return $this->client()->post('/connect', compact('ip', 'port'))->json();
    }

    public function disconnect(): array
    {
        return $this->client()->post('/disconnect')->json();
    }

    public function status(): array
    {
        return $this->client()->get('/status')->json();
    }

    public function startScanEpc(array $params): array
    {
        return $this->client()->post('/scan/epc/start', $params)->json();
    }

    public function startScan6b(array $params): array
    {
        return $this->client()->post('/scan/6b/start', $params)->json();
    }

    public function startScanGb(array $params): array
    {
        return $this->client()->post('/scan/gb/start', $params)->json();
    }

    public function stopScan(): array
    {
        return $this->client()->post('/scan/stop')->json();
    }

    public function writeEpc(array $params): array
    {
        return $this->client()->post('/write/epc', $params)->json();
    }

    public function writeEpcFilter(array $params): array
    {
        return $this->client()->post('/write/epc-filter', $params)->json();
    }

    public function writeEpcUserData(array $params): array
    {
        return $this->client()->post('/write/epc/userdata', $params)->json();
    }

    public function writeEpcReserved(array $params): array
    {
        return $this->client()->post('/write/epc/reserved', $params)->json();
    }

    public function write6bUserData(array $params): array
    {
        return $this->client()->post('/write/6b/userdata', $params)->json();
    }

    public function writeGbEpc(array $params): array
    {
        return $this->client()->post('/write/gb/epc', $params)->json();
    }

    public function writeGbEpcFilter(array $params): array
    {
        return $this->client()->post('/write/gb/epc-filter', $params)->json();
    }

    public function writeGbUserData(array $params): array
    {
        return $this->client()->post('/write/gb/userdata', $params)->json();
    }

    public function writeGbSafe(array $params): array
    {
        return $this->client()->post('/write/gb/safe', $params)->json();
    }

    public function bridgeAvailable(): bool
    {
        try {
            $this->client()->timeout(2)->get('/status');
            return true;
        } catch (ConnectionException) {
            return false;
        }
    }
}
