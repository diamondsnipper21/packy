<?php

namespace App\Mail\Subscriptions;

use App\Mail\SupportMail;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use Carbon\Carbon;

class SubscriptionRenewalReminderMail extends SupportMail
{
    /**
     * @param MemberSubscriptions $subscription
     */
    public function __construct(MemberSubscriptions $subscription)
    {
        parent::__construct($subscription->member->user);

        $this->id = 'subscription-renewal-reminder';
        $this->with = [
            'memberName' => $subscription->member->user->name,
            'communityName' => $subscription->community->name,
            'communityLogo' => $subscription->community->logo,
            'nextBillingDate' => Carbon::parse($subscription->next_billing_at)->locale($this->lang)->translatedFormat('l d M Y'),
            'communityUrl' => config('app.url') . '/' . $subscription->community->url,
            'card' => [
                'brand' => $subscription->payment_method->card_brand,
                'last4' => $subscription->payment_method->last4
            ],
            'subscription' => [
                'amount' => $subscription->amount,
                'currency' => '€',
                'period' => $subscription->period,
            ]
        ];
    }
}
