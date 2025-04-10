<?php

namespace App\Exceptions\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailNotificationForNewPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $postId;
    private int $memberId;

    /**
     * @param int $postId
     * @param int $memberId
     */
    public function __construct(
        int $postId,
        int $memberId
    ) {
        $this->postId = $postId;
        $this->memberId = $memberId;
    }

    /**
     * @param MailService $mailService
     * @return void
     */
    public function handle(MailService $mailService)
    {
        $response = $mailService->sendEmailNotificationForNewPost($this->postId, $this->memberId);

        \Log::info(['SendEmailNotificationForNewPost@handle $response', $response]);
    }
}
