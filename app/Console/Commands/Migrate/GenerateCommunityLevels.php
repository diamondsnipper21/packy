<?php

namespace App\Console\Commands\Migrate;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Services\LevelService;

use Illuminate\Console\Command;

class GenerateCommunityLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-community-level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate community levels';

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
     * @return void
     */
    public function handle(LevelService $levelService): void
    {
        CommunityMember::where('level', 0)->update(['level' => 1]);

        $communityIds = Community::all()->pluck('id')->toArray();
        foreach ($communityIds as $id) {
            $levelService->generateLevels($id);
        }
    }
}
