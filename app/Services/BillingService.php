<?php

namespace App\Services;

use App\Enum\PayoutStatusEnum;
use App\Models\Billing\Payouts;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class BillingService extends Service
{
    const VAT_RATE_IE = 0.23; // Ireland VAT rate 23%

    private StripeService $stripeService;
    private PayoutService $payoutService;
    private LoggerInterface $logger;
    private string $log;

    public function __construct(
        StripeService $stripeService,
        PayoutService $payoutService,
        LoggerInterface $logger
    ) {
        $this->stripeService = $stripeService;
        $this->payoutService = $payoutService;
        $this->logger = $logger;
        $this->log = '';
    }

    /**
     * Get pending payouts and make fund transfer on Stripe
     * from Marketplace account to customer account
     *
     * @return void
     */
    public function treatPendingPayouts(): void
    {
        $this->log = 'TreatPendingPayouts' . PHP_EOL;

        $balanceAmount = $this->retrieveBalance();
        $payouts = $this->retrievePendingPayouts();

        $this->log .= 'Balance: ' . $balanceAmount . PHP_EOL;
        $this->log .= count($payouts) . ' found' . PHP_EOL;

        foreach ($payouts as $payout)
        {
            if ($balanceAmount < $payout->amount) {
                $this->log .= 'Not enough on balance (' . $balanceAmount . ')' . PHP_EOL;
                break;
            }

            $balanceAmount = $this->processPayout($payout, $balanceAmount);
        }

        $this->log .= 'Total: ' . $balanceAmount . PHP_EOL;
        $this->logger->info($this->log);
        $this->logProcessEnd();
    }

    private function retrieveBalance(): float
    {
        $balance = $this->stripeService->retrieveBalance();

        return $balance->available[0]['amount'];
    }

    /**
     * @return Collection
     */
    private function retrievePendingPayouts(): Collection
    {
        return Payouts::where(['status' => PayoutStatusEnum::STATUS_PENDING])->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @param Payouts $payout
     * @param float $balanceAmount
     * @return float
     */
    private function processPayout(Payouts $payout, float $balanceAmount): float
    {
        $user = $payout->community->user;

        $this->log .= PHP_EOL . 'Payout #' . $payout->id . '...' . PHP_EOL;
        $this->log .= 'Amount: ' . $payout->amount / 100 . PHP_EOL;
        $this->log .= 'Currency: ' . $payout->currency . PHP_EOL;
        $this->log .= 'Destination: ' . $user->stripeAccount->account_id . PHP_EOL;
        $this->log .= $user->name . ' (' . $user->email . ')' . PHP_EOL;

        $completePayout = $this->payoutService->completePayoutRequest(
            $this->stripeService,
            $user->stripeAccount->account_id,
            $payout
        );

        if ($completePayout['success'] === true) {
            $balanceAmount -= $payout->amount;
        }

        return $balanceAmount;
    }

    private function logProcessEnd(): void
    {
        $this->logger->info('END at ' . date('Y-m-d H:i:s') .  PHP_EOL);
    }

    /**
     * @param Builder $query
     * @return array
     */
    public function getPayoutsTotals(Builder $query): array
    {
        try {
            $balance = $this->stripeService->retrieveBalance();
            $totalsBalance = $balance->available[0]['amount'];
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail(__('Failed to retrieve Stripe balance.'));
        }

        $queryPending = clone $query;
        $queryPaid = clone $query;

        return $this->success([
            'balance' => $totalsBalance,
            'pending' => $queryPending->where('status', '=', PayoutStatusEnum::STATUS_PENDING)->sum('amount'),
            'paid' => $queryPaid->where('status', '=', PayoutStatusEnum::STATUS_COMPLETE)->sum('amount'),
        ]);
    }

    /**
     * @param string|null $country
     * @param string $type
     * @param string $category
     * @return float
     */
    public function getVatRateByCountry(
        string $country = null,
        string $type = VatRate::TYPE_GENERAL,
        string $category = 'Electronically Supplied Services'
    ): float
    {
        $result = 0;

        if ($country !== null) {
            $vatRate = VatRate::where([
                'type' => $type,
                'category' => $category,
                'country' => $country
            ])->first();

            if ($vatRate) {
                $result = $vatRate->rate;
            }
        }

        return $result;
    }

    /**
     * @param array $filter
     * @param array $sort
     * @param int $perPage
     * @param int $currentPage
     * @return array
     */
    public function getFilteredPayouts(array $filter, array $sort, int $perPage, int $currentPage): array
    {
        $query = Payouts::with('community')->with('user');

        if ($filter['search']) {
            $search = $filter['search'];

            $query->where(function($query) use ($search) {
                $query->whereHas('community', function($q) use ($search) {
                    $q->where('name', 'LIKE', '%'. $search .'%');
                });
                $query->orWhereHas('user', function($q) use ($search) {
                    $q->where('firstname', 'LIKE', '%'. $search .'%')
                        ->orWhere('lastname', 'LIKE', '%'. $search .'%');
                });
                $query->orWhere('stripe_transfer_id', 'LIKE', '%'. $search .'%');
            });
        }
        if ($filter['date_start']) {
            $query->where('created_at', '>=', $filter['date_start']);
        }
        if ($filter['date_end']) {
            $query->where('created_at', '<=', $filter['date_end']);
        }
        if ($filter['user']) {
            $query->where('to', '=', $filter['user']);
        }
        if ($filter['community']) {
            $query->where('community_id', '=', $filter['community']);
        }

        $totals = $this->getPayoutsTotals(clone $query);
        if ($filter['status']) {
            $query->where('status', '=', $filter['status']);
        }

        if ($sort['column'] && $sort['direction']) {
            $query->orderBy(
                $sort['column'],
                $sort['direction']
            );
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        $payouts = $query->get();
        $paginate = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [$totals, $payouts, $paginate];
    }
}
