<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $username;
    public $message;

    public function __construct($roomId, $username, $message)
    {
        $this->roomId = $roomId;
        $this->username = $username;
        $this->message = $message;
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
