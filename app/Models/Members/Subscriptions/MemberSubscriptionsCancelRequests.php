<?php

namespace App\Models\Members\Subscriptions;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use App\Support\Traits\BelongsToSubscription;
use Illuminate\Database\Eloquent\Model;

class MemberSubscriptionsCancelRequests extends Model
{
    use BelongsToCommunity;
    use BelongsToSubscription;
    use BelongsToMember;

    public $table = 'community_member_subscriptions_cancel_requests';

    protected $fillable = [
        'community_id',
        'member_id',
        'subscription_id',
    ];
}
