<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'session_id' => $this->scan_session_id,
            'protocol'   => $this->protocol,
            'epc'        => $this->epc,
            'tid'        => $this->tid,
            'user_data'  => $this->user_data,
            'antenna'    => $this->antenna,
            'rssi'       => $this->rssi,
            'scanned_at' => $this->scanned_at?->toIso8601String(),
        ];
    }
}
