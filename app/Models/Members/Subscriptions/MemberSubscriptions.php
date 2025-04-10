<?php

namespace App\Models\Members\Subscriptions;

use App\Models\PaymentMethodMarketplace;
use App\Models\StripePrices;
use App\Services\Notifications\Broadcast\NewSubscriptionNotification;
use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberSubscriptions extends Model
{
    use BelongsToCommunity;
    use BelongsToMember;

    public $table = 'community_member_subscriptions';

    protected $fillable = [
        'member_id',
        'community_id',
        'price_id',
        'payment_method_id',
        'stripe_subscription_id',
        'period',
        'status',
        'failed_attempts',
        'trial_ends_at',
        'next_billing_at',
        'cancelled_at'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'amount'
    ];

    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    public function price(): BelongsTo
    {
        return $this->belongsTo(StripePrices::class, 'price_id', 'id');
    }

    public function cancel(): HasOne
    {
        return $this->hasOne(MemberSubscriptionsCancelRequests::class, 'subscription_id', 'id');
    }

    public function payment_method(): HasOne
    {
        return $this->hasOne(PaymentMethodMarketplace::class, 'id', 'payment_method_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MemberSubscriptionsTransactions::class, 'subscription_id');
    }

    /**
     * @return float
     */
    public function getAmountAttribute(): float
    {
        $amount = 0;
        if ($this->period === self::PERIOD_MONTHLY) {
            $amount = $this->price->amount_monthly;
        } elseif ($this->period === self::PERIOD_YEARLY) {
            $amount = $this->price->amount_yearly;
        }

        return $amount;
    }

    /**
     * @param int $days
     * @return Collection
     */
    public static function getNextBillingSubscriptions(int $days): Collection
    {
        $date = Carbon::now()->addDays($days)
            ->format('Y-m-d');

        return self::where('next_billing_at', '>=', $date . ' 00:00:00')
            ->where('next_billing_at', '<=', $date . ' 23:59:59')
            ->where('status', '=', self::STATUS_ACTIVE)
            ->get();
    }

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();
        static::created(function ($model) {
            if ($model->community->user_id === 1) {
                new NewSubscriptionNotification($model->community->user_id, $model->member->user);
            }
        });
    }
}
