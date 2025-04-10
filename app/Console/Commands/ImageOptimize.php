<?php

namespace App\Console\Commands;

use App\Models\Medias;
use App\Models\User;
use App\Services\LoggerService;
use App\Services\MediaService;
use Illuminate\Console\Command;

class ImageOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:image-optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize image';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param MediaService $mediaService
     * @return void
     */
    public function handle(MediaService $mediaService): void
    {
        $medias = Medias::where(['type' => Medias::TYPE_IMAGE])->get();
        foreach ($medias as $media) {
            if (!$media->path) {
                continue;
            }

            $result = $mediaService->optimizeMedia($media->path, $media->type);
            if ($result['success']) {
                try {
                    $media->path = $result['mediaPath'];
                    $media->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }

        $users = User::all();
        foreach ($users as $user) {
            if (!$user->photo) {
                continue;
            }

            $result = $mediaService->optimizeMedia($user->photo, Medias::TYPE_IMAGE, 'user_photo');
            if ($result['success']) {
                try {
                    $user->photo = $result['mediaPath'];
                    $user->save();
                } catch (\Exception $e) {
                    LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                }
            }
        }
    }
}
