<?php

namespace App\Domain\Notifications;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case DEAD_LETTER = 'dead_letter';
}

