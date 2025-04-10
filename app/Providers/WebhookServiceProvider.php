<?php

namespace App\Providers;

use App\Enum\StripeWebhookEventsEnum;
use App\Strategies\StripeWebhookEvent\AccountUpdatedStrategy;
use App\Strategies\StripeWebhookEvent\CustomerSubscriptionDeletedStrategy;
use App\Strategies\StripeWebhookEvent\CustomerSubscriptionUpdatedStrategy;
use App\Strategies\StripeWebhookEvent\InvoiceCreatedStrategy;
use App\Strategies\StripeWebhookEvent\InvoicePaidStrategy;
use App\Strategies\StripeWebhookEvent\InvoicePaymentFailedStrategy;
use App\Strategies\StripeWebhookEvent\TransferReversedStrategy;
use Illuminate\Support\ServiceProvider;
use App\Services\WebhookService;

class WebhookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService([
                StripeWebhookEventsEnum::EVENT_CUSTOMER_SUBSCRIPTION_RESUMED => $app->make(CustomerSubscriptionUpdatedStrategy::class),
                StripeWebhookEventsEnum::EVENT_CUSTOMER_SUBSCRIPTION_PAUSED => $app->make(CustomerSubscriptionUpdatedStrategy::class),
                StripeWebhookEventsEnum::EVENT_CUSTOMER_SUBSCRIPTION_UPDATED => $app->make(CustomerSubscriptionUpdatedStrategy::class),
                StripeWebhookEventsEnum::EVENT_CUSTOMER_SUBSCRIPTION_DELETED => $app->make(CustomerSubscriptionDeletedStrategy::class),
                StripeWebhookEventsEnum::EVENT_ACCOUNT_UPDATED => $app->make(AccountUpdatedStrategy::class),
                StripeWebhookEventsEnum::EVENT_INVOICE_CREATED => $app->make(InvoiceCreatedStrategy::class),
                StripeWebhookEventsEnum::EVENT_INVOICE_PAID => $app->make(InvoicePaidStrategy::class),
                StripeWebhookEventsEnum::EVENT_INVOICE_PAYMENT_FAILED => $app->make(InvoicePaymentFailedStrategy::class),
                StripeWebhookEventsEnum::EVENT_TRANSFER_REVERSED => $app->make(TransferReversedStrategy::class)
            ]);
        });
    }
}