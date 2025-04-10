<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public int $fromId;
    public int $toId;
    public string $action;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $id, int $fromId, int $toId, string $action)
    {
        $this->id = $id;
        $this->fromId = $fromId;
        $this->toId = $toId;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel('App.Member.' . $this->toId);
    }

    /**
     * Name of the event for WS to listen to
     *
     * When listening for an event with broadcastAs,
     * Listen should start with .
     *
     * eg .notification.created
     */
    public function broadcastAs()
    {
        return 'notification.created';
    }
}
