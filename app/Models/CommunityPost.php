<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use App\Support\Traits\BelongsToMember;
use App\Support\Traits\GenerateUniqueSlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    use GenerateUniqueSlugTrait;
    use BelongsToMember;
    use BelongsToCommunity;

    public $table = 'community_posts';

    protected $fillable = [
        'community_id',
        'member_id',
        'title',
        'content',
        'path',
        'pinned',
        'category_id',
        'likes',
        'visibility',
        'disable_comment'
    ];

    public const CATEGORY_GENERAL = 'general';
    public const CATEGORY_NEW = 'new';
    public const CATEGORY_SCALING = 'scaling';
    public const CATEGORY_WINS = 'wins';
    public const CATEGORY_TRAFFIC = 'traffic';
    public const CATEGORY_FUNNELS = 'funnels';
    public const CATEGORY_SALES = 'sales';
    public const CATEGORY_MINDSET = 'mindset';
    public const CATEGORY_PRODUCTIVITY = 'productivity';
    public const CATEGORY_OFFERS = 'offers';

    public const VISIBILITY_PENDING = 0;
    public const VISIBILITY_APPROVED = 1;
    public const VISIBILITY_REFUSED = 2;

    public const DISABLED_COMMENT = 1;
    public const ENABLED_COMMENT = 0;

    public const ACTION_PIN_TO_PAGE = 'pin_to_page';
    public const ACTION_UNPIN_FROM_PAGE = 'unpin_from_page';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'editable',
        'commentsCount',
        'firstMedia',
        'pollVotedMemberCount'
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class, 'post_id', 'id')
            ->with('member')
            ->with('member.user')
            ->with('member.groups')
            ->orderBy('created_at');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Medias::class, 'community_posts_medias', 'post_id', 'media_id')
            ->orderBy('created_at');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'owner_id', 'id')
            ->where('polls.owner', '=', Poll::OWNER_POST)
            ->orderBy('created_at');
    }

    /**
     * Get editable status from diff time
     *
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        $editable = false;

        $createdAtTimestamp = strtotime($this->created_at);
        $currentTimestamp = strtotime(date('Y-m-d H:i:s'));

        // Formulate the Difference between two dates
        $diff = abs($currentTimestamp - $createdAtTimestamp);

        if ($diff < 3600) {
            $editable = true;
        }

        return $editable;
    }

    /**
     * Get count of comments
     *
     * @return int
     */
    public function getCommentsCountAttribute(): int
    {
        return CommunityPostComment::getCountOfCommentsByPostId($this->id);
    }

    /**
     * Get first media of this post
     *
     * @return object
     */
    public function getFirstMediaAttribute(): ?object
    {
        return CommunityPostsMedias::getFirstMediaByPostId($this->id);
    }

    /**
     * Get count of poll voted members
     *
     * @return int
     */
    public function getPollVotedMemberCountAttribute(): int
    {
        return Poll::getCountOfPollVotedMemberByPostId($this->id);
    }

    public function category()
    {
        return $this->hasOne(CommunityCategory::class, 'id', 'category_id');
    }

    /**
     * Get post ids with community id
     *
     * @param int $communityId
     * @return array
     */
    public static function getPostIds(int $communityId): array
    {
        return self::where(['community_id' => $communityId])->pluck('id')->toArray();
    }

    /**
     * Get post ids with comments
     *
     * @param int $communityId
     * @return array
     */
    public static function getCommentsPostIds(int $communityId): array
    {
        $postIds = self::getPostIds($communityId);
        $commentPostIds = CommunityPostComment::whereIn('post_id', $postIds)->pluck('post_id')->toArray();

        return array_unique($commentPostIds);
    }

    /**
     * @param int $communityId
     * @param string|null $timeLimit
     * @return Collection
     */
    public static function getPosts(int $communityId, string $timeLimit = null): Collection
    {
        $query = self::query()->where(['community_id' => $communityId]);
        if ($timeLimit) {
            $query->where('created_at', '>=', $timeLimit);
        }

        return $query->whereNotIn('visibility', [CommunityPost::VISIBILITY_PENDING, CommunityPost::VISIBILITY_REFUSED])
            ->with('member')
            ->with('member.user')
            ->with('member.groups')
            ->orderBy('pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param string $path
     * @return CommunityPost|null
     */
    public static function getPostDetailByUrl(string $path): ?CommunityPost
    {
        return self::where('path', $path)
            ->with('category')
            ->with('comments')
            ->with('medias')
            ->with('member')
            ->with('member.user')
            ->with('member.groups')
            ->with('polls')
            ->first();
    }
}
