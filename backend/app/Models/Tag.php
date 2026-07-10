<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'scan_session_id', 'protocol', 'epc', 'tid',
        'user_data', 'antenna', 'rssi', 'scanned_at',
        'ant1', 'ant2', 'ant3', 'ant4',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class);
    }
}
