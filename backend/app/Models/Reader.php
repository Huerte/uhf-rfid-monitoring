<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reader extends Model
{
    protected $fillable = ['name', 'ip', 'port', 'connected', 'connected_at'];

    protected $casts = [
        'connected' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function scanSessions(): HasMany
    {
        return $this->hasMany(ScanSession::class);
    }
}
