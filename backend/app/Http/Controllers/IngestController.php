<?php

namespace App\Http\Controllers;

use App\Models\ScanSession;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestController extends Controller
{
    /**
     * Receives tag reads from the Python bridge and persists them.
     *
     * POST /api/scans/ingest
     * Body: { "session_id": int, "tags": [ { "protocol", "epc", "tid", "rssi", "antenna" } ] }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id'        => 'required|integer',
            'tags'              => 'required|array|min:1',
            'tags.*.protocol'   => 'required|in:epc,6b,gb',
            'tags.*.epc'        => 'nullable|string',
            'tags.*.tid'        => 'nullable|string',
            'tags.*.user_data'  => 'nullable|string',
            'tags.*.rssi'       => 'nullable|numeric',
            'tags.*.antenna'    => 'nullable|integer',
        ]);

        // Tolerate a session that no longer exists (bridge restart edge case).
        $session = ScanSession::find($data['session_id']);
        if (! $session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $now = now();
        $rows = array_map(fn ($tag) => [
            'scan_session_id' => $session->id,
            'protocol'        => $tag['protocol'],
            'epc'             => $tag['epc']       ?? null,
            'tid'             => $tag['tid']        ?? null,
            'user_data'       => $tag['user_data']  ?? null,
            'rssi'            => $tag['rssi']       ?? null,
            'antenna'         => $tag['antenna']    ?? null,
            'scanned_at'      => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $data['tags']);

        Tag::insert($rows);

        return response()->json(['inserted' => count($rows)], 201);
    }
}
