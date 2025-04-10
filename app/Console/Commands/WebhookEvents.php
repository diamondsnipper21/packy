<?php

namespace App\Console\Commands;

use App\Services\WebhookService;
use Illuminate\Console\Command;

class WebhookEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:webhook-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Treat webhook events';

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
     * @param WebhookService $webhookService
     * @return void
     */
    public function handle(WebhookService $webhookService): void
    {
        $webhookService->processWebhooks();
    }
}
