<?php

return [
    'stripe' => [
        'subscriptions_public_key' => env('STRIPE_PUBLIC_KEY_SUBSCRIPTIONS', ''),
        'subscriptions_secret_key' => env('STRIPE_SECRET_KEY_SUBSCRIPTIONS', ''),

        'marketplace_public_key' => env('STRIPE_PUBLIC_KEY_MARKETPLACE', ''),
        'marketplace_secret_key' => env('STRIPE_SECRET_KEY_MARKETPLACE', ''),

        'basic_plan_id' => env('STRIPE_BASIC_PLAN_ID', ''),
        'tax_rate_id' => env('STRIPE_TAX_RATE_ID', ''),
        'trial_days' => env('STRIPE_TRIAL_DAYS', 14),

        'webhook_key_subscriptions' => env('STRIPE_WEBHOOK_KEY_SUBSCRIPTIONS', ''),
        'webhook_key_marketplace' => env('STRIPE_WEBHOOK_KEY_MARKETPLACE', '')
    ],
    'currency' => env('APP_CURRENCY', 'EUR'),
    'payout_delay' => env('APP_PAYOUT_DAYS', 15),
];