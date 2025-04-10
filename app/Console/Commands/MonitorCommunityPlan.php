<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class MonitorCommunityPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:community-plans-renewal-trial-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email reminder to users 3 days before trial renewals';

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
     * @param CommunityService $communityService
     * @return void
     */
    public function handle(CommunityService $communityService): void
    {
        $communityService->sendReminderEmailForTrialPlan(3);
    }
}
