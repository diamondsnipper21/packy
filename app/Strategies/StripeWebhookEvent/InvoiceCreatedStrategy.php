<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Models\CommunityPlan;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\WebhookEvent;
use App\Services\LoggerService;
use App\Services\StripeService;
use Stripe\Invoice;

class InvoiceCreatedStrategy implements StripeWebhookEventStrategyInterface
{
    /**
     * Executes a strategy when an invoice is created on Stripe (= draft status).
     * When an invoice is created, we add the community name inside it.
     *
     * @param WebhookEvent $webhook The webhook event containing the necessary data for updating the invoice.
     * @return void
     */
    public function execute(WebhookEvent $webhook): void
    {
        $event = json_decode($webhook->body);

        $communityName = $this->getCommunityName($event->data->object->subscription);
        if ($communityName === null) {
            return;
        }

        $stripeService = new StripeService(config('payment.stripe.subscriptions_secret_key'));

        try {
            $invoice = $stripeService->retrieveInvoice($event->data->object->id);
            if (!$invoice || $invoice->status !== 'draft') {
                return;
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        try {
            $stripeService->updateInvoice(
                $event->data->object->id,
                strtoupper($communityName)
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @param string $subscriptionId
     * @return string|null
     */
    private function getCommunityName(string $subscriptionId): ?string
    {
        $communityName = null;

        $communityPlan = CommunityPlan::where(['st_subscription_id' => $subscriptionId])->first();
        if ($communityPlan) {
            $communityName = $communityPlan->community->name;
        } else {
            $memberSubscription = MemberSubscriptions::where(['stripe_subscription_id' => $subscriptionId])->first();
            if ($memberSubscription) {
                $communityName = $memberSubscription->community->name;
            }
        }

        return $communityName;
    }
}