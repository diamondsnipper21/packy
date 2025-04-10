<?php

namespace App\Enum;

class QueueEnum
{
    public const QUEUE_DEFAULT = 'default';
    public const QUEUE_EMAIL_FOR_NEW_POST = 'send-email-for-new-post';
    public const QUEUE_EMAIL_FOR_JOIN_REQUEST = 'send-email-for-join-request';
    public const QUEUE_AUTO_DM_FOR_JOIN_REQUEST = 'send-auto-dm-for-join-request';
}
