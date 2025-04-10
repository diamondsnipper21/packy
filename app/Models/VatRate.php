<?php

namespace App\Models;

use App\Enum\CountryCodeEnum;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    public $table = 'vat_rates';

    protected $fillable = [
        'type',
        'category',
        'country',
        'rate',
        'stripe_tax_rate_id'
    ];

    const TYPE_GENERAL = 'General';

    public static function getVatRates(): array
    {
        return [
            CountryCodeEnum::COUNTRY_CODE_AUSTRIA => 20,
            CountryCodeEnum::COUNTRY_CODE_BELGIUM => 21,
            CountryCodeEnum::COUNTRY_CODE_BULGARIA => 20,
            CountryCodeEnum::COUNTRY_CODE_CZECHIA => 20,
            CountryCodeEnum::COUNTRY_CODE_DENMARK => 25,

            CountryCodeEnum::COUNTRY_CODE_GERMANY => 19,
            CountryCodeEnum::COUNTRY_CODE_ESTONIA => 22,
            CountryCodeEnum::COUNTRY_CODE_IRELAND => 23,
            CountryCodeEnum::COUNTRY_CODE_GREECE => 24,
            CountryCodeEnum::COUNTRY_CODE_SPAIN => 21,

            CountryCodeEnum::COUNTRY_CODE_FRANCE => 20,
            CountryCodeEnum::COUNTRY_CODE_ITALY => 22,
            CountryCodeEnum::COUNTRY_CODE_CYPRUS => 19,
            CountryCodeEnum::COUNTRY_CODE_LATVIA => 21,
            CountryCodeEnum::COUNTRY_CODE_LITHUANIA => 21,

            CountryCodeEnum::COUNTRY_CODE_LUXEMBOURG => 17,
            CountryCodeEnum::COUNTRY_CODE_HUNGARY => 27,
            CountryCodeEnum::COUNTRY_CODE_MALTA => 18,
            CountryCodeEnum::COUNTRY_CODE_NETHERLANDS => 21,
            CountryCodeEnum::COUNTRY_CODE_POLAND => 23,

            CountryCodeEnum::COUNTRY_CODE_PORTUGAL => 23,
            CountryCodeEnum::COUNTRY_CODE_ROMANIA => 19,
            CountryCodeEnum::COUNTRY_CODE_SLOVENIA => 22,
            CountryCodeEnum::COUNTRY_CODE_SLOVAKIA => 20,
            CountryCodeEnum::COUNTRY_CODE_FINLAND => 25.5,

            CountryCodeEnum::COUNTRY_CODE_SWEDEN => 25,
            CountryCodeEnum::COUNTRY_CODE_UNITED_KINGDOM => 20
        ];
    }
}
