<?php

namespace App\Events;

use App\Models\Tag;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagScanned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Tag $tag) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('rfid.live'),
            new Channel('rfid.session.' . $this->tag->scan_session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tag.scanned';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->tag->id,
            'session_id'  => $this->tag->scan_session_id,
            'protocol'    => $this->tag->protocol,
            'epc'         => $this->tag->epc,
            'tid'         => $this->tag->tid,
            'user_data'   => $this->tag->user_data,
            'antenna'     => $this->tag->antenna,
            'rssi'        => $this->tag->rssi,
            'scanned_at'  => $this->tag->scanned_at?->toIso8601String(),
        ];
    }
}
