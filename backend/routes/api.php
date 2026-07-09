<?php

use App\Http\Controllers\StandaloneIngestController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('scans')->group(function () {
    // Standalone ingest endpoint - for simple Python scripts bypassing the bridge
    Route::post('/standalone-ingest', [StandaloneIngestController::class, 'store']);
});

Route::prefix('tags')->group(function () {
    Route::get('/',             [TagController::class, 'index']);
    Route::get('/export/csv',   [TagController::class, 'exportCsv']);
    Route::get('/{tag}',        [TagController::class, 'show']);
    Route::delete('/{tag}',     [TagController::class, 'destroy']);
});
