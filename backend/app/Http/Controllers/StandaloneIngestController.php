<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Models\ScanSession;
use App\Models\Tag;
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request;

class StandaloneIngestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'protocol' => 'sometimes|string',
            'epc'      => 'required|string',
            'tid'      => 'nullable|string',
            'rssi'     => 'nullable|numeric',
            'antenna'  => 'nullable|integer',
        ]);

        $reader = Reader::firstOrCreate(
            ['ip' => '127.0.0.1', 'port' => 0],
            ['connected' => true]
        );

        $session = ScanSession::firstOrCreate(
            ['reader_id' => $reader->id, 'status' => 'running'],
            ['protocol' => $data['protocol'] ?? 'epc', 'antenna' => 1]
        );

        try {
            $tag = Tag::firstOrCreate(
                [
                    'scan_session_id' => $session->id,
                    'epc'             => $data['epc'],
                ],
                [
                    'protocol'   => $data['protocol'] ?? 'epc',
                    'tid'        => $data['tid'] ?? null,
                    'rssi'       => $data['rssi'] ?? null,
                    'antenna'    => $data['antenna'] ?? 1,
                    'scanned_at' => now(),
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (!$tag->wasRecentlyCreated) {
            return response()->json(['status' => 'ignored'], 200);
        }

        \App\Events\TagScanned::dispatch($tag);

        return response()->json(['status' => 'success', 'tag_id' => $tag->id], 201);
    }
}
