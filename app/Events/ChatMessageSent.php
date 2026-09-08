<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData;
    public $roomId;

    public function __construct($messageData, $roomId)
    {
        $this->messageData = $messageData;
        $this->roomId = $roomId;
    }

    public function broadcastOn()
    {
        return new Channel('canvas-room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'chat.sent';
    }
}