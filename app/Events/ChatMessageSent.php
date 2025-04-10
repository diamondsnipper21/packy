<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public int $fromId;
    public int $toId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $id, int $fromId, int $toId)
    {
        $this->id = $id;
        $this->fromId = $fromId;
        $this->toId = $toId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel('App.User.' . $this->toId);
    }

    /**
     * Name of the event for WS to listen to
     *
     * When listening for an event with broadcastAs,
     * Listen should start with .
     *
     * eg .chat.message.sent
     */
    public function broadcastAs()
    {
        return 'chat.message.sent';
    }
}
