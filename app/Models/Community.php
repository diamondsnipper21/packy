<?php

namespace App\Models;

use App\Enum\PayoutStatusEnum;
use App\Models\Billing\Invoices;
use App\Models\Billing\Payouts;
use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Community extends Model
{
    use BelongsToUser;

    public $table = 'community';

    protected $fillable = [
        'user_id',
        'name',
        'privacy',
        'owner_show',
        'summary_description',
        'description',
        'summary_photo',
        'logo',
        'favicon',
        'url',
        'last_sent_notification',
        'auto_post_approbation',
        'status',
        'tab_classrooms',
        'tab_calendar',
        'tab_leaderboard',
        'price_id',
        'trial_days',
        'invoice_data'
    ];

    public const PRIVACY_PRIVATE = 'private';
    public const PRIVACY_PUBLIC = 'public';

    public const OWNER_SHOW = 1;
    public const OWNER_HIDE = 0;

    public const AUTO_POST_APPROBATION = 1;
    public const MANUAL_POST_APPROBATION = 0;

    // Status
    public const STATUS_PUBLISHED = 1;
    public const STATUS_SUSPENDED = 2;
    public const STATUS_INACTIVE = 3;
    public const STATUS_PENDING = 4;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'payoutsBalance',
        'pendingPayoutsBalance'
    ];

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class, 'community_id')
            ->withTimestamps();
    }

    public function links(): HasMany
    {
        return $this->hasMany(CommunityLink::class, 'community_id')
            ->orderBy('created_at', 'desc');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CommunityRule::class, 'community_id')
            ->orderBy('order');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(CommunityGroups::class, 'community_id')
            ->with('members');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(CommunityCategory::class, 'community_id')
            ->orderBy('created_at');
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(CommunityExtensions::class, 'community_id')
            ->with('extension')
            ->orderBy('created_at');
    }

    public function medias(): HasMany
    {
        return $this->hasMany(CommunityMedias::class, 'community_id')
            ->with('media')
            ->orderBy('order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoices::class, 'community_id')
            ->orderBy('created_at', 'DESC');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payouts::class, 'community_id')
            ->orderBy('created_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MemberSubscriptionsTransactions::class, 'community_id')
            ->orderBy('created_at');
    }

    public function products(): HasMany
    {
        return $this->hasMany(StripeProducts::class, 'community_id')
            ->orderBy('created_at', 'desc');
    }

    public function price(): HasOne
    {
        return $this->hasOne(StripePrices::class, 'id', 'price_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(CommunityNotificationsSettings::class, 'community_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(CommunityPlan::class, 'community_id');
    }

    public function getPayoutsBalanceAttribute(): float
    {
        $transactions = MemberSubscriptionsTransactions::getPayableForPayout($this->id);

        $pendingPayouts = Payouts::where(['community_id' => $this->id, 'status' => PayoutStatusEnum::STATUS_PENDING])->sum('amount');
        $completePayouts = Payouts::where(['community_id' => $this->id, 'status' => PayoutStatusEnum::STATUS_COMPLETE])->sum('amount');
        $payouts = $pendingPayouts + $completePayouts;

        return max((collect($transactions)->sum('payable') - $payouts) / 100, 0);
    }

    public function getPendingPayoutsBalanceAttribute(): float
    {
        $transactions = MemberSubscriptionsTransactions::getPendingForPayout($this->id);

        return max(collect($transactions)->sum('payable') / 100, 0);
    }

    /**
     * Get community info
     *
     * @param int $communityId
     * @return null | object
     */
    public static function getCommunity(int $communityId): ?object
    {
        $query = Community::query();
        $query->where([
            'id' => $communityId
        ]);

        $query->with('links')
        ->with('categories')
        ->with('medias')
        ->with('price');

        if (auth()->check() === true) {
            $query->with('rules')
            ->with('groups')
            ->with('products')
            ->with('products.prices')
            ->with('extensions');
        }

        return $query->first();
    }

    /**
     * @return Community|null
     */
    public static function getIncubateurCommunity(): ?Community
    {
        return self::where(['url' => 'incubateur'])->first();
    }

    /**
     * @param int $userId
     * @return int
     */
    public static function getActiveCommunityCount(int $userId): int
    {
        return self::where(['user_id' => $userId])
            ->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_SUSPENDED])
            ->count();
    }
}
