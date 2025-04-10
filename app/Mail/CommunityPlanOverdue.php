<?php

namespace App\Mail;

use App\Models\CommunityPlan;
use App\Models\CommunityPlanReminder;
use App\Services\LoggerService;

class CommunityPlanOverdue extends SupportMail
{
    /**
     * @param CommunityPlan $plan
     */
    public function __construct(CommunityPlan $plan)
    {
        parent::__construct($plan->community->user);

        $this->id = 'community-plan-overdue';
        $this->with = [
            'userName' => $plan->community->user->name,
            'communityName' => $plan->community->name,
            'communityLogo' => $plan->community->logo,
            'communityUrl' => $plan->community->url
        ];

        try {
            $reminder = new CommunityPlanReminder();
            $reminder->community_id = $plan->community->id;
            $reminder->plan_id = $plan->id;
            $reminder->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
