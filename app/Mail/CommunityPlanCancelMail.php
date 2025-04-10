<?php

namespace App\Mail;

use App\Models\CommunityPlan;
use Carbon\Carbon;

class CommunityPlanCancelMail extends SupportMail
{
    /**
     * @param CommunityPlan $plan
     */
    public function __construct(CommunityPlan $plan)
    {
        parent::__construct($plan->community->user);

        $this->id = 'community-plan-cancel';
        $this->with = [
            'userName' => $plan->community->user->name,
            'communityName' => $plan->community->name,
            'communityLogo' => $plan->community->logo,
            'communityUrl' => config('app.url') . '/' . $plan->community->url,
            'periodEnd' => Carbon::parse($plan->current_period_end)->locale($this->lang)->translatedFormat('l d M Y')
        ];
    }
}
