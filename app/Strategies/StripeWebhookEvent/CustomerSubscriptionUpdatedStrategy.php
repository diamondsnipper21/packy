<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Helpers\DatetimeHelper;
use App\Models\CommunityPlan;
use App\Models\WebhookEvent;
use App\Services\LoggerService;
use App\Services\PlanService;

class CustomerSubscriptionUpdatedStrategy implements StripeWebhookEventStrategyInterface
{
    private PlanService $planService;

    /**
     * @param PlanService $planService
     */
    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * @param WebhookEvent $webhook
     * @return void
     * @doc https://dashboard.stripe.com/settings/billing/automatic
     */
    public function execute(WebhookEvent $webhook): void
    {
        $event = json_decode($webhook->body);

        $communityPlan = CommunityPlan::where(['st_subscription_id' => $event->data->object->id])->first();
        if (!$communityPlan) {
            return;
        }

        $this->updateCommunityPlan($communityPlan, $event->data->object);

        switch ($event->data->object->status)
        {
            case CommunityPlan::STATUS_PAST_DUE:
                $this->planService->passedDuePlan($communityPlan);
                break;

            case CommunityPlan::STATUS_CANCELED:
                $this->planService->cancelPlan($communityPlan, 'Canceled from Stripe');
                break;

            case CommunityPlan::STATUS_ACTIVE:
                $this->planService->reactivatePlan($communityPlan);
                break;
        }
    }

    /**
     * @param CommunityPlan $communityPlan
     * @param $event
     * @return void
     */
    private function updateCommunityPlan(CommunityPlan $communityPlan, $event): void
    {
        try {
            $communityPlan->update([
                'status' => $event->status,
                'current_period_start' => $event->current_period_start ? DatetimeHelper::timestampToDate($event->current_period_start) : NULL,
                'current_period_end' => $event->current_period_end ? DatetimeHelper::timestampToDate($event->current_period_end) : NULL,
                'trial_start' => $event->trial_start ? DatetimeHelper::timestampToDate($event->trial_start) : NULL,
                'trial_end' => $event->trial_end ? DatetimeHelper::timestampToDate($event->trial_end) : NULL,
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}