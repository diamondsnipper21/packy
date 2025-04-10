<?php declare(strict_types=1);

namespace App\Services;

use App\Mail\CommunityPlanCancelMail;
use App\Mail\CommunityPlanOverdue;
use App\Mail\CommunityPlanRenewalReminderMail;
use App\Mail\CommunityReactivatedMail;
use App\Models\Community;
use App\Models\CommunityPlan;
use App\Models\CommunityPlanReminder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class PlanService extends Service
{
    private MemberService $memberService;

    /**
     * @param MemberService $memberService
     */
    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * @param CommunityPlan $communityPlan
     * @param string|null $reason
     * @param bool $sendEmail
     * @return JsonResponse
     */
    public function cancelPlan(CommunityPlan $communityPlan, string $reason = null, bool $sendEmail = true): JsonResponse
    {
        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));

        try {
            $subscription = $stripeService->retrieveSubscription($communityPlan->st_subscription_id);
            if ($subscription && $subscription->status !== CommunityPlan::STATUS_CANCELED) {
                $stripeService->cancelSubscription(
                    $communityPlan->st_subscription_id,
                    $reason
                );
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        try {
            $communityPlan->status = CommunityPlan::STATUS_CANCELED;
            $communityPlan->save();

            $communityPlan->community->status = Community::STATUS_INACTIVE;
            $communityPlan->community->save();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        if ($sendEmail === true) {
            try {
                Mail::to($communityPlan->community->user->email)->send(new CommunityPlanCancelMail($communityPlan));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }

        $this->memberService->checkIncubateurCommunityMembership($communityPlan->community->user_id);

        return response()->json(['success' => true]);
    }

    /**
     * The community plan has passed due : email every day + red bar
     *
     * @param CommunityPlan $communityPlan
     * @return JsonResponse
     */
    public function passedDuePlan(CommunityPlan $communityPlan): JsonResponse
    {
        try {
            if (!$communityPlan->passed_due) {
                $communityPlan->passed_due = Carbon::now();
            }
            $communityPlan->save();

            Mail::to($communityPlan->community->user->email)->send(new CommunityPlanOverdue($communityPlan));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        // more than 8 days -> we block members subscriptions + we block community content
        if ($communityPlan->passed_due) {
            $now = Carbon::now();
            $dateLimit = Carbon::parse($communityPlan->passed_due)->addDays(8);

            if ($now >= $dateLimit) {
                try {
                    $communityPlan->community->status = Community::STATUS_SUSPENDED;
                    $communityPlan->community->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * @param CommunityPlan $communityPlan
     * @return JsonResponse
     */
    public function reactivatePlan(CommunityPlan $communityPlan): JsonResponse
    {
        try {
            $communityPlan->passed_due = null;
            $communityPlan->save();

            $communityPlan->community->status = Community::STATUS_PUBLISHED;
            $communityPlan->community->save();

            Mail::to($communityPlan->community->user->email)->send(new CommunityReactivatedMail($communityPlan->community));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Reminder is send only once a day
     *
     * @param CommunityPlan $communityPlan
     * @return void
     */
    public function sendReminder(CommunityPlan $communityPlan): void
    {
        $lastReminder = CommunityPlanReminder::where(['plan_id' => $communityPlan->id])
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$lastReminder) {
            $this->passedDuePlan($communityPlan);
            return;
        }

        $limitDate = Carbon::now()->subDays(1);
        $lastReminderDate = Carbon::parse($lastReminder->created_at);

        if ($limitDate > $lastReminderDate) {
            $this->passedDuePlan($communityPlan);
        }
    }

    /**
     * Get all plans with a close renew date and send a email reminder to members
     * We send an email 3 days before the plan renews.
     *
     * @param int $days
     * @return void
     */
    public function sendReminderEmails(int $days): void
    {
        foreach (CommunityPlan::getNextBillingPlans($days) as $plan) {
            try {
                Mail::to($plan->community->user->email)->send(new CommunityPlanRenewalReminderMail($plan));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }
}