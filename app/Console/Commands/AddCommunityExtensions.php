<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Services\ExtensionService;
use Illuminate\Console\Command;

class AddCommunityExtensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add-community-extensions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add extensions to community';

    /**
     * Execute the console command.
     *
     * @param ExtensionService $extensionService
     * @return void
     */
    public function handle(ExtensionService $extensionService): void
    {
        $communityIds = Community::all()->pluck('id')->toArray();
        foreach ($communityIds as $communityId) {
            $extensionService->generateExtensions($communityId);
        }
    }
}
