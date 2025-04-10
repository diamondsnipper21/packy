<?php

namespace App\Strategies\StripeWebhookEvent;

use App\Models\WebhookEvent;

/**
 * @doc https://docs.stripe.com/webhooks
 */
interface StripeWebhookEventStrategyInterface
{
    public function execute(WebhookEvent $webhook);
}