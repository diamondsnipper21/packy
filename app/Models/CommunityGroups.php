<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommunityGroups extends Model
{
    use BelongsToCommunity;

    public $table = 'community_groups';

    protected $fillable = [
        'community_id',
        'name',
        'description',
        'publish'
    ];

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(CommunityMember::class, 'community_group_members', 'group_id', 'member_id')
            ->withTimestamps();
    }
}
