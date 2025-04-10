<?php

namespace App\Mail\Authenticator;

use App\Mail\SupportMail;
use App\Models\User;

class TwoFactorAuthenticationCodeMail extends SupportMail
{
    /**
     * @param User $user
     * @param string $twoFactorCode
     */
    public function __construct(User $user, string $twoFactorCode)
    {
        parent::__construct($user);

        $this->id = 'two-factor-authentication-code';
        $this->with = [
            'userName' => $user->name,
            'twoFactorCode' => $twoFactorCode
        ];
    }
}
