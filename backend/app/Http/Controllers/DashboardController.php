<?php

namespace App\Http\Controllers;

use App\Models\ScanSession;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_sessions' => ScanSession::count(),
            'running'        => ScanSession::where('status', 'running')->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
