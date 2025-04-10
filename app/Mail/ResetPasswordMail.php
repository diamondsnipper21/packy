<?php

namespace App\Mail;

use App\Models\User;

class ResetPasswordMail extends SupportMail
{
    /**
     * @param User $user
     * @param string $link
     */
    public function __construct(User $user, string $link)
    {
        parent::__construct($user);

        $this->id = 'reset-password';
        $this->with = [
            'userName' => $user->name,
            'link' => $link
        ];
    }
}
