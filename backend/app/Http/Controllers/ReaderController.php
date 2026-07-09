<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Services\RfidBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReaderController extends Controller
{
    public function __construct(private RfidBridgeService $bridge) {}

    public function connect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ip'   => 'sometimes|ip',
            'port' => 'sometimes|integer|min:1|max:65535',
        ]);

        $ip   = $data['ip']   ?? config('rfid.reader_ip');
        $port = $data['port'] ?? config('rfid.reader_port');

        $result = $this->bridge->connect($ip, $port);

        if (($result['status'] ?? '') === 'connected') {
            $reader = Reader::firstOrCreate(['ip' => $ip, 'port' => $port]);
            $reader->update(['connected' => true, 'connected_at' => now()]);
            $result['reader_id'] = $reader->id;
        }

        return response()->json($result);
    }

    public function disconnect(): JsonResponse
    {
        $result = $this->bridge->disconnect();
        Reader::where('connected', true)->update(['connected' => false]);
        return response()->json($result);
    }

    public function status(): JsonResponse
    {
        return response()->json($this->bridge->status());
    }
}
