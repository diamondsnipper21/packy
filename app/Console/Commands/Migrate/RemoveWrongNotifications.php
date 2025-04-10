<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class RemoveWrongNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete-wrong-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handler for removing wrong notifications.';

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
     * @param NotificationService $notificationService
     * @return void
     */
    public function handle(NotificationService $notificationService): void
    {
        $notificationService->removeWrongNotifications();
    }
}
