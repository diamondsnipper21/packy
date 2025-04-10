<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityPostsMedias extends Model
{
    public $table = 'community_posts_medias';

    protected $fillable = [
        'post_id',
        'media_id'
    ];

    public function post(): HasOne
    {
        return $this->hasOne(\App\Models\CommunityPost::class, 'id', 'post_id');
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
        $postMedia = self::where(['post_id' => $postId])
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
                self::where(['post_id' => $ownerId, 'media_id' => $mediaId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            // delete multiple medias
            try {
                self::where(['post_id' => $ownerId])->delete();
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
            $media = new CommunityPostsMedias();
            $media->post_id = $ownerId;
            $media->media_id = $mediaId;
            $media->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
