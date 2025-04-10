<?php

namespace App\Models;

use App\Services\Notifications\Broadcast\NewSubscriptionNotification;
use App\Support\Traits\BelongsToCommunity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPlan extends Model
{
    use BelongsToCommunity;

    public $table = 'community_plans';

    protected $fillable = [
        'community_id',
        'payment_method_id',
        'st_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_start',
        'trial_end',
        'passed_due',
        'amount',
        'currency'
    ];

    protected $hidden = [
        'st_subscription_id',
    ];

    // Status - @todo - move to Enum
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAUSED = 'paused';

    // final statuses
    public const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';
    public const STATUS_DROPPED = 'dropped';

    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(UserPaymentMethod::class, 'payment_method_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(UserPlansTransactions::class, 'plan_id');
    }

    /**
     * @param array $communityIds
     * @return int
     */
    public static function getCountActiveSubscriptions(array $communityIds = []): int
    {
        return self::whereIn('community_id', $communityIds)
            ->where('status', self::STATUS_ACTIVE)
            ->count();
    }

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();
        static::created(function ($model) {
            new NewSubscriptionNotification(1, $model->community->user);
        });
    }

    /**
     * @param int $days
     * @return Collection
     */
    public static function getNextBillingPlans(int $days): Collection
    {
        $date = Carbon::now()->addDays($days)
            ->format('Y-m-d');

        return self::where('current_period_end', '>=', $date . ' 00:00:00')
            ->where('current_period_end', '<=', $date . ' 23:59:59')
            ->where('status', '=', self::STATUS_ACTIVE)
            ->get();
    }

    /**
     * @param int $days
     * @return Collection
     */
    public static function getNextBillingTrialPlans(int $days): Collection
    {
        $date = Carbon::now()->addDays($days)
            ->format('Y-m-d');

        return self::where('current_period_end', '>=', $date . ' 00:00:00')
            ->where('current_period_end', '<=', $date . ' 23:59:59')
            ->where('status', '=', self::STATUS_TRIALING)
            ->get();
    }
}
