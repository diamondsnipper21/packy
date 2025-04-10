<?php

namespace App\Services\Authenticator;

use App\Mail\Authenticator\TwoFactorAuthenticationCodeMail;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use App\Models\User;
use App\Services\LoggerService;
use Illuminate\Support\Facades\Mail;

class LocalAuthenticatorService implements Authenticator
{
    public function setup()
    {
        // no setup required
    }

    /**
     * @return string|null
     */
    public function generate(): ?string
    {
        if (!session()->has('2fa_code')) {
            $code = sprintf("%06d", mt_rand(1, 999999));
            \Log::info(['LocalAuthenticatorService $code', $code]);

            session()->put('2fa_code', base64_encode(md5($code, true)));

            $send = self::send($code);
            if (!$send) {
                return null;
            }
        }

        return session('2fa_code');
    }

    /**
     * @param string|null $usersCode
     * @param bool $reGen
     * @return array
     */
    public function verify(string $usersCode = null, bool $reGen = false): array
    {
        if (session()->has('2fa_locked')) {
            return ['success' => false];
        }

        // a code has been validated for less than 10 minutes
        if (session()->has('2fa_expires') && strtotime('now') <= session('2fa_expires')) {
            return ['success' => true, 'expires' => session('2fa_expires')];
        }

        // the code didn't exist yet or user ask for a generation
        if (!session()->has('2fa_code') || $reGen === true) {
            return ['success' => false, 'code' => self::generate()];
        }

        if (session()->has('2fa_code')) {
            $usersCode = base64_encode(md5($usersCode, true));
            if ($usersCode === session('2fa_code')) {
                // 2fa code is valid during 10 minutes only
                session()->put('2fa_expires', strtotime('now +10 minutes'));
                session()->forget('2fa_code');
                session()->forget('2fa_locked');
                return ['success' => true, 'expires' => session('2fa_expires')];
            }
        }

        return ['success' => false];
    }

    /**
     * @param string $code
     * @return bool
     */
    private function send(string $code): bool
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            return false;
        }

        try {
            Mail::to($user->email)->send(new TwoFactorAuthenticationCodeMail($user, $code));
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return false;
        }

        return true;
    }
}
