<?php

namespace App\Exceptions\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailNotificationForJoinRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $communityId;
    private $memberId;
    private $access;
    private $feedback;

    /**
     * @param $communityId
     * @param $memberId
     * @param $access
     * @param $feedback
     * 
     * @return void
     */
    public function __construct(
        $communityId,
        $memberId,
        $access,
        $feedback
    ) {
        $this->communityId = $communityId;
        $this->memberId = $memberId;
        $this->access = $access;
        $this->feedback = $feedback;
    }

    /**
     * @param MailService $mailService
     * @return void
     */
    public function handle(MailService $mailService)
    {
        $mailService->sendEmailNotificationForJoinRequest(
            $this->communityId,
            $this->memberId,
            $this->access,
            $this->feedback
        );
    }
}
