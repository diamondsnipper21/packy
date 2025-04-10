<?php

namespace App\Mail;

use App\Models\CommunityPlan;
use Carbon\Carbon;

class CommunityPlanRenewalReminderMail extends SupportMail
{
    /**
     * @param CommunityPlan $plan
     */
    public function __construct(CommunityPlan $plan)
    {
        parent::__construct($plan->community->user);

        $this->id = 'community-plan-renewal-reminder';
        $this->with = [
            'userName' => $plan->community->user->name,
            'communityName' => $plan->community->name,
            'communityLogo' => $plan->community->logo,
            'nextBillingDate' => Carbon::parse($plan->current_period_end)->locale($this->lang)->translatedFormat('l d M Y'),
            'communityUrl' => $plan->community->url,
            'card' => [
                'brand' => $plan->payment_method->card_brand,
                'last4' => $plan->payment_method->last4
            ],
            'plan' => [
                'amount' => $plan->amount / 100,
                'currency' => '€'
            ]
        ];
    }
}
