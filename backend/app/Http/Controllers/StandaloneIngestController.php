<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Models\ScanSession;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandaloneIngestController extends Controller
{
    /**
     * Receives a single tag read directly from standalone Python scripts.
     * Automatically handles creating dummy readers/sessions if the DB is empty.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'protocol' => 'sometimes|string',
            'epc'      => 'required|string',
            'tid'      => 'nullable|string',
            'rssi'     => 'nullable|numeric',
            'antenna'  => 'nullable|integer',
        ]);

        // Auto-create a dummy reader and session so foreign keys don't fail on an empty DB
        $reader = Reader::firstOrCreate(
            ['ip' => '127.0.0.1', 'port' => 0],
            ['connected' => true]
        );

        $session = ScanSession::firstOrCreate(
            ['reader_id' => $reader->id, 'status' => 'running'],
            ['protocol' => $data['protocol'] ?? 'epc', 'antenna' => 1]
        );

        $tag = Tag::create([
            'scan_session_id' => $session->id,
            'protocol'        => $data['protocol'] ?? 'epc',
            'epc'             => $data['epc'],
            'tid'             => $data['tid'] ?? null,
            'rssi'            => $data['rssi'] ?? null,
            'antenna'         => $data['antenna'] ?? 1,
            'scanned_at'      => now(),
        ]);

        \App\Events\TagScanned::dispatch($tag);

        return response()->json(['status' => 'success', 'tag_id' => $tag->id], 201);
    }
}
