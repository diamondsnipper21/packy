<?php

namespace App\Enum;

class PayoutStatusEnum
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETE = 'complete';
    const STATUS_REFUSED = 'refused';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERSED = 'reversed';
}
