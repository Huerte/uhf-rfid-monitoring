<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    protected $fillable = [
        'scan_session_id', 'protocol', 'epc', 'tid',
        'user_data', 'antenna', 'rssi', 'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class);
    }
}
