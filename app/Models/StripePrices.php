<?php

namespace App\Models;

use App\Enum\StripePriceTypeEnum;
use App\Helpers\CurrencyHelper;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StripePrices extends Model
{
    public $table = 'stripe_prices';

    /**
     * @var array
     */
    protected $fillable = [
        'product_id',
        'amount_monthly',
        'amount_yearly',
        'stripe_price_id_monthly',
        'stripe_price_id_yearly'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'priceName',
        'members',
        'type'
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(StripeProducts::class, 'product_id', 'id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscriptions::class, 'price_id', 'id');
    }

    /**
     * Number of members subscriptions with this price
     * @return int
     */
    public function getMembersAttribute(): int
    {
        return $this->subscriptions()->count();
    }

    /**
     * @return string
     */
    public function getTypeAttribute(): string
    {
        if ($this->amount_yearly !== null && $this->amount_monthly !== null) {
            return StripePriceTypeEnum::TYPE_MONTHLY_YEARLY;
        } else if ($this->amount_yearly !== null) {
            return StripePriceTypeEnum::TYPE_YEARLY;
        } else {
            return StripePriceTypeEnum::TYPE_MONTHLY;
        }
    }

    /**
     * @return string
     */
    public function getPriceNameAttribute(): string
    {
        $name = '';
        if ($this->amount_monthly) {
            $name = CurrencyHelper::format($this->amount_monthly) . ' ' . __('common.per-month');
        }
        if ($this->amount_yearly) {
            if ($this->amount_monthly) {
                $name .= " ". __('common.or') . " ";
            }
            $name .= CurrencyHelper::format($this->amount_yearly) . ' ' . __('common.per-year');
        }

        return $name;
    }
}
