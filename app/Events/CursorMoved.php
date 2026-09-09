<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursorMovedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $id;
    public $username;
    public $color;
    public $pctX;
    public $pctY;

    public function __construct($roomId, $payload)
    {
        $this->roomId = $roomId;
        $this->id = $payload['id'] ?? null;
        $this->username = $payload['username'] ?? 'Anonim';
        $this->color = $payload['color'] ?? '#e63946';
        $this->pctX = $payload['pctX'] ?? 0;
        $this->pctY = $payload['pctY'] ?? 0;
    }

    public function broadcastOn()
    {
        return new Channel('canvas-room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'cursor.moved';
    }
}
