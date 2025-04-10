<?php

namespace App\Http\Controllers\App\Test;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use App\Services\Notifications\Broadcast\NewSubscriptionNotification;

class TestController extends Controller
{
    public function test(MailService $mailService): void
    {
        //$user = User::find(2);
        //new NewSubscriptionNotification(1, $user);

        $mailService->testEmails();
    }
}