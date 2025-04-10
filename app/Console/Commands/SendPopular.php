<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class SendPopular extends Command
{
    /**
     * @var string
     */
    protected $signature = 'command:send-popular {interval}';

    /**
     * @var string
     */
    protected $description = 'Send email notification with digest';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle(CommunityService $communityService): void
    {
        $communityService->sendPopularInterval($this->argument('interval'));
    }
}
