<?php declare(strict_types=1);

namespace App\Services;

use App\Enum\CurrencyEnum;
use App\Enum\MemberSubscriptionTransactionStatusEnum;
use App\Enum\PayoutStatusEnum;
use App\Models\Billing\Invoices;
use App\Models\Billing\Payouts;
use App\Models\Community;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayoutService extends Service
{
    /**
     * Perform an action to request a payout for a specified community.
     *
     * @param Community $community The community for which to request a payout
     * @return array Returns an array with information about the payout request, including success status, message, and payout object
     */
    public function requestPayoutForCommunity(Community $community): array
    {
        if ($community->payoutsBalance <= 0) {
            return $this->fail(__('Insufficient funds in balance.'));
        }

        // Start a new database transaction.
        DB::beginTransaction();

        $payout = $this->createPayoutRequest($community);
        if (!$payout) {
            DB::rollBack();
            return $this->fail(__('An error occurred while creating a payout.'));
        }

        if (!$this->createSubscriptionTransactions($community, $payout)) {
            DB::rollBack();
            $payout->delete();
            return $this->fail(__('An error occurred while creating a transaction.'));
        }

        // If everything went well, commit the transaction.
        DB::commit();

        return $this->success([
            'message' => __('Payout asked.'),
            'payout' => $payout
        ]);
    }

    /**
     * Complete a payout for a specific Stripe account using StripeService.
     *
     * @param StripeService $stripeService The service used to interact with Stripe API
     * @param string $stripeAccountId The account ID associated with the Stripe account
     * @param Payouts $payout The payout object to be completed
     * @return array An array containing information about the completed payout
     */
    public function completePayoutRequest(StripeService $stripeService, string $stripeAccountId, Payouts $payout): array
    {
        if (config('app.env') === 'production') {
            $stripeTransferId = $this->createStripeTransfer($stripeService, $stripeAccountId, $payout);
            if (!$stripeTransferId) {
                $payout->status = PayoutStatusEnum::STATUS_FAILED;
                $payout->save();

                return $this->fail(__('An error occurred while completing the payout.'));
            }
        } else {
            $stripeTransferId = 'tr_'. rand();
        }

        try {
            $payout->stripe_transfer_id = $stripeTransferId;
            $payout->status = PayoutStatusEnum::STATUS_COMPLETE;
            $payout->completed_at = new \DateTime();
            $payout->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail(__('An error occurred while completing the payout.'));
        }

        if ($payout->status === PayoutStatusEnum::STATUS_COMPLETE) {
            $this->createInvoiceFeesFromPayout($payout);
        }

        return $this->success(['payout' => $payout]);
    }

    /**
     * @param Payouts $payout
     * @return void
     */
    public function refusePayoutRequest(Payouts $payout): void
    {
        try {
            $payout->status = PayoutStatusEnum::STATUS_REFUSED;
            $payout->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        try {
            MemberSubscriptionsTransactions::where([
                'community_id' => $payout->community_id,
                'payout_id' => $payout->id
            ])->update(['payout_id' => NULL]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Create a payout request for a specific community.
     *
     * @param Community $community The community for which the payout is created
     * @return Payouts|null The created payout object if successful, or null if an exception is caught
     */
    private function createPayoutRequest(Community $community): ?Payouts
    {
        $transactions = MemberSubscriptionsTransactions::getPayableForPayout($community->id);

        try {
            $payout = Payouts::create([
                'community_id' => $community->id,
                'to' => $community->user->id,
                'amount' => $community->payoutsBalance * 100,
                'currency' => config('payment.currency'),
                'status' => PayoutStatusEnum::STATUS_PENDING,
                'period_start' => $transactions->first()->created_at,
                'period_end' => $transactions->last()->created_at
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $payout;
    }

    /**
     * Creates subscription transactions for a given community and payout.
     *
     * @param Community $community The community for which subscription transactions are created.
     * @param Payouts $payout The payout to associate with the transactions.
     * @return bool Returns true if the subscription transactions were successfully created and updated,
     * false if an exception occurred during the process.
     */
    private function createSubscriptionTransactions(Community $community, Payouts $payout): bool
    {
        try {
            MemberSubscriptionsTransactions::where([
                'community_id' => $community->id,
                'status' => MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE
            ])
                ->where('created_at', '<=', MemberSubscriptionsTransactions::getPayoutLimitDate())
                ->update(['payout_id' => $payout->id]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Create a transfer using the provided Stripe service and payout details.
     *
     * @param StripeService $stripeService The Stripe service instance to use for creating the transfer
     * @param string $stripeAccountId The destination Stripe account ID for the transfer
     * @param Payouts $payout The payout object containing amount and currency for the transfer
     * @return string|null The ID of the created transfer if successful, or null if an error occurs during transfer creation
     */
    private function createStripeTransfer(StripeService $stripeService, string $stripeAccountId, Payouts $payout): ?string
    {
        $transfer = $stripeService->createTransfer(
            destination: $stripeAccountId,
            amount: $payout->amount,
            currency: $payout->currency
        );

        if (!isset($transfer['id'])) {
            LoggerService::logException(__METHOD__, __LINE__, __('Error creating transfer'));
            return null;
        }

        return $transfer['id'];
    }

    /**
     * Create an invoice from a payout for Packie service fees
     *
     * @param Payouts $payout The payout object to create invoice from
     * @return void
     */
    private function createInvoiceFeesFromPayout(Payouts $payout): void
    {
        $packieFees = 0;
        foreach ($payout->transactions as $transaction) {
            $packieFees += $transaction->fees;
        }

        if (!$packieFees) {
            return;
        }

        $amount = $packieFees / (1 + BillingService::VAT_RATE_IE);
        $tax = $amount * BillingService::VAT_RATE_IE;

        $invoiceData = json_decode($payout->community->invoice_data ?? '{}', true);

        try {
            Invoices::insert([
                'user_id' => $payout->user->id,
                'community_id' => $payout->community->id,
                'number' => 'IN-' . date('Y'),
                'amount' => (int) $amount,
                'tax' => (int) $tax,
                'tax_rate' => BillingService::VAT_RATE_IE * 100,
                'currency' => CurrencyEnum::CURRENCY_EUR,
                'status' => Invoices::STATUS_PAID,
                'data' => json_encode([
                    'period_start' => Carbon::parse($payout->period_start)->format('Y-m-d'),
                    'period_end' => Carbon::parse($payout->period_end)->format('Y-m-d'),
                    'business_name' => $invoiceData['business_name'] ?? $payout->user->name,
                    'address' => $invoiceData['address'] ?? '',
                    'zipcode' => $invoiceData['zipcode'] ?? '',
                    'city' => $invoiceData['city'] ?? '',
                    'country' => $invoiceData['country'] ?? $payout->user->country,
                    'vat_number' => $invoiceData['vatNumber'] ?? '',
                ])
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }

    /**
     * Get payouts and transactions data for a specific year and community
     *
     * @param int $communityId The ID of the community to retrieve data for
     * @param int|null $year The year for which to retrieve data (default is current year)
     * @return array An array containing payouts and transactions data for the specified year and community
     */
    public function getPayoutsDataByYear(int $communityId, int $year = null): array
    {
        if ($year === null) {
            $year = Carbon::now()->year;
        }

        $dateStart = sprintf('%s-01-01 00:00:00', $year);
        $dateEnd = sprintf('%s-12-31 23:59:59', $year);

        $payouts = Payouts::where(['community_id' => $communityId])
            ->where('created_at', '>=', $dateStart)
            ->where('created_at', '<=', $dateEnd)
            ->get();

        $transactions = MemberSubscriptionsTransactions::where(['community_id' => $communityId])
            ->where('created_at', '>=', $dateStart)
            ->where('created_at', '<=', $dateEnd)
            ->get();

        return [$payouts, $transactions];
    }
}