<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\CommunityChatsMedias;
use App\Models\CommunityMedias;
use App\Models\CommunityPostsMedias;
use App\Models\CommunityScheduledPostsMedias;
use App\Models\CommunityCommentsMedias;
use App\Models\Medias;
use App\Models\ResourceFile;
use Gumlet\ImageResize;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class MediaService extends Service
{
    public const AMAZON_S3_PATH = 'https://wolfeo.s3.eu-west-1.amazonaws.com/';
    public const AVATAR_WIDTH = 200;

    private StorageService $storageService;

    /**
     * @param StorageService $storageService
     */
    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Uploads a media file to Amazon AWS
     *
     * @param object $file file object
     * @param string $from module name
     * @param string $fileType
     * @param int | null $size
     * @return array
     */
    public function uploadMediaToAws(object $file, string $from, string $fileType, ?int $size = null): array
    {
        ini_set('memory_limit', '-1');

        $extension = $file->getClientOriginalExtension();
        $type = FileHelper::getExtensionType($extension);

        $invalidType = false;
        if ($from === 'settingModal' && !in_array($type, [Medias::TYPE_IMAGE, Medias::TYPE_VIDEO])) {
            $invalidType = true;
        }

        if ($fileType === 'community_media' && !in_array($type, [Medias::TYPE_IMAGE, Medias::TYPE_VIDEO])) {
            $invalidType = true;
        }

        if ($invalidType) {
            return [
                'success' => false,
                'message' => __('This media file type is not supported.')
            ];
        }

        $filename = FileHelper::generateFileName($extension);
        $uuid = str_replace('', '.' . $extension, $filename);

        $uploadFileToAws = $this->storageService->uploadFileToAws('packy_resources/' . $filename, $file->getPathName(), $size, 'public');
        if ($uploadFileToAws['success'] !== true) {
            return ['success' => false, 'message' => $uploadFileToAws['message']];
        }

        if ($type === Medias::TYPE_IMAGE && $extension !== 'gif' && $fileType !== 'community_media') {
            $this->optimizeMedia($uploadFileToAws['awsPath'], $type, $fileType);
        }

        try {
            $resourceFile = new ResourceFile();
            $resourceFile->uuid = $uuid;
            $resourceFile->mime_type = $file->getClientMimeType();
            $resourceFile->size = $file->getSize();
            $resourceFile->type = $type;
            $resourceFile->url = $uploadFileToAws['awsPath'];
            $resourceFile->file_name = $file->getClientOriginalName();
            $resourceFile->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return [
            'success' => true,
            'path' => $resourceFile->resource_url(),
            'type' => $type,
            'ext' => strtolower($extension),
            'size' => $size,
            'filename' => $filename
        ];
    }

    /**
     * Optimize media
     *
     * @param string $mediaPath
     * @param string $type
     * @param string $fileType
     * @return array
     */
    public function optimizeMedia(string $mediaPath, string $type, string $fileType = ''): array
    {
        if (!$mediaPath || !in_array($type, [Medias::TYPE_IMAGE, Medias::TYPE_VIDEO])) {
            return ['success' => false];
        }

        $filename = str_replace(' ', '_', basename($mediaPath));
        $constantPath = str_replace(' ', '_', $mediaPath);

        $tempFileDir = storage_path() . '/app/upload-temp';
        $tempFilePath = $tempFileDir . '/' . $filename;
        if (!$tempFilePath) {
            return ['success' => false];
        }

        if (!file_exists($tempFileDir)) {
            mkdir($tempFileDir, 0777, true);
        }

        if (is_file($tempFilePath)) {
            unlink($tempFilePath);
        }

        $width = 800;
        if ($fileType === 'user_photo') {
            $width = self::AVATAR_WIDTH;
        }

        if ($type === Medias::TYPE_IMAGE) {
            $this->optimizeImage($constantPath, $tempFilePath, $width);
        } elseif ($type === Medias::TYPE_VIDEO) {
            $this->optimizeVideo($constantPath, $tempFilePath);
        }

        $s3Path = '';
        if (str_contains($constantPath, self::AMAZON_S3_PATH)) {
            $s3Path = str_replace(self::AMAZON_S3_PATH, '', $constantPath);
        } else {
            $pathArray = parse_url($constantPath);
            if (!empty($pathArray['path'])) {
                $s3Path = ltrim($pathArray['path'], '/');
            }
        }

        if (!$s3Path) {
            return ['success' => false];
        }

        try {
            $size = filesize($tempFilePath);
        } catch (\Exception $e) {
            $size = null;
        }

        $uploadFileToAws = $this->storageService->uploadFileToAws($s3Path, $tempFilePath, $size, 'public');
        if ($uploadFileToAws['success'] !== true) {
            return ['success' => false, 'message' => $uploadFileToAws['message']];
        }

        // delete temp media file
        if (is_file($tempFilePath)) {
            unlink($tempFilePath);
        }

        return [
            'success' => true,
            'mediaPath' => $uploadFileToAws['awsPath']
        ];
    }

    /**
     * Optimizes image size
     *
     * @param string $constantPath
     * @param string $tempFilePath
     * @param int $width
     */
    private function optimizeImage(string $constantPath, string $tempFilePath, int $width = 800): void
    {
        try {
            if (file_put_contents($tempFilePath, file_get_contents($constantPath))) {
                ImageOptimizer::optimize($tempFilePath);
                $image = new ImageResize($tempFilePath);

                // if image exceeds target $width, we resize it.
                if ($image->getSourceWidth() > $width) {
                    $image->resizeToWidth($width);
                }

                $image->save($tempFilePath);
            }
        } catch (\Exception $e) {
            \Log::error(['optimizeImage exception', $e->getMessage()]);
        }
    }

    /**
     * @param array $arrayInsert
     * @param string $owner
     * @param int $ownerId
     * @param int $order
     * @return array
     */
    public function createMedia(array $arrayInsert, string $owner, int $ownerId, int $order = 0): array
    {
        extract($arrayInsert);

        try {
            $media = Medias::firstOrCreate([
                'type' => $type,
                'path' => $path,
                'ext' => $ext,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $response = ['success' => true];

        // @todo - use CommunityMediaStrategies here
        if ($owner === Medias::OWNER_COMMUNITY) {
            $response = CommunityMedias::createMedia($ownerId, $media->id, $order);
        } else if ($owner === Medias::OWNER_POST) {
            $response = CommunityPostsMedias::createMedia($ownerId, $media->id);
        } else if ($owner === Medias::OWNER_SCHEDULED_POST) {
            $response = CommunityScheduledPostsMedias::createMedia($ownerId, $media->id);
        } else if ($owner === Medias::OWNER_COMMENT) {
            $response = CommunityCommentsMedias::createMedia($ownerId, $media->id);
        } else if ($owner === Medias::OWNER_CHAT) {
            $response = CommunityChatsMedias::createMedia($ownerId, $media->id);
        }

        return $response;
    }

    /**
     * @param string $owner
     * @param int $ownerId
     * @param int $mediaId
     * @return array
     */
    public function deleteMedia(string $owner, int $ownerId, int $mediaId = 0): array
    {
        $response = ['success' => false];

        // @todo - use CommunityMediaStrategies here
        if ($owner === Medias::OWNER_COMMUNITY) {
            $response = CommunityMedias::deleteMedia($ownerId, $mediaId);
        } else if ($owner === Medias::OWNER_POST) {
            $response = CommunityPostsMedias::deleteMedia($ownerId, $mediaId);
        } else if ($owner === Medias::OWNER_SCHEDULED_POST) {
            $response = CommunityScheduledPostsMedias::deleteMedia($ownerId, $mediaId);
        } else if ($owner === Medias::OWNER_COMMENT) {
            $response = CommunityCommentsMedias::deleteMedia($ownerId, $mediaId);
        } else if ($owner === Medias::OWNER_CHAT) {
            $response = CommunityChatsMedias::deleteMedia($ownerId, $mediaId);
        }

        return $response;
    }

    /**
     * @param string $path
     * @param string $type
     * @param string $owner
     * @param int $ownerId
     * @param int $order
     * @return array
     */
    public function createNewMedia(string $path, string $type, string $owner, int $ownerId, int $order = 0): array
    {
        $filename = str_replace(' ', '_', basename($path));

        $media = [
            'type' => $type,
            'path' => $path,
            'ext' => pathinfo($filename, PATHINFO_EXTENSION),
            'filename' => $filename
        ];

        return $this->createMedia($media, $owner, $ownerId, $order);
    }

    /**
     * Optimizes video size
     *
     * @param string $constantPath
     * @param string $tempFilePath
     */
    private function optimizeVideo(string $constantPath, string $tempFilePath): void
    {
        $cmd = 'ffmpeg -i ' . $constantPath . ' -vcodec libx264 -crf 24 ' . $tempFilePath;
        exec($cmd);
    }

    /**
     * @param int $communityId
     * @param int $fromId
     * @param int $toId
     * @return array
     */
    public function changeMediaOrder(int $communityId, int $fromId, int $toId): array
    {
        $from = CommunityMedias::where(['community_id' => $communityId, 'id' => $fromId])->first();
        if (!$from) {
            return $this->fail(__('Media not found.'));
        }

        $to = CommunityMedias::where(['community_id' => $communityId, 'id' => $toId])->first();
        if (!$to) {
            return $this->fail(__('Media not found.'));
        }

        $fromOrder = $from->order ?? 0;
        $toOrder = $to->order ?? 0;

        DB::beginTransaction();

        try {
            $from->order = $toOrder;
            $from->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            DB::rollBack();
            return $this->fail(__('Failed to update media order (1).'));
        }

        try {
            $to->order = $fromOrder;
            $to->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            DB::rollBack();
            return $this->fail(__('Failed to update media order (2).'));
        }

        DB::commit();

        return $this->success();
    }
}
