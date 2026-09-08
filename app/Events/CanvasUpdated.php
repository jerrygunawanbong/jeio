<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CanvasUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $canvasData;
    public $roomId;

    public function __construct($canvasData, $roomId)
    {
        $this->canvasData = $canvasData;
        $this->roomId = $roomId;
    }

    public function broadcastOn()
    {
        // Channel unik per room
        return new Channel('canvas-room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'canvas.updated';
    }
}