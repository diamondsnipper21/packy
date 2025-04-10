<?php

namespace App\Services\Notifications\Broadcast;

use App\Services\LoggerService;
use Pusher\Pusher;

abstract class BroadcastNotificationService
{
    private Pusher $broadcastService;
    protected string $channels;
    protected string $event;
    protected string $message;

    public function __construct()
    {
        $this->broadcastService = $this->getInstance();
    }

    /**
     * @return Pusher|null
     */
    private function getInstance(): ?Pusher
    {
        try {
            return $this->broadcastService = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER')]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function trigger(): bool
    {
        try {
            $this->broadcastService->trigger(
                $this->channels,
                $this->event, [
                    'message' => $this->message
                ]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return false;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return false;
        }

        return true;
    }
}
