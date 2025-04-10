<?php

namespace App\Console\Commands;

use App\Services\MemberService;
use Illuminate\Console\Command;

class CleanMemberTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean-member-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean member tags';

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
    public function handle(MemberService $memberService): void
    {
        $memberService->cleanMemberTags();
    }
}
