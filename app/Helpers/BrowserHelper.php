<?php

namespace App\Helpers;

class BrowserHelper
{
    /**
     * @return bool
     */
    public static function isSafari(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (str_contains($userAgent, 'Safari') &&
            !str_contains($userAgent, 'Chrome')) {
            return true;
        }

        return false;
    }
}