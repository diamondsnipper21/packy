<?php

namespace App\Enum;

/**
 * @doc https://docs.stripe.com/api/events/types
 */
class StripeWebhookEventsEnum
{
    /** Occurs whenever a customer’s subscription is paused. Only applies when subscriptions enter status=paused, not when payment collection is paused. */
    public const EVENT_CUSTOMER_SUBSCRIPTION_PAUSED = 'customer.subscription.paused';
    /** Occurs whenever a customer’s subscription is no longer paused. Only applies when a status=paused subscription is resumed, not when payment collection is resumed. */
    public const EVENT_CUSTOMER_SUBSCRIPTION_RESUMED = 'customer.subscription.resumed';
    /** Occurs whenever a subscription changes (e.g., switching from one plan to another, or changing the status from trial to active). */
    public const EVENT_CUSTOMER_SUBSCRIPTION_UPDATED = 'customer.subscription.updated';
    /** Occurs whenever a customer’s subscription ends. */
    public const EVENT_CUSTOMER_SUBSCRIPTION_DELETED = 'customer.subscription.deleted';
    public const EVENT_ACCOUNT_UPDATED = 'account.updated';
    public const EVENT_INVOICE_CREATED = 'invoice.created';
    public const EVENT_INVOICE_PAID = 'invoice.paid';
    public const EVENT_INVOICE_PAYMENT_FAILED = 'invoice.payment_failed';
    public const EVENT_TRANSFER_REVERSED = 'transfer.reversed';
}
