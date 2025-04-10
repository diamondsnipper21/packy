<?php

namespace App\Models\Billing;

use App\Models\Members\Subscriptions\MemberSubscriptionsTransactions;
use App\Models\User;
use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payouts extends Model
{
    use BelongsToCommunity;
    use BelongsToUser;

    public $table = 'payouts';

    protected $fillable = [
        'community_id',
        'to',
        'amount',
        'currency',
        'status',
        'period_start',
        'period_end',
        'stripe_transfer_id',
        'completed_at'
    ];

    protected $appends = [
        'stripe_transfer_link'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(MemberSubscriptionsTransactions::class, 'payout_id');
    }

    public function getStripeTransferLinkAttribute(): ?string
    {
        return $this->stripe_transfer_id ?
            'https://dashboard.stripe.com/test/connect/transfers/' . $this->stripe_transfer_id : null;
    }
}
