<?php

namespace App\Models;

use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityChatsMedias extends Model
{
    public $table = 'community_chats_medias';

    protected $fillable = [
        'chat_id',
        'media_id'
    ];

    public function chat(): HasOne
    {
        return $this->hasOne(\App\Models\Chat::class, 'id', 'chat_id');
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
                self::where(['chat_id' => $ownerId, 'media_id' => $mediaId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            // delete multiple medias
            try {
                self::where(['chat_id' => $ownerId])->delete();
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
            $media = new CommunityChatsMedias();
            $media->chat_id = $ownerId;
            $media->media_id = $mediaId;
            $media->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
