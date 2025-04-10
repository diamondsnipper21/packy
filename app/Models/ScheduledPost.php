<?php

namespace App\Models;

use App\Support\Traits\BelongsToMember;
use App\Support\Traits\GenerateUniqueSlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledPost extends Model
{
    use GenerateUniqueSlugTrait;
    use BelongsToMember;

    public $table = 'scheduled_posts';

    protected $fillable = [
        'community_id',
        'classroom_lesson_id',
        'member_id',
        'title',
        'content',
        'path',
        'pinned',
        'category_id',
        'visibility',
        'disable_comment',
        'publish_at',
        'publish_timezone',
        'repeat_end_at',
        'repeat_every',
        'repeat_on',
        'origin_id',
        'send_notification',
        'next_scheduled_at',
    ];

    public const VISIBILITY_PENDING = 0;
    public const VISIBILITY_APPROVED = 1;
    public const VISIBILITY_REFUSED = 2;

    public const DISABLED_COMMENT = 1;
    public const ENABLED_COMMENT = 0;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'firstMedia'
    ];

    public function category()
    {
        return $this->hasOne(CommunityCategory::class, 'id', 'category_id');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Medias::class, 'community_scheduled_posts_medias', 'scheduled_post_id', 'media_id')
            ->orderBy('created_at');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'owner_id', 'id')
            ->where('polls.owner', '=', Poll::OWNER_SCHEDULED_POST)
            ->orderBy('created_at');
    }

    /**
     * Get first media of this post
     *
     * @return object
     */
    public function getFirstMediaAttribute(): ?object
    {
        $postId = $this->id;
        if ($this->origin_id > 0) {
            $postId = $this->origin_id;
        }

        return CommunityScheduledPostsMedias::getFirstMediaByPostId($postId);
    }

    /**
     * @param int $postId
     * @param $publishAtTimestamp
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getOne(int $postId, $publishAtTimestamp)
    {
        $query = self::query()
            ->where('publish_at', gmdate('Y-m-d H:i:s', $publishAtTimestamp))
            ->where(function ($subQuery) use ($postId) {
                return $subQuery
                    ->where(['id' => $postId])
                    ->orWhere(['origin_id' => $postId]);
            });

        return $query->first();
    }

    public static function getScheduledPostDetailById(int $postId): ScheduledPost
    {
        return ScheduledPost::where('id', $postId)
            ->with('medias')
            ->with('category')
            ->with('member')
            ->with('member.user')
            ->with('polls')
            ->first();
    }
}
