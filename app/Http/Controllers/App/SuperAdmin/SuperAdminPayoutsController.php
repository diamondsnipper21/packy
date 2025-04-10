<?php

namespace App\Http\Controllers\App\SuperAdmin;

use App\Enum\PayoutStatusEnum;
use App\Models\Billing\Payouts;
use App\Services\Authenticator\AuthenticatorService;
use App\Services\BillingService;
use App\Services\PayoutService;
use App\Services\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SuperAdminPayoutsController extends SuperAdminAppController
{
    private BillingService $billingService;
    private PayoutService $payoutService;

    /**
     * @param BillingService $billingService
     * @param PayoutService $payoutService
     */
    public function __construct(BillingService $billingService, PayoutService $payoutService)
    {
        $this->billingService = $billingService;
        $this->payoutService = $payoutService;
    }

    /**
     * Displays super admin dashboard
     *
     * @return View
     */
    public function view(): View
    {
        return view('dashboard-super-admin');
    }

    /**
     * Returns filtered search on payouts
     *
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        [$totals, $payouts, $paginate] = $this->billingService->getFilteredPayouts(
            $request->filter,
            $request->sort,
            $request->perPage,
            $request->currentPage
        );

        $users = [];
        $communities = [];
        foreach ($payouts as $payout) {
            $users[$payout->to] = $payout->user;
            $communities[$payout->community_id] = $payout->community;
        }

        return [
            'success' => true,
            'payouts' => [
                'filters' => [
                    'users' => $users,
                    'communities' => $communities,
                    'statuses' => [
                        PayoutStatusEnum::STATUS_PENDING,
                        PayoutStatusEnum::STATUS_COMPLETE,
                        PayoutStatusEnum::STATUS_FAILED,
                    ],
                ],
                'totals' => $totals,
                'data' => $paginate->items(),
                'pagination' => [
                    'per_page' => $request->perPage,
                    'total_rows' => $paginate->total(),
                    'current_page' => $paginate->currentPage(),
                    'first_page' => 1,
                    'last_page' => $paginate->lastPage(),
                ]
            ],
        ];
    }

    /**
     * @param Request $request
     * @param AuthenticatorService $authenticatorService
     * @param StripeService $stripeService
     * @return JsonResponse
     */

    /**
     * Completes a payout request
     *
     * @param Request $request
     * @param AuthenticatorService $authenticatorService
     * @param StripeService $stripeService
     * @return JsonResponse
     */
    public function complete(Request $request, AuthenticatorService $authenticatorService, StripeService $stripeService): JsonResponse
    {
        // 2FA validation
        $checkCode = $authenticatorService->getInstance()->verify();
        if ($checkCode['success'] === false) {
            return $this->error();
        }

        $payout = $this->getPayout($request->id);
        if (!$payout) {
            return $this->error();
        }

        $completePayout = $this->payoutService->completePayoutRequest(
            $stripeService,
            $payout->community->user->stripeAccount->account_id,
            $payout
        );

        if ($completePayout['success'] !== true) {
            return $this->error();
        }

        return $this->returnSuccessData(__('Payout completed.'), $completePayout['payout']);
    }

    /**
     * Refuses a payout request
     *
     * @param Request $request - The request object containing the payout id
     * @return JsonResponse - The JSON response containing the
     */
    public function refuse(Request $request): JsonResponse
    {
        $payout = $this->getPayout($request->id);
        if (!$payout) {
            return $this->error();
        }

        $this->payoutService->refusePayoutRequest($payout);

        return $this->returnSuccessData(__('Payout refused.'), $payout);
    }

    /**
     * @param int $id
     * @return Payouts|null
     */
    private function getPayout(int $id): ?Payouts
    {
        return Payouts::where(['id' => $id])
            ->where('status', '=', PayoutStatusEnum::STATUS_PENDING)
            ->with('community')
            ->with('user')
            ->first();
    }

    /**
     * Returns success data including a message, specific payout, and totals.
     *
     * @param string $message The message associated with the success data
     * @param Payouts $payout The specific payout object
     * @return JsonResponse The JSON response containing the success data
     */
    private function returnSuccessData(string $message, Payouts $payout): JsonResponse
    {
        $query = Payouts::with('community')->with('user');

        return $this->success([
            'message' => $message,
            'payout' => $payout,
            'totals' => $this->billingService->getPayoutsTotals($query)
        ]);
    }
}
