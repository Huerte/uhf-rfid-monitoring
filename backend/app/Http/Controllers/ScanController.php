<?php

namespace App\Http\Controllers;

use App\Events\TagScanned;
use App\Models\Reader;
use App\Models\ScanSession;
use App\Models\Tag;
use App\Services\RfidBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(private RfidBridgeService $bridge) {}

    public function index(): JsonResponse
    {
        return response()->json(
            ScanSession::with('reader')->latest()->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reader_id'      => 'required|exists:readers,id',
            'protocol'       => 'required|in:epc,6b,gb',
            'antenna'        => 'sometimes|integer|min:1|max:8',
            'read_tid'       => 'sometimes|boolean',
            'read_user_data' => 'sometimes|boolean',
            'filter_tid'     => 'sometimes|string',
        ]);

        $session = ScanSession::create([
            'reader_id'      => $data['reader_id'],
            'protocol'       => $data['protocol'],
            'antenna'        => $data['antenna'] ?? 1,
            'read_tid'       => $data['read_tid'] ?? false,
            'read_user_data' => $data['read_user_data'] ?? false,
            'status'         => 'running',
        ]);

        $bridgePayload = [
            'antenna'        => $session->antenna,
            'read_tid'       => $session->read_tid,
            'read_user_data' => $session->read_user_data,
            'session_id'     => $session->id,
        ];

        if (isset($data['filter_tid'])) {
            $bridgePayload['filter_tid'] = $data['filter_tid'];
        }

        $method = match ($data['protocol']) {
            '6b'    => 'startScan6b',
            'gb'    => 'startScanGb',
            default => 'startScanEpc',
        };

        $result = $this->bridge->$method($bridgePayload);

        if (($result['status'] ?? '') === 'error') {
            $session->update(['status' => 'error', 'error_message' => $result['message'] ?? 'Bridge error']);
        }

        return response()->json(['session' => $session, 'bridge' => $result], 201);
    }

    public function show(ScanSession $scan): JsonResponse
    {
        return response()->json($scan->load(['reader', 'tags']));
    }

    public function stop(ScanSession $scan): JsonResponse
    {
        $result = $this->bridge->stopScan();

        $scan->update(['status' => 'stopped', 'ended_at' => now()]);

        return response()->json(['session' => $scan, 'bridge' => $result]);
    }

    public function destroy(ScanSession $scan): JsonResponse
    {
        $scan->delete();
        return response()->json(null, 204);
    }

    public function ingest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => 'required|exists:scan_sessions,id',
            'tags'       => 'required|array',
            'tags.*.epc' => 'nullable|string',
            'tags.*.tid' => 'nullable|string',
            'tags.*.user_data' => 'nullable|string',
            'tags.*.antenna'   => 'nullable|integer',
            'tags.*.rssi'      => 'nullable|integer',
            'tags.*.protocol'  => 'nullable|string',
        ]);

        $session = ScanSession::findOrFail($data['session_id']);
        $created = [];

        foreach ($data['tags'] as $t) {
            $tag = Tag::create([
                'scan_session_id' => $session->id,
                'protocol'        => $t['protocol'] ?? $session->protocol,
                'epc'             => $t['epc'] ?? null,
                'tid'             => $t['tid'] ?? null,
                'user_data'       => $t['user_data'] ?? null,
                'antenna'         => $t['antenna'] ?? $session->antenna,
                'rssi'            => $t['rssi'] ?? null,
                'scanned_at'      => now(),
            ]);

            broadcast(new TagScanned($tag))->toOthers();
            $created[] = $tag;
        }

        return response()->json(['ingested' => count($created)], 201);
    }
}
