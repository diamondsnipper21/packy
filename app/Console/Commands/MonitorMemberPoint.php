<?php

namespace App\Console\Commands;

use App\Services\MemberService;
use Illuminate\Console\Command;

class MonitorMemberPoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:monitor-member-point';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor member point';

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
        $memberService->calculateMembersPoints('-1 day', 'day');
        $memberService->calculateMembersPoints('-1 week', 'week');
        $memberService->calculateMembersPoints('-1 month', 'month');
    }
}
