<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\ScanSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tag::with('scanSession')->latest('scanned_at');

        if ($request->filled('session_id')) {
            $query->where('scan_session_id', $request->session_id);
        }

        if ($request->filled('epc')) {
            $query->where('epc', 'like', '%' . $request->epc . '%');
        }

        if ($request->filled('protocol')) {
            $query->where('protocol', $request->protocol);
        }

        if ($request->filled('from')) {
            $query->where('scanned_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('scanned_at', '<=', $request->to);
        }

        return response()->json($query->paginate(50));
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json($tag->load('scanSession'));
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();
        return response()->json(null, 204);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Tag::with('scanSession')->latest('scanned_at');

        if ($request->filled('session_id')) {
            $query->where('scan_session_id', $request->session_id);
        }

        if ($request->filled('epc')) {
            $query->where('epc', 'like', '%' . $request->epc . '%');
        }

        if ($request->filled('protocol')) {
            $query->where('protocol', $request->protocol);
        }

        if ($request->filled('from')) {
            $query->where('scanned_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('scanned_at', '<=', $request->to);
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'session_id', 'protocol', 'epc', 'tid', 'user_data', 'antenna', 'rssi', 'scanned_at']);

            $query->chunk(500, function ($tags) use ($handle) {
                foreach ($tags as $tag) {
                    fputcsv($handle, [
                        $tag->id,
                        $tag->scan_session_id,
                        $tag->protocol,
                        $tag->epc,
                        $tag->tid,
                        $tag->user_data,
                        $tag->antenna,
                        $tag->rssi,
                        $tag->scanned_at,
                    ]);
                }
            });

            fclose($handle);
        }, 'tags_export_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
