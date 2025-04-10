<?php 
declare(strict_types=1);

namespace App\Services;

use App\Models\Community;
use App\Models\StripePrices;
use App\Models\StripeProducts;

class CommunityPriceService extends Service
{
    /**
     * @param Community $community
     * @param array $postData
     * @param StripeService $stripeService
     * @return array
     */
    public function create(Community $community, array $postData, StripeService $stripeService): array
    {
        $product = $community->products->first() ?? $this->createProduct($community, $stripeService);

        try {
            $price = StripePrices::firstOrNew(['id' => $postData['priceId'], 'product_id' => $product->id]);

            // if this is current price -> we set this price as active on Stripe
            $active = ($community->price_id && $community->price_id === $price->id);

            if (!$price->amount_monthly && $postData['amountMonthly']) {
                $stripePriceMonthly = $stripeService->createMonthlyPrice(
                    $postData['amountMonthly'],
                    $product->stripe_product_id,
                    $active
                );

                if ($stripePriceMonthly) {
                    $price->amount_monthly = $postData['amountMonthly'];
                    $price->stripe_price_id_monthly = $stripePriceMonthly->id;
                }
            }
            if (!$price->amount_yearly && $postData['amountYearly']) {
                $stripePriceYearly = $stripeService->createYearlyPrice(
                    $postData['amountYearly'],
                    $product->stripe_product_id,
                    $active
                );

                if ($stripePriceYearly) {
                    $price->amount_yearly = $postData['amountYearly'];
                    $price->stripe_price_id_yearly = $stripePriceYearly->id;
                }
            }
            $price->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail(__('An error occurred while updating community price.'));
        }

        return $this->success([
            'price' => $price,
            'products' => StripeProducts::where(['community_id' => $community->id])->with('prices')->get()
        ]);
    }

    /**
     * @param Community $community
     * @param array $postData
     * @param StripeService $stripeService
     * @return array
     */
    public function update(Community $community, array $postData, StripeService $stripeService): array
    {
        $price = null;

        // a price id has been passed into the request -> this is an existing price status update
        if ($postData['priceId'] !== null) {
            $price = StripePrices::where([
                'id' => $postData['priceId'],
                'product_id' => $community->products[0]->id
            ])->first();

            if (!$price) {
                return $this->fail(__('Price not found.'));
            }

            // enable new price
            $this->updateStripePrices($price, $stripeService);
        }

        // disable old prices
        if ($community->price) {
            $this->updateStripePrices($community->price, $stripeService, false);
        }

        // we update the community price to new price
        try {
            Community::where([
                'id' => $postData['communityId']
            ])->update([
                'price_id' => $postData['priceId']
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }

        return $this->success(['price' => $price]);
    }

    /**
     * @param Community $community
     * @param StripeService $stripeService
     * @return StripeProducts|null
     */
    private function createProduct(Community $community, StripeService $stripeService): ?StripeProducts
    {
        $stripeProduct = $stripeService->createProduct($community);
        if (!$stripeProduct) {
            return null;
        }

        try {
            $product = StripeProducts::create([
                'stripe_product_id' => $stripeProduct->id,
                'community_id' => $community->id,
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return null;
        }

        return $product;
    }

    /**
     * @param StripePrices $price
     * @param StripeService $stripeService
     * @param bool $active Default is true
     * @return bool
     */
    private function updateStripePrices(StripePrices $price, StripeService $stripeService, bool $active = true): bool
    {
        try {
            if ($price->stripe_price_id_monthly) {
                $stripeService->updatePrice($price->stripe_price_id_monthly, $active);
            }
            if ($price->stripe_price_id_yearly) {
                $stripeService->updatePrice($price->stripe_price_id_yearly, $active);
            }
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return false;
        }

        return true;
    }
}