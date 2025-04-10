<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use App\Models\Community;
use App\Services\ExtensionService;

class SendAutoDmToNewMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-auto-dm-to-new-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Auto DM to new joined members';

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
     * Execute the console command.
     *
     * @param ExtensionService $extensionService
     * @return void
     */
    public function handle(ExtensionService $extensionService): void
    {
        $communityIds = Community::all()->pluck('id')->toArray();
        foreach ($communityIds as $communityId) {
            $extensionService->sendAutoDm($communityId, ExtensionService::FOR_COMMUNITY);
        }
    }
}
