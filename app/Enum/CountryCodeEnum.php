<?php

namespace App\Enum;

class CountryCodeEnum
{
    public const COUNTRY_CODE_AUSTRIA = 'AT';
    public const COUNTRY_CODE_BELGIUM = 'BE';
    public const COUNTRY_CODE_BULGARIA = 'BG';
    public const COUNTRY_CODE_CZECHIA = 'CZ';
    public const COUNTRY_CODE_DENMARK = 'DK';
    public const COUNTRY_CODE_GERMANY = 'DE';
    public const COUNTRY_CODE_ESTONIA = 'EE';
    public const COUNTRY_CODE_IRELAND = 'IE';
    public const COUNTRY_CODE_GREECE = 'GR';
    public const COUNTRY_CODE_SPAIN = 'ES';
    public const COUNTRY_CODE_FRANCE = 'FR';
    public const COUNTRY_CODE_ITALY = 'IT';
    public const COUNTRY_CODE_CYPRUS = 'CY';
    public const COUNTRY_CODE_LATVIA = 'LV';
    public const COUNTRY_CODE_LITHUANIA = 'LT';
    public const COUNTRY_CODE_LUXEMBOURG = 'LU';
    public const COUNTRY_CODE_HUNGARY = 'HU';
    public const COUNTRY_CODE_MALTA = 'MT';
    public const COUNTRY_CODE_NETHERLANDS = 'NL';
    public const COUNTRY_CODE_POLAND = 'PL';
    public const COUNTRY_CODE_PORTUGAL = 'PT';
    public const COUNTRY_CODE_ROMANIA = 'RO';
    public const COUNTRY_CODE_SLOVENIA = 'SI';
    public const COUNTRY_CODE_SLOVAKIA = 'SK';
    public const COUNTRY_CODE_FINLAND = 'FI';
    public const COUNTRY_CODE_SWEDEN = 'SE';
    public const COUNTRY_CODE_UNITED_KINGDOM = 'GB';

    /**
     * @return array
     */
    static function getConstants(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
