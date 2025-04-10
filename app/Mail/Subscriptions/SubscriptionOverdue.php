<?php

namespace App\Mail\Subscriptions;

use App\Mail\SupportMail;
use App\Models\Members\Subscriptions\MemberSubscriptions;

class SubscriptionOverdue extends SupportMail
{
    /**
     * @param MemberSubscriptions $subscription
     */
    public function __construct(MemberSubscriptions $subscription)
    {
        parent::__construct($subscription->member->user);

        $this->id = 'subscription-overdue';
        $this->with = [
            'memberName' => $subscription->member->user->name,
            'communityName' => $subscription->community->name,
            'communityLogo' => $subscription->community->logo,
            'communityUrl' => config('app.url') . '/' . $subscription->community->url,
            'card' => [
                'brand' => $subscription->payment_method->card_brand,
                'last4' => $subscription->payment_method->last4
            ],
            'subscription' => [
                'amount' => $subscription->amount,
                'currency' => '€',
            ]
        ];
    }
}