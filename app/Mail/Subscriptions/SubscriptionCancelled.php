<?php

namespace App\Mail\Subscriptions;

use App\Mail\SupportMail;
use App\Models\Members\Subscriptions\MemberSubscriptions;

class SubscriptionCancelled extends SupportMail
{
    /**
     * @param MemberSubscriptions $subscription
     */
    public function __construct(MemberSubscriptions $subscription)
    {
        parent::__construct($subscription->member->user);

        $this->id = 'subscription-cancelled';
        $this->with = [
            'memberName' => $subscription->member->user->name,
            'communityName' => $subscription->community->name,
            'communityLogo' => $subscription->community->logo,
            'communityUrl' => config('app.url') . '/' . $subscription->community->url,
            'card' => $subscription->payment_method
        ];
    }
}
