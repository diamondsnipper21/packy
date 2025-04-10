<?php

namespace App\Services\Notifications\Broadcast;

use App\Helpers\CurrencyHelper;
use App\Models\CommunityNotificationsSettings;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\Notification;

class NewPaymentNotification extends BroadcastNotificationService
{
    /**
     * @param MemberSubscriptionsTransactions $transaction
     */
    public function __construct(MemberSubscriptionsTransactions $transaction)
    {
        parent::__construct();

        $this->event = 'new-payment';
        $this->channels = env('APP_NAME') . '-community-' . $transaction->subscription->community->id;
        $this->message = sprintf(
            __('%s from %s'),
            CurrencyHelper::format($transaction->amount, $transaction->currency),
            ucfirst(strtolower($transaction->subscription->user->firstname))
        );

        $hasNotification = CommunityNotificationsSettings::where([
            'community_id' => $transaction->subscription->community->id,
            'type' => Notification::OT_NEW_PAYMENT
        ])->first();

        if (!$hasNotification) {
            return false;
        }

        return $this->trigger();
    }
}
