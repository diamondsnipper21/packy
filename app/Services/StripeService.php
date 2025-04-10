<?php

namespace App\Services;

use App\Enum\CurrencyEnum;
use App\Enum\MemberSubscriptionTransactionStatusEnum;
use App\Helpers\DatetimeHelper;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPlan;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\PaymentMethodMarketplace;
use App\Models\StripeAccount;
use App\Models\StripeAccountLink;
use App\Models\User;
use App\Models\UserPaymentMethod;
use App\Models\VatRate;
use Stripe\Account;
use Stripe\Invoice;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Price;
use Stripe\Product;
use Stripe\SearchResult as StripeSearchResult;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

class StripeService extends Service
{
    private StripeClient $stripe;

    private ?string $connectedAccount = null;

    /**
     * @param string|null $secretKey
     */
    public function __construct(string $secretKey = null, string $connectedAccount = null)
    {
        if ($secretKey === null) {
            $this->stripe = new StripeClient(config('payment.stripe.marketplace_secret_key'));
        } else {
            $this->stripe = new StripeClient($secretKey);
        }

        // connectedAccount not null = API calls are made on this connected account
        $this->connectedAccount = $connectedAccount;
    }

    /**
     * @doc https://docs.stripe.com/api/customers/create
     *
     * @param string $email
     * @param string $name
     * @param string|null $country
     * @param string|null $ip
     * @param string|null $postalCode
     * @return \Stripe\Customer|null
     */
    public function createCustomer(
        string $email,
        string $name,
        string $country = null,
        string $ip = null,
        string $postalCode = null,
        string $paymentMethod = null
    ): ?\Stripe\Customer
    {
        try {
            $customerData = [
                'name' => $name,
                'email' => $email,
                'address' => [
                    'country' => $country ?? 'FR'
                ]
            ];

            if ($postalCode) {
                $customerData['address']['postal_code'] = $postalCode;
            }
            if ($ip) {
                $customerData['tax']['ip_address'] = $ip;
            }
            if ($paymentMethod) {
                $customerData['payment_method'] = $paymentMethod;
            }

            $customer = $this->stripe->customers->create($customerData);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $customer;
    }

    /**
     * @doc https://docs.stripe.com/api/payment_methods/create
     *
     * @param string $token
     * @return StripePaymentMethod|null
     */
    public function createPaymentMethod(string $token): ?StripePaymentMethod
    {
        try {
            return $this->stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $token
                ],
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * Attach Stripe payment method to community member using token or payment method id
     *
     * @param User $user
     * @param array $data
     * @return PaymentMethodMarketplace|null
     */
    public function attachMemberPaymentMethod(User $user, array $data): ?PaymentMethodMarketplace
    {
        $this->stripe->paymentMethods->attach(
            $data['id'],
            ['customer' => $user->stripe_customer_id_marketplace]
        );

        try {
            $paymentMethod = PaymentMethodMarketplace::firstOrNew([
                'user_id' => $user->id,
                'payment_method_id' => $data['id'],
                'last4' => $data['card']['last4'],
                'card_brand' => $data['card']['brand'],
            ]);
            $paymentMethod->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $paymentMethod;
    }

    /**
     * Attach Stripe payment method to packie user using token or payment method id
     *
     * @param User $user
     * @param array $data
     * @return UserPaymentMethod|null
     */
    public function attachUserPaymentMethod(User $user, array $data): ?UserPaymentMethod
    {
        try {
            $this->stripe->paymentMethods->attach(
                $data['id'],
                ['customer' => $user->stripe_customer_id]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            \Log::error(['attachUserPaymentMethod Exception', [$user->id, $data]]);
            return null;
        }

        try {
            $paymentMethod = UserPaymentMethod::firstOrNew([
                'user_id' => $user->id,
                'payment_method_id' => $data['id'],
                'last4' => $data['card']['last4'],
                'card_brand' => $data['card']['brand'],
            ]);
            $paymentMethod->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $paymentMethod;
    }

    /**
     * @todo - move to PlanService
     *
     * Create subscription for community
     *
     * @param int $userId
     * @param UserPaymentMethod $paymentMethod
     * @param bool $withTrial
     * @param Community|null $community
     * @return array|null
     */
    public function createSubscriptionPlan(int $userId, UserPaymentMethod $paymentMethod, bool $withTrial, Community $community = null): ?array
    {
        $user = User::where(['id' => $userId])->first();
        if (!$user) {
            return null;
        }

        $item = [
            'price' => config('payment.stripe.basic_plan_id'),
            'tax_rates' => [config('payment.stripe.tax_rate_id')]
        ];

        $subscriptionData = [
            'customer' => $user->stripe_customer_id,
            'automatic_tax' => [
                'enabled' => false
            ],
            'items' => [$item],
            'default_payment_method' => $paymentMethod->payment_method_id,
            'trial_period_days' => $withTrial ? config('payment.stripe.trial_days') : 0,
            'metadata' => [
                'user_id' => $user->id
            ],
            'cancel_at_period_end' => true
        ];

        if ($community) {
            $subscriptionData['description'] = $community->name;
            $subscriptionData['metadata']['community_id'] = $community->id;
        }

        if (!$withTrial) {
            $subscriptionData['collection_method'] = 'charge_automatically';
            $subscriptionData['payment_behavior'] = 'error_if_incomplete';
            $subscriptionData['cancel_at_period_end'] = 'false';
        }

        \Log::info(['createSubscriptionPlan', json_encode($subscriptionData)]);

        try {
            $subscription = $this->stripe->subscriptions->create($subscriptionData);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return [
            'subscription' => $subscription,
            'pending_setup_intent' => $subscription->pending_setup_intent ?? NULL,
            'latest_invoice' => $subscription->latest_invoice ?? NULL
        ];
    }

    /**
     * @param string $stripeSubscriptionId
     * @param string $paymentMethodId
     * @return StripeSubscription|null
     */
    public function updateSubscription(string $stripeSubscriptionId, string $paymentMethodId): ?StripeSubscription
    {
        try {
            return $this->stripe->subscriptions->update(
                $stripeSubscriptionId,
                ['default_payment_method' => $paymentMethodId]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $stripeSubscriptionId
     * @param array $arrayUpdate
     * @return StripeSubscription|null
     */
    public function updateSubscriptionDescription(string $stripeSubscriptionId, array $arrayUpdate): ?StripeSubscription
    {
        try {
            return $this->stripe->subscriptions->update(
                $stripeSubscriptionId,
                $arrayUpdate
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $stripeCustomerId
     * @param array $arrayUpdate
     * @return \Stripe\Customer|null
     */
    public function updateCustomer(
        string $stripeCustomerId,
        array $arrayUpdate
    ): ?\Stripe\Customer
    {
        try {
            return $this->stripe->customers->update($stripeCustomerId, $arrayUpdate);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * Get stripe subscription details
     *
     * @param string $subscriptionId
     * @return StripeSubscription|null
     */
    public function getSubscription(string $subscriptionId): ?StripeSubscription
    {
        try {
            return $this->stripe->subscriptions->retrieve($subscriptionId, []);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $subscriptionId
     * @param string|null $reason
     * @param bool $immediately
     * @return StripeSubscription|null
     */
    public function cancelSubscription(
        string $subscriptionId,
        string $reason = null,
        bool $immediately = true
    ): ?StripeSubscription
    {
        try {
            if ($immediately === true) {
                MemberSubscriptions::where([
                    'stripe_subscription_id' => $subscriptionId
                ])->update([
                    'status' => MemberSubscriptions::STATUS_CANCELLED
                ]);

                return $this->stripe->subscriptions->cancel(
                    $subscriptionId, [
                        'cancellation_details' => [
                            'comment' => $reason ?? 'No specified reason'
                        ]
                    ]
                );
            } else {
                return $this->stripe->subscriptions->update(
                    $subscriptionId,
                    ['cancel_at_period_end' => true]
                );
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $subscriptionId
     * @return StripeSubscription|null
     */
    public function reactivateSubscription(string $subscriptionId): ?StripeSubscription
    {
        try {
            return $this->stripe->subscriptions->update(
                $subscriptionId,
                ['cancel_at_period_end' => false]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * Get stripe invoices by subscription
     *
     * @param CommunityPlan $plan
     * @param int $limit
     * @param int $page
     * 
     * @return StripeSearchResult
     */
    public function getInvoicesBySubscription(CommunityPlan $plan, int $limit, int $page): StripeSearchResult
    {
        $condition = [
            'query' => 'subscription:"' . $plan->st_subscription_id . '"',
            'limit' => $limit
        ];
        if (!empty($page)) {
            $condition['page'] = $page;
        }

        $result = $this->stripe->invoices->search($condition);
        foreach ($result->data as $key => $item) {
            $result->data[$key] = [
                'status' => $item->status,
                'amount_paid' => $item->amount_paid,
                'created' => $item->created,
                'customer_email' => $item->customer_email,
                'customer_name' => $item->customer_name,
                'due_date' => $item->due_date,
                'number' => $item->number,
                'id' => $item->id,
                'currency' => $item->currency,
            ];
        }
        return $result;
    }

    /**
     * @param Community $community
     * @return Product|null
     */
    public function createProduct(Community $community): ?Product
    {
        try {
            return $this->stripe->products->create([
                'name' => 'Packie subscription plan (#' . $community->id . ')',
                'description' => 'Subscription plan to community "' . $community->name . '"',
                'statement_descriptor' => 'Packie sub. ' . date('m-d')
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param int|null $userId
     * @return StripeAccount|null
     */
    public function createAccount(int $userId = null): ?StripeAccount
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        try {
            $create = $this->stripe->accounts->create([
                'email' => $user->email,
                'controller' => [
                    'fees' => ['payer' => 'application'],
                    'losses' => ['payments' => 'application'],
                    'stripe_dashboard' => ['type' => 'express'],
                ]
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        try {
            $stripeAccount = new StripeAccount();
            $stripeAccount->user_id = $userId;
            $stripeAccount->account_id = $create->id;
            $stripeAccount->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $stripeAccount;
    }

    /**
     * @param float $amount
     * @param string $interval
     * @param string $stripeProductId
     * @param bool $active
     * @return Price|null
     */
    public function createPrice(float $amount, string $interval, string $stripeProductId, bool $active = false): ?Price
    {
        try {
            return $this->stripe->prices->create([
                'currency' => 'eur',
                'unit_amount' => round($amount * 100, 0),
                'recurring' => ['interval' => $interval],
                'product' => $stripeProductId,
                'active' => $active
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $accountId
     * @return Account|null
     */
    public function deleteAccount(string $accountId): ?Account
    {
        try {
            $delete = $this->stripe->accounts->delete($accountId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $delete;
    }

    /***
     * @param int|null $userId
     * @return Account|null
     */
    public function retrieveAccount(int $userId = null): ?Account
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        $stripeAccount = StripeAccount::where(['user_id' => $userId])->first();
        if (!$stripeAccount) {
            return null;
        }

        try {
            $account = $this->stripe->accounts->retrieve($stripeAccount->account_id, []);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $account;
    }

    /**
     * @param float $amount
     * @param string $stripeProductId
     * @param bool $active
     * @return Price|null
     */
    public function createMonthlyPrice(float $amount, string $stripeProductId, bool $active = false): ?Price
    {
        return $this->createPrice($amount, 'month', $stripeProductId, $active);
    }

    /**
     * @param float $amount
     * @param string $stripeProductId
     * @param bool $active
     * @return Price|null
     */
    public function createYearlyPrice(float $amount, string $stripeProductId, bool $active = false): ?Price
    {
        return $this->createPrice($amount, 'year', $stripeProductId, $active);
    }

    /**
     * @param string $priceId
     * @param bool $active
     * @return Price|null
     */
    public function updatePrice(string $priceId, bool $active = false): ?Price
    {
        try {
            return $this->stripe->prices->update($priceId, [
                'active' => $active
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function deleteAccountLinks(int $userId = null): void
    {
        if ($userId === null) {
            $userId = session('user_id');
        }

        try {
            StripeAccountLink::where(['user_id' => $userId])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @doc https://docs.stripe.com/api/account_links
     * @param StripeAccount $stripeAccount
     * @return StripeAccountLink|null
     */
    public function createAccountLink(StripeAccount $stripeAccount): ?StripeAccountLink
    {
        $postData = [
            'account' => $stripeAccount->account_id,
            'refresh_url' => config('app.url') . '/stripe/refresh',
            'return_url' => config('app.url') . '/stripe/return',
            'type' => 'account_onboarding',
            'collection_options' => ['fields' => 'currently_due']
        ];

        try {
            $create = $this->stripe->accountLinks->create($postData);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        try {
            $stripeAccountLink = new StripeAccountLink();
            $stripeAccountLink->user_id = $stripeAccount->user_id;
            $stripeAccountLink->account_id = $stripeAccount->id;
            $stripeAccountLink->object = $create->object;
            $stripeAccountLink->url = $create->url;
            $stripeAccountLink->expires_at = date('Y-m-d H:i:s', $create->expires_at);
            $stripeAccountLink->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $stripeAccountLink;
    }

    /**
     * @todo - move to SubscriptionService
     *
     * @doc https://docs.stripe.com/api/subscriptions/create
     *
     * @param Community $community
     * @param CommunityMember $member
     * @param PaymentMethodMarketplace $paymentMethod
     * @param string $period
     * @return MemberSubscriptions|null
     */
    public function createSubscription(
        Community $community,
        CommunityMember $member,
        PaymentMethodMarketplace $paymentMethod,
        string $period
    ): ?MemberSubscriptions
    {
        $priceId = $period === 'yearly' ?
            $community->price->stripe_price_id_yearly :
            $community->price->stripe_price_id_monthly;

        $item = ['price' => $priceId];

        $vatRate = VatRate::where([
            'type' => VatRate::TYPE_GENERAL,
            'category' => 'Electronically Supplied Services',
            'country' => $member->user->country
        ])->first();

        if ($vatRate) {
            $item['tax_rates'] = [$vatRate->stripe_tax_rate_id];
        }

        // trial period can only be applied to new customers
        // we remove it if customer already had a subscription
        $existingMember = CommunityMember::where([
            'community_id' => $community->id,
            'user_id' => session('user_id')
        ])->first();

        if ($existingMember && $existingMember->subscription) {
            $community->trial_days = null;
        }

        try {
            $data = [
                'customer' => $member->user->stripe_customer_id_marketplace,
                'automatic_tax' => [
                    'enabled' => false
                ],
                'items' => [$item],
                'default_payment_method' => $paymentMethod->payment_method_id,
                'description' => 'Community #'. $community->id .' '. $period .' sub / user #' . $member->user_id
            ];

            if ($community->trial_days) {
                $data['trial_period_days'] = $community->trial_days;
            }

            \Log::info(['StripeService@createSubscription data', $data]);

            $subscription = $this->stripe->subscriptions->create($data);

            //  if the attempted charge fails, the subscription is created in an "incomplete" status.
            if ($subscription->status === 'incomplete') {
                return null;
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        sleep(2);

        try {
            $memberSubscription = MemberSubscriptions::create([
                'member_id' => $member->id,
                'community_id' => $community->id,
                'price_id' => $community->price->id,
                'payment_method_id' => $paymentMethod->id,
                'stripe_subscription_id' => $subscription->id,
                'period' => $period,
                'status' => MemberSubscriptions::STATUS_ACTIVE,
                'trial_ends_at' => $community->trial_days ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'next_billing_at' => date('Y-m-d H:i:s', $subscription->current_period_end)
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        $invoice = $this->retrieveInvoice($subscription->latest_invoice);
        if ($invoice && $invoice->status === 'paid') {
            $taxRate = 0;
            if ($invoice->subtotal_excluding_tax > 0) {
                $taxRate = ($invoice->tax / $invoice->subtotal_excluding_tax) * 100;
            }

            try {
                $transaction = new MemberSubscriptionsTransactions();
                $transaction->charge = $invoice->charge;
                $transaction->invoice = $invoice->id;
                $transaction->member_id = $member->id;
                $transaction->community_id = $community->id;
                $transaction->subscription_id = $memberSubscription->id;
                $transaction->payment_method_id = $memberSubscription->payment_method_id;
                $transaction->number = date('ym') . strtoupper(uniqid());
                $transaction->amount = $invoice->total;
                $transaction->tax = $invoice->tax;
                $transaction->tax_rate = $taxRate;
                $transaction->fees = $this->calculatePackieFees($invoice->total);
                $transaction->currency = strtoupper($invoice->currency);
                $transaction->country = $member->user->country;
                $transaction->status = MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE;
                $transaction->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }

        return $memberSubscription;
    }

    /**
     * @doc https://docs.stripe.com/api/transfers/create
     *
     * @param string $destination
     * @param int $amount
     * @param string $currency
     * @return array
     */
    public function createTransfer(string $destination, int $amount, string $currency = CurrencyEnum::CURRENCY_EUR): array
    {
        try {
            $transfer = $this->stripe->transfers->create([
                'amount' => $amount,
                'currency' => strtolower($currency),
                'destination' => $destination
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail($e->getMessage());
        }

        return $this->success(['transfer' => $transfer]);
    }

    /**
     * @doc https://docs.stripe.com/api/accounts/login_link/create
     *
     * @param string $accountId
     * @return \Stripe\LoginLink|null
     */
    public function createLoginLink(string $accountId): ?\Stripe\LoginLink
    {
        try {
            return $this->stripe->accounts->createLoginLink($accountId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $chargeId
     * @return \Stripe\Charge|null
     */
    public function retrieveCharge(string $chargeId): ?\Stripe\Charge
    {
        try {
            return $this->stripe->charges->retrieve($chargeId, []);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $balanceTransactionId
     * @return \Stripe\BalanceTransaction|null
     */
    public function retrieveBalanceTransaction(string $balanceTransactionId): ?\Stripe\BalanceTransaction
    {
        try {
            return $this->stripe->balanceTransactions->retrieve($balanceTransactionId, []);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $paymentIntentId
     * @return \Stripe\PaymentIntent|null
     */
    public function retrievePaymentIntent(string $paymentIntentId): ?\Stripe\PaymentIntent
    {
        try {
            return $this->stripe->paymentIntents->retrieve($paymentIntentId, []);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @return \Stripe\Balance|null
     */
    public function retrieveBalance(): ?\Stripe\Balance
    {
        try {
            return $this->stripe->balance->retrieve([]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @doc https://docs.stripe.com/api/invoices/pay
     *
     * @param string $invoiceId
     * @return Invoice|null
     */
    public function payInvoice(string $invoiceId): ?Invoice
    {
        try {
            return $this->stripe->invoices->pay($invoiceId, []);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $invoiceId
     * @return Invoice|null
     */
    public function retrieveInvoice(string $invoiceId): ?Invoice
    {
        try {
            return $this->stripe->invoices->retrieve($invoiceId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $subscriptionId
     * @return StripeSubscription|null
     */
    public function retrieveSubscription(string $subscriptionId): ?\Stripe\Subscription
    {
        try {
            return $this->stripe->subscriptions->retrieve($subscriptionId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param float $percentage
     * @param string $country
     * @return \Stripe\TaxRate|null
     */
    public function createTaxRate(float $percentage, string $country): ?\Stripe\TaxRate
    {
        try {
            return $this->stripe->taxRates->create([
                'display_name' => 'VAT (' . $country . ')',
                'description' => 'General - Electronically Supplied Services - ' . $country,
                'percentage' => $percentage,
                'country' => $country,
                'jurisdiction' => $country,
                'inclusive' => true,
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $invoiceId
     * @param string $description
     * @return void
     */
    public function updateInvoice(string $invoiceId, string $description): void
    {
        try {
            $this->stripe->invoices->update(
                $invoiceId, [
                    'description' => $description
                ]
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Returns amount of Packie's fees related to the transaction
     *
     * @param int $amount
     * @return int
     */
    public function calculatePackieFees(int $amount): int
    {
        return ($amount * 0.05 + 30) * (1 + self::VAT_RATE_IE);
    }

    /**
     * @param string $setupIntentId
     * @return \Stripe\SetupIntent|null
     */
    public function retrieveSetupIntent(string $setupIntentId): ?\Stripe\SetupIntent
    {
        try {
            return $this->stripe->setupIntents->retrieve($setupIntentId);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }
    }
}
