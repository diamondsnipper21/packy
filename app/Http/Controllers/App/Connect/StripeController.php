<?php

namespace App\Http\Controllers\App\Connect;

use App\Enum\StripeAccountStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\StripeAccount;
use App\Services\LoggerService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    /**
     * The refresh_url should call a method on your server to create a new Account Link with the same
     * parameters and redirect the connected account to the new Account Link URL.
     *
     * @doc https://docs.stripe.com/connect/hosted-onboarding?shell=true&api=true
     * @return RedirectResponse
     */
    public function refresh(): RedirectResponse
    {
        $stripeService = new StripeService();

        $stripeAccount = StripeAccount::where(['user_id' => session('user_id')])->first();
        if (!$stripeAccount) {
            $stripeAccount = $stripeService->createAccount(session('user_id'));
        }

        $stripeService->deleteAccountLinks();

        $stripeAccountLink = $stripeService->createAccountLink($stripeAccount);
        if ($stripeAccountLink) {
            return redirect($stripeAccountLink->url);
        }

        return redirect()->to('/');
    }

    /**
     * Stripe redirects the connected account back to this URL when they complete the onboarding flow or
     * click Save for later at any point in the flow. It does not mean that all information has been
     * collected, or that there are no outstanding requirements on the account.
     * It only means the flow was entered and exited properly.
     *
     * @doc https://docs.stripe.com/connect/hosted-onboarding?shell=true&api=true
     */
    public function return()
    {
        $stripeService = new StripeService();

        $account = $stripeService->retrieveAccount();
        if (isset($account['requirements']['currently_due']) && !$account['requirements']['currently_due']) {
            // account is enabled and verified
            try {
                StripeAccount::where([
                    'user_id' => session('user_id'),
                    'account_id' => $account->id
                ])->update([
                    'status' => StripeAccountStatusEnum::STATUS_ENABLED
                ]);
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }

            session()->put('flash-message', [
                'title' => __('Setup complete'),
                'text' => __('You\'re now ready to start making money with your community!'),
                'type' => 'success'
            ]);
        }

        return redirect()->to('/');
    }

    /**
     * @param Request $request
     * @return void
     */
    /*
    public function deleteAccount(Request $request): void
    {
        if (!$request->accountId) {
            exit();
        }

        $stripeService = new StripeService();
        $delete = $stripeService->deleteAccount($request->accountId);
        dump($delete);
    }
    */
}
