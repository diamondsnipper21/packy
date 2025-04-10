<?php

namespace App\Mail;

use App\Models\CommunityPlan;
use Carbon\Carbon;

class CommunityTrialPlanReminderMail extends SupportMail
{
    /**
     * @param CommunityPlan $plan
     */
    public function __construct(CommunityPlan $plan)
    {
        parent::__construct($plan->community->user);

        $currencies = [
            'usd' => '$',
            'eur' => '€'
        ];

        $this->id = 'community-trial-plan-reminder';
        $this->with = [
            'userName' => $plan->community->user->name,
            'communityName' => $plan->community->name,
            'communityLogo' => $plan->community->logo,
            'communityUrl' => config('app.url') . '/' . $plan->community->url,
            'periodEnd' => Carbon::parse($plan->current_period_end)->locale($this->lang)->translatedFormat('l d M Y'),
            'currency' => $currencies[$plan->currency],
            'amount' => $plan->amount / 100,
            'cardBrand' => $plan->payment_method->card_brand,
            'last4' => $plan->payment_method->last4
        ];
    }
}
