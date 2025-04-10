<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StripeProducts extends Model
{
    use BelongsToCommunity;

    public $table = 'stripe_products';

    protected $fillable = [
        'community_id',
        'stripe_product_id',
    ];

    /**
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(StripePrices::class, 'product_id')
            ->orderBy('created_at', 'desc');
    }
}
