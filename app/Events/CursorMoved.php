<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursorMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cursorData;
    public $roomId;

    public function __construct($cursorData, $roomId)
    {
        $this->cursorData = $cursorData;
        $this->roomId = $roomId;
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