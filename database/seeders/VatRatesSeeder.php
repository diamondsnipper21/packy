<?php

namespace Database\Seeders;

use App\Models\VatRate;
use App\Services\LoggerService;
use App\Services\StripeService;
use Illuminate\Database\Seeder;

class VatRatesSeeder extends Seeder
{
    /**
     * Populates database with vat rates
     *
     * @return void
     */
    public function run(): void
    {
        $stripeService = new StripeService();

        foreach (VatRate::getVatRates() as $country => $vatRate) {
            $rate = VatRate::where(['country' => $country])->first();
            if ($rate) {
                continue;
            }

            $stripeTaxRate = $stripeService->createTaxRate(
                percentage: $vatRate,
                country: $country
            );

            if (!$stripeTaxRate) {
                continue;
            }

            try {
                $rate = VatRate::firstOrNew(['country' => $country]);
                $rate->type = VatRate::TYPE_GENERAL;
                $rate->category = 'Electronically Supplied Services';
                $rate->country = $country;
                $rate->rate = $vatRate;
                $rate->stripe_tax_rate_id = $stripeTaxRate?->id;
                $rate->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }
}
