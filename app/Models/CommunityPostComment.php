<?php

namespace App\Models;

use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPostComment extends Model
{
    use BelongsToMember;

    public $table = 'community_post_comments';

    protected $fillable = [
        'post_id',
        'member_id',
        'content',
        'path',
        'likes',
        'parent_id'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'editable',
        'medias',
    ];

    public function post(): BelongsTo
    {
        return $this->BelongsTo(CommunityPost::class, 'post_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class, 'parent_id')
            ->orderBy('created_at');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Medias::class, 'community_comments_medias', 'comment_id', 'media_id')
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
     * Get medias
     *
     * @return object
     */
    public function getMediasAttribute(): object
    {
        return $this->medias()->get();
    }

    /**
     * Get comments of community post
     *
     * @param int $postId
     */
    public static function getCommentsByPostId(int $postId): object
    {
        $commentsQuery = CommunityPostComment::where([
            'post_id' => $postId
        ]);

        return $commentsQuery->with('member')->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get count of comments of community post
     *
     * @param int $postId
     * @return int
     */
    public static function getCountOfCommentsByPostId(int $postId): int
    {
        $count = 0;

        $commentsQuery = CommunityPostComment::where([
            'post_id' => $postId
        ]);
        if ($commentsQuery->count() > 0) {
            $count = $commentsQuery->count();
        }

        return $count;
    }

    /**
     * Get parent owner id
     *
     * @param CommunityPostComment $comment
     * @return int
     */
    public static function getParentOwnerMemberId(CommunityPostComment $comment): int
    {
        if ($comment->parent_id) {
            $parent = self::where([
                'post_id' => $comment->post_id,
                'id' => $comment->parent_id
            ])->first();
        } else {
            $parent = CommunityPost::find($comment->post_id);
        }

        return $parent->member_id ?? 0;
    }

    public static function getCommentDetailById(int $id): CommunityPostComment
    {
        return CommunityPostComment::where('id', $id)
            ->with('member')
            ->with('member.user')
            ->with('member.groups')
            ->first();
    }
}
