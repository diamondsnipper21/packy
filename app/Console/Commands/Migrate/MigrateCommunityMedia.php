<?php

namespace App\Console\Commands\Migrate;

use App\Services\CommunityService;
use App\Services\MediaService;
use Illuminate\Console\Command;

class MigrateCommunityMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-community-media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate community media data.';

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
     * @param CommunityService $communityService
     * @return void
     */
    public function handle(MediaService $mediaService, CommunityService $communityService): void
    {
        $communityService->migrateCommunityMediaData($mediaService);
    }
}
