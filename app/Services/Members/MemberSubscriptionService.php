<?php

namespace App\Services\Members;

use App\Mail\Subscriptions\SubscriptionRenewalReminderMail;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsCancelRequests;
use App\Services\LoggerService;
use App\Services\MemberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MemberSubscriptionService
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function cancel(Request $request): void
    {
        try {
            MemberSubscriptions::where(['id' => $request->id])->update([
                'status' => MemberSubscriptions::STATUS_CANCELLED,
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            throw new \Exception();
        }
    }

    /**
     * Get all subscriptions with a close renew date and send a email reminder to members
     * We send an email 3 then 2 days before the subscription renews.
     *
     * @param int $days
     * @return void
     */
    public function sendReminderEmails(int $days): void
    {
        foreach (MemberSubscriptions::getNextBillingSubscriptions($days) as $subscription) {
            try {
                Mail::to($subscription->user->email)->send(new SubscriptionRenewalReminderMail($subscription));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * @return void
     */
    public function treatCancelsRequests(): void
    {
        $dateLimit = Carbon::now()->addDays(1)
                ->format('Y-m-d') . ' 23:59:59';

        $cancelRequests = MemberSubscriptionsCancelRequests::all();
        foreach ($cancelRequests as $cancelRequest) {
            if ($cancelRequest->subscription->next_billing_at > $dateLimit) {
                continue;
            }

            try {
                $this->memberService->removeMemberFromCommunity(
                    $cancelRequest->community_id,
                    $cancelRequest->member_id
                );

                $cancelRequest->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }

    /*
    private function treatSubscription(MemberSubscriptions $memberSubscription, array $stripeData): void
    {
        $payment = null;

        if ($payment === false) {
            try {
                //
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                throw new \Exception();
            }
        }

        // we update the next billing date
        try {
            $date = Carbon::parse($memberSubscription->next_billing_at);
            if ($payment === true) {
                if ($memberSubscription->period === 'monthly') {
                    $date->addMonths(1);
                }
                if ($memberSubscription->period === 'yearly') {
                    $date->addYears(1);
                }
            }

            try {
                MemberSubscriptions::where(['id' => $memberSubscription->id])->update([
                    'next_billing_at' => $date->format('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                throw new \Exception();
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            throw new \Exception();
        }
    }
    */
}
