<?php

namespace App\Helpers;

use App\Enum\CurrencyEnum;
use App\Enum\LangEnum;

class CurrencyHelper
{
    /**
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function format(float $amount, string $currency = CurrencyEnum::CURRENCY_EUR): string
    {
        $formatter = new \NumberFormatter(
            session('locale') ?? LangEnum::LANG_ENGLISH,
            \NumberFormatter::CURRENCY
        );

        return $formatter->formatCurrency($amount, $currency);
    }
}