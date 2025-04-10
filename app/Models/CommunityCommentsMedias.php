<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityCommentsMedias extends Model
{
    public $table = 'community_comments_medias';

    protected $fillable = [
        'comment_id',
        'media_id'
    ];

    public function comment(): HasOne
    {
        return $this->hasOne(\App\Models\CommunityPostComment::class, 'id', 'comment_id');
    }

    public function media(): HasOne
    {
        return $this->hasOne(\App\Models\Medias::class, 'id', 'media_id');
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
                self::where(['comment_id' => $ownerId, 'media_id' => $mediaId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            // delete multiple medias
            try {
                self::where(['comment_id' => $ownerId])->delete();
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
            $media = new CommunityCommentsMedias();
            $media->comment_id = $ownerId;
            $media->media_id = $mediaId;
            $media->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
