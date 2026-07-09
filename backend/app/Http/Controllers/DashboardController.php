<?php

namespace App\Http\Controllers;

use App\Models\ScanSession;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_tags'     => Tag::count(),
            'total_sessions' => ScanSession::count(),
            'running'        => ScanSession::where('status', 'running')->count(),
            'recent_tags'    => Tag::latest('scanned_at')->limit(20)->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}
