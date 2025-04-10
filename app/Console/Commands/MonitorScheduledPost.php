<?php

namespace App\Console\Commands;

use App\Services\PostService;
use Illuminate\Console\Command;

class MonitorScheduledPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:monitor-scheduled-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor scheduled post to generate community post';

    private PostService $postService;

    /**
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        parent::__construct();
        $this->postService = $postService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->postService->processScheduledPost();
    }
}
