<?php

use App\Http\Controllers\IngestController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('reader')->group(function () {
    Route::post('connect',    [ReaderController::class, 'connect']);
    Route::post('disconnect', [ReaderController::class, 'disconnect']);
    Route::get('status',      [ReaderController::class, 'status']);
});

Route::prefix('scans')->group(function () {
    Route::get('/',              [ScanController::class, 'index']);
    Route::post('/',             [ScanController::class, 'store']);
    // Bridge ingest endpoint — called by python-bridge/main.py for every antenna read.
    Route::post('/ingest',       [IngestController::class, 'store']);
    Route::get('/{scan}',        [ScanController::class, 'show']);
    Route::post('/{scan}/stop',  [ScanController::class, 'stop']);
    Route::delete('/{scan}',     [ScanController::class, 'destroy']);
});

Route::prefix('tags')->group(function () {
    Route::get('/',             [TagController::class, 'index']);
    Route::get('/export/csv',   [TagController::class, 'exportCsv']);
    Route::get('/{tag}',        [TagController::class, 'show']);
    Route::delete('/{tag}',     [TagController::class, 'destroy']);
});

Route::prefix('write')->group(function () {
    Route::post('/epc',           [WriteController::class, 'writeEpc']);
    Route::post('/epc-filter',    [WriteController::class, 'writeEpcFilter']);
    Route::post('/epc/userdata',  [WriteController::class, 'writeEpcUserData']);
    Route::post('/epc/reserved',  [WriteController::class, 'writeEpcReserved']);
    Route::post('/6b/userdata',   [WriteController::class, 'write6bUserData']);
    Route::post('/gb/epc',        [WriteController::class, 'writeGbEpc']);
    Route::post('/gb/epc-filter', [WriteController::class, 'writeGbEpcFilter']);
    Route::post('/gb/userdata',   [WriteController::class, 'writeGbUserData']);
    Route::post('/gb/safe',       [WriteController::class, 'writeGbSafe']);
});
