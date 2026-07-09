<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanSession extends Model
{
    protected $fillable = [
        'reader_id', 'protocol', 'antenna', 'read_tid',
        'read_user_data', 'status', 'error_message', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'read_tid' => 'boolean',
        'read_user_data' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
}
