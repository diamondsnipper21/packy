<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Enum\MemberSubscriptionTransactionStatusEnum;
use App\Mail\CommunityPlanOverdue;
use App\Mail\Subscriptions\SubscriptionCancelled;
use App\Mail\Subscriptions\SubscriptionOverdue;
use App\Models\CommunityPlan;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\UserPlansTransactions;
use App\Models\WebhookEvent;
use App\Services\LoggerService;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;

class InvoicePaymentFailedStrategy implements StripeWebhookEventStrategyInterface
{
    /**
     * Executes a strategy when a payment fails on Stripe.
     *
     * @doc https://docs.stripe.com/billing/subscriptions/overview#settings
     * @param WebhookEvent $webhook
     * @return void
     */
    public function execute(WebhookEvent $webhook): void
    {
        $event = json_decode($webhook->body);

        $memberSubscription = MemberSubscriptions::where(['stripe_subscription_id' => $event->data->object->subscription])->first();
        if ($memberSubscription) {
            $this->handleMemberSubscription($event, $memberSubscription);
        }

        $communityPlan = CommunityPlan::where(['st_subscription_id' => $event->data->object->subscription])->first();
        if (!$communityPlan) {
            $this->handleUserPlan($event, $communityPlan);
        }
    }

    /**
     * @param $event
     * @param MemberSubscriptions $memberSubscription
     * @return void
     */
    private function handleMemberSubscription($event, MemberSubscriptions $memberSubscription): void
    {
        $stripeService = new StripeService();

        $status = MemberSubscriptions::STATUS_OVERDUE;
        $failedAttempts = $memberSubscription->failed_attempts + 1;
        if ($failedAttempts >= 3) {
            $status = MemberSubscriptions::STATUS_CANCELLED;
        }

        $this->saveMemberSubscriptionTransaction($event->data->object, $memberSubscription, $stripeService);

        try {
            $memberSubscription->status = $status;
            $memberSubscription->failed_attempts = $failedAttempts;
            $memberSubscription->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }

        if ($status === MemberSubscriptions::STATUS_OVERDUE) {
            try {
                Mail::to($memberSubscription->user->email)
                    ->send(new SubscriptionOverdue($memberSubscription));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return;
            }
        }

        if ($status === MemberSubscriptions::STATUS_CANCELLED) {
            try {
                $stripeService->cancelSubscription(
                    $memberSubscription->stripe_subscription_id,
                    'Payment failed'
                );

                Mail::to($memberSubscription->user->email)
                    ->send(new SubscriptionCancelled($memberSubscription));
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return;
            }
        }
    }

    /**
     * @param $event
     * @param CommunityPlan $communityPlan
     * @return void
     */
    private function handleUserPlan($event, CommunityPlan $communityPlan): void
    {
        try {
            $communityPlan->status = CommunityPlan::STATUS_PAST_DUE;
            $communityPlan->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }

        try {
            Mail::to($communityPlan->community->user->email)
                ->send(new CommunityPlanOverdue($communityPlan));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }

        $this->saveUserPlanTransaction($event->data->object, $communityPlan);
    }

    /**
     * @param $event
     * @param CommunityPlan $communityPlan
     * @return void
     */
    private function saveUserPlanTransaction($event, CommunityPlan $communityPlan): void
    {
        $taxRate = 0;
        if ($event->subtotal_excluding_tax > 0) {
            $taxRate = $event->tax / $event->subtotal_excluding_tax;
        }

        try {
            $transaction = new UserPlansTransactions();
            $transaction->charge = $event->charge;
            $transaction->community_id = $communityPlan->community_id;
            $transaction->plan_id = $communityPlan->id;
            $transaction->payment_method_id = $communityPlan->payment_method_id;
            $transaction->number = date('ym') . strtoupper(uniqid());
            $transaction->invoice = $event->id;
            $transaction->amount = $event->total;
            $transaction->tax = $event->tax;
            $transaction->tax_rate = $taxRate;
            $transaction->currency = strtoupper($event->currency);
            $transaction->country = $event->customer_address->country;
            $transaction->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }
    }

    /**
     * @param $event
     * @param MemberSubscriptions $memberSubscription
     * @param StripeService $stripeService
     * @return void
     */
    private function saveMemberSubscriptionTransaction(
        $event,
        MemberSubscriptions $memberSubscription,
        StripeService $stripeService
    ): void
    {
        $taxRate = 0;
        if ($event->subtotal_excluding_tax > 0) {
            $taxRate = $event->tax / $event->subtotal_excluding_tax;
        }

        try {
            $transaction = MemberSubscriptionsTransactions::firstOrNew([
                'charge' => $event->charge
            ]);
            $transaction->invoice = $event->id;
            $transaction->user_id = $memberSubscription->user_id;
            $transaction->community_id = $memberSubscription->community_id;
            $transaction->subscription_id = $memberSubscription->id;
            $transaction->payment_method_id = $memberSubscription->payment_method_id;
            $transaction->number = date('ym') . strtoupper(uniqid());
            $transaction->amount = $event->total;
            $transaction->tax = $event->tax;
            $transaction->tax_rate = $taxRate;
            $transaction->fees = $stripeService->calculatePackieFees($event->total);
            $transaction->currency = strtoupper($event->currency);
            $transaction->country = $event->customer_address->country;
            $transaction->status = MemberSubscriptionTransactionStatusEnum::STATUS_OVERDUE;
            $transaction->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return;
        }
    }
}