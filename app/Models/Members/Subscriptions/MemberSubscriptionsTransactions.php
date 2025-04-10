<?php

namespace App\Models\Members\Subscriptions;

use App\Enum\MemberSubscriptionTransactionStatusEnum;
use App\Models\PaymentMethodMarketplace;
use App\Services\Notifications\Broadcast\NewPaymentNotification;
use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use App\Support\Traits\BelongsToSubscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberSubscriptionsTransactions extends Model
{
    use BelongsToMember;
    use BelongsToCommunity;
    use BelongsToSubscription;

    public $table = 'community_member_subscriptions_transactions';

    protected $fillable = [
        'member_id',
        'community_id',
        'subscription_id',
        'payment_method_id',
        'payout_id',
        'number',
        'charge',
        'invoice',
        'amount',
        'tax',
        'tax_rate',
        'fees',
        'currency',
        'country',
        'status',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'payable',
        'daysUntilPayable',
    ];

    public function getPayableAttribute(): int
    {
        return max(($this->amount - $this->tax - $this->fees), 0);
    }

    public function payment_method(): HasOne
    {
        return $this->hasOne(PaymentMethodMarketplace::class, 'id', 'payment_method_id');
    }

    public function getDaysUntilPayableAttribute(): int
    {
        $dt1 = new \DateTime($this->created_at);
        $dt2 = new \DateTime();
        $dt2->modify("-" . config('payment.payout_delay') . " day");

        return max($dt1->diff($dt2)->d, 0);
    }

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();
        static::created(function ($model) {
            if ($model->status === MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE) {
                new NewPaymentNotification($model);
            }
        });
    }

    /**
     * @param int $communityId
     * @return mixed
     */
    public static function getPayableForPayout(int $communityId)
    {
        return MemberSubscriptionsTransactions::where([
            'community_id' => $communityId,
            'status' => MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE,
        ])->where('created_at', '<=', self::getPayoutLimitDate())->orderBy('created_at', 'ASC')->get();
    }

    /**
     * @param int $communityId
     * @return mixed
     */
    public static function getPendingForPayout(int $communityId)
    {
        return MemberSubscriptionsTransactions::where([
            'community_id' => $communityId,
            'status' => MemberSubscriptionTransactionStatusEnum::STATUS_COMPLETE,
        ])->where('created_at', '>', self::getPayoutLimitDate())->orderBy('created_at', 'ASC')->get();
    }

    /**
     * @return string
     */
    public static function getPayoutLimitDate(): string
    {
        return date('Y-m-d H:i:s', strtotime('-' . config('payment.payout_delay') . ' days'));
    }

}
