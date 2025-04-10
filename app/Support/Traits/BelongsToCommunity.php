<?php

namespace App\Support\Traits;

use App\Models\Community;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCommunity
{
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id', 'id');
    }
}