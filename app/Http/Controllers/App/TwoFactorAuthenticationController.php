<?php

namespace App\Http\Controllers\App;

use App\Services\Authenticator\AuthenticatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TwoFactorAuthenticationController extends AppController
{
    /**
     * @param AuthenticatorService $authenticatorService
     * @return JsonResponse
     */
    public function init(AuthenticatorService $authenticatorService): JsonResponse
    {
        $auth = $authenticatorService->getInstance();

        session()->forget('2fa_code');
        session()->forget('2fa_expires');

        return $this->success([
            'code' => $auth->generate(),
            'expires' => session('2fa_expires')
        ]);
    }

    /**
     * @param Request $request
     * @param AuthenticatorService $authenticatorService
     * @return array
     */
    public function check(Request $request, AuthenticatorService $authenticatorService): array
    {
        return $authenticatorService->getInstance()->verify(
            $request->code,
            $request->send_again
        );
    }
}
