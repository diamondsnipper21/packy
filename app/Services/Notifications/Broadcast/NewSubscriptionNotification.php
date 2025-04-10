<?php

namespace App\Services\Notifications\Broadcast;

use App\Models\User;

class NewSubscriptionNotification extends BroadcastNotificationService
{
    /**
     * @param int $userId
     * @param User $user
     */
    public function __construct(int $userId, User $user)
    {
        parent::__construct();

        $this->event = 'new-subscription';
        $this->channels = 'user-' . $userId;
        $this->message = sprintf(
            __('%s subscribed to Packie'),
            ucfirst(strtolower($user->name))
        );

        return $this->trigger();
    }
}
