<?php

namespace App\Console\Commands\Migrate;

use App\Services\MemberService;
use Illuminate\Console\Command;

class CalculateInitialUserPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-initial-user-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate initial user\'s points after column is added';

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
     * @param MemberService $memberService
     * @return void
     */
    public function handle(MemberService $memberService): void
    {
        $memberService->calculateMembersPoints('-2 years', 'day');
        $memberService->calculateMembersPoints('-1 week', 'week');
        $memberService->calculateMembersPoints('-1 month', 'month');
    }
}
