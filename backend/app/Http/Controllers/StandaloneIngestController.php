<?php

namespace App\Http\Controllers;

use App\Events\TagScanned;
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

        $antennaId = (int) ($data['antenna'] ?? 1);

        $antColumn = in_array($antennaId, [1, 2, 3, 4]) ? "ant{$antennaId}" : 'ant1';

        $reader = Reader::firstOrCreate(
            ['ip' => '127.0.0.1', 'port' => 0],
            ['connected' => true]
        );

        $session = ScanSession::firstOrCreate(
            ['reader_id' => $reader->id, 'status' => 'running'],
            ['protocol' => $data['protocol'] ?? 'epc', 'antenna' => 1]
        );

        $tag = Tag::where('scan_session_id', $session->id)
                   ->where('epc', $data['epc'])
                   ->first();

        if ($tag === null) {
            $tag = Tag::create([
                'scan_session_id' => $session->id,
                'protocol'        => $data['protocol'] ?? 'epc',
                'epc'             => $data['epc'],
                'tid'             => $data['tid'] ?? null,
                'rssi'            => $data['rssi'] ?? null,
                'antenna'         => $antennaId,
                'scanned_at'      => now(),
                $antColumn        => 1,
            ]);
        } else {
            $tag->increment($antColumn);
            $tag->update([
                'scanned_at' => now(),
                'rssi'       => $data['rssi'] ?? $tag->rssi,
                'antenna'    => $antennaId,
            ]);
            $tag->refresh();
        }

        TagScanned::dispatch($tag);

        return response()->json(['status' => 'success', 'tag_id' => $tag->id], 200);
    }
}
