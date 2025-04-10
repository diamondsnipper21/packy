<?php

namespace App\Models;

use App\Models\Community;
use App\Models\Medias;
use App\Services\LoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityMedias extends Model
{
    public $table = 'community_medias';

    protected $fillable = [
        'community_id',
        'media_id',
        'order'
    ];

    /**
     * @return HasOne
     */
    public function community(): HasOne
    {
        return $this->hasOne(Community::class, 'id', 'community_id');
    }

    /**
     * @return HasOne
     */
    public function media(): HasOne
    {
        return $this->hasOne(Medias::class, 'id', 'media_id');
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
            $existingMedia = self::where(['community_id' => $ownerId, 'media_id' => $mediaId])->first();
            $order = $existingMedia->order ?? 0;

            // delete one media
            try {
                self::where(['community_id' => $ownerId, 'media_id' => $mediaId])->delete();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }

            if ($order > 0) {
                // Update below media's order
                $belowMedias = self::where('community_id', $ownerId)
                ->where('order', '>', $order)
                ->orderBy('order', 'asc')
                ->get();

                if (!empty($belowMedias)) {
                    foreach ($belowMedias as $belowMedia) {
                        try {
                            $belowMedia->order = $belowMedia->order - 1;
                            $belowMedia->save();
                        } catch (\Exception $e) {
                            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                        }
                    }
                }
            }
        } else {
            // delete multiple medias
            try {
                self::where(['community_id' => $ownerId])->delete();
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
     * @param int $order
     * @return array
     */
    public static function createMedia(int $ownerId, int $mediaId, int $order = 0): array
    {
        $existingMedia = self::where(['community_id' => $ownerId, 'media_id' => $mediaId])->first();
        if (!empty($existingMedia)) {
            return ['success' => true];
        }

        if (!$order) {
            $existingMediaCnt = self::where([
                'community_id' => $ownerId
            ])->count();
            $order = $existingMediaCnt + 1;
        }

        $belowMedias = self::where('community_id', $ownerId)
            ->where('order', '>=', $order)
            ->orderBy('order', 'asc')
            ->get();

        try {
            $media = new CommunityMedias();
            $media->community_id = $ownerId;
            $media->media_id = $mediaId;
            $media->order = $order;
            $media->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        // Update below media's order
        if (!empty($belowMedias)) {
            foreach ($belowMedias as $belowMedia) {
                try {
                    $belowMedia->order = $belowMedia->order + 1;
                    $belowMedia->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }

        return ['success' => true];
    }
}
