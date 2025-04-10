<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class SendUnread extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:send-unread {interval}';

    /**
     * @var string
     */
    protected $description = 'Send email with unread notifications';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param CommunityService $communityService
     * @return void
     */
    public function handle(CommunityService $communityService): void
    {
        $communityService->sendUnreadInterval($this->argument('interval'));
    }
}
