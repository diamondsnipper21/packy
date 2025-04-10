<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Mail\CommunityClosedMail;
use App\Models\Community;
use App\Models\CommunityPlan;
use App\Models\WebhookEvent;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\PlanService;
use Illuminate\Support\Facades\Mail;

class CustomerSubscriptionDeletedStrategy implements StripeWebhookEventStrategyInterface
{
    private MemberService $memberService;
    private PlanService $planService;

    /**
     * @param MemberService $memberService
     * @param PlanService $planService
     */
    public function __construct(MemberService $memberService, PlanService $planService)
    {
        $this->memberService = $memberService;
        $this->planService = $planService;
    }

    /**
     * @param WebhookEvent $webhook
     * @return void
     */
    public function execute(WebhookEvent $webhook): void
    {
        $customerSubscriptionUpdatedStrategy = new CustomerSubscriptionUpdatedStrategy($this->planService);
        $customerSubscriptionUpdatedStrategy->execute($webhook);

        try {
            $plans = CommunityPlan::whereIn('status', [CommunityPlan::STATUS_CANCELED, CommunityPlan::STATUS_UNPAID])
                ->where('current_period_end', '<', strtotime('now'))
                ->whereHas('community', function ($query) {
                    $query->where('status', '!=', Community::STATUS_INACTIVE);
                })
                ->with('community')
                ->get();

            foreach ($plans as $plan) {
                $plan->community->status = Community::STATUS_INACTIVE;
                $plan->community->save();

                $this->memberService->checkIncubateurCommunityMembership($plan->community->user->id);
                Mail::to($plan->community->user->email)->send(new CommunityClosedMail($plan));
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}