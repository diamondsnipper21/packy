<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigestPostsSent extends Model
{
    use BelongsToCommunity;
    use BelongsToMember;

    public $table = 'digest_posts_sent';

    protected $fillable = [
        'community_id',
        'member_id',
        'post_id'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'id', 'post_id');
    }

    /**
     * Get post ids that already sent for popular interval
     *
     * @param int $communityId
     * @param int $memberId
     * @return array
     */
    public static function getSentPostIds(int $communityId, int $memberId): array
    {
        return self::where(['community_id' => $communityId, 'member_id' => $memberId])
            ->pluck('post_id')
            ->toArray();
    }
}
