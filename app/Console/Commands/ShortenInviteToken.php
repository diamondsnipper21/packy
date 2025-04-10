<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class ShortenInviteToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:shorten-invite-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make invite token short.';

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
        $communityService->shortenInviteToken();
    }
}
