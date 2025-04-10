<?php

namespace App\Support\Traits;

use App\Models\CommunityMember;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToMember
{
    public function member(): BelongsTo
    {
        return $this->belongsTo(CommunityMember::class, 'member_id', 'id');
    }
}