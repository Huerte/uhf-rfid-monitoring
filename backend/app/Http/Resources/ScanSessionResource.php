<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reader_id'      => $this->reader_id,
            'protocol'       => $this->protocol,
            'antenna'        => $this->antenna,
            'read_tid'       => $this->read_tid,
            'read_user_data' => $this->read_user_data,
            'status'         => $this->status,
            'error_message'  => $this->error_message,
            'tag_count'      => $this->whenCounted('tags'),
            'started_at'     => $this->started_at?->toIso8601String(),
            'ended_at'       => $this->ended_at?->toIso8601String(),
        ];
    }
}
