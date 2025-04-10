<?php

namespace App\Enum;

class MemberSubscriptionTransactionStatusEnum
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETE = 'complete';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_REFUNDED = 'refunded';
}
