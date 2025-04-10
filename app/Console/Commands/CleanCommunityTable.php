<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class CleanCommunityTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean-community-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean community table';

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
     * @todo - test
     * Execute the console command.
     */
    public function handle(CommunityService $communityService): void
    {
        $communityService->cleanCommunityTable();
    }
}
