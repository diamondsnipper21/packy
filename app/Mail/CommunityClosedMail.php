<?php

namespace App\Mail;

use App\Models\CommunityPlan;

class CommunityClosedMail extends SupportMail
{
    /**
     * @param CommunityPlan $plan
     */
    public function __construct(CommunityPlan $plan)
    {
        parent::__construct($plan->community->user);

        $this->id = 'community-closed';
        $this->with = [
            'userName' => $plan->community->user->name,
            'communityName' => $plan->community->name,
            'communityLogo' => $plan->community->logo,
            'communityUrl' => config('app.url') . '/' . $plan->community->url,
        ];
    }
}
