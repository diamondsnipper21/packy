<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityScheduledPostsMedias extends Model
{
    public $table = 'community_scheduled_posts_medias';

    protected $fillable = [
        'scheduled_post_id',
        'media_id'
    ];

    public function post(): HasOne
    {
        return $this->hasOne(\App\Models\ScheduledPost::class, 'id', 'scheduled_post_id');
    }

    public function media(): HasOne
    {
        return $this->hasOne(\App\Models\Medias::class, 'id', 'media_id');
    }

    /**
     * Get first media of community post
     *
     * @param int $postId
     */
    public static function getFirstMediaByPostId(int $postId): ?object
    {
        $firstMedia = null;
        $postMedia = self::where(['scheduled_post_id' => $postId])
            ->with('media')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!empty($postMedia) && !empty($postMedia->media)) {
            $firstMedia = $postMedia->media;
        }

        return $firstMedia;
    }

    /**
     * @param int $ownerId
     * @param int $mediaId
     * @return array
     */
    public static function deleteMedia(int $ownerId, int $mediaId): array
    {
        $response = ['success' => true];
        if ($mediaId > 0) {
            // delete one media
            try {
                self::where(['scheduled_post_id' => $ownerId, 'media_id' => $mediaId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            // delete multiple medias
            try {
                self::where(['scheduled_post_id' => $ownerId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $response;
    }

    /**
     * @param int $ownerId
     * @param int $mediaId
     * @return array
     */
    public static function createMedia(int $ownerId, int $mediaId): array
    {
        try {
            $media = new CommunityScheduledPostsMedias();
            $media->scheduled_post_id = $ownerId;
            $media->media_id = $mediaId;
            $media->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
