<?php

namespace App\Support\Traits;

use App\Models\Members\Subscriptions\MemberSubscriptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSubscription
{
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscriptions::class, 'subscription_id', 'id');
    }
}