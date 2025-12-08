<?php

namespace App\Domain\Notifications;

use App\Domain\Events\Event;
use Carbon\Carbon;

class Notification
{
    public function __construct(
        public readonly string    $id,
        public readonly Event     $event,
        public readonly string    $channel,
        public NotificationStatus $status,
        public readonly Carbon    $createdAt,
        public ?Carbon            $deliveredAt = null,
        public int                $attemptCount = 0,
        public ?Carbon            $lastAttemptAt = null,
        public ?string            $lastFailureReason = null
    )
    {
    }

    public function markAsDelivered(): void
    {
        $this->status = NotificationStatus::DELIVERED;
        $this->deliveredAt = Carbon::now();
    }

    public function markAsFailed(string $reason): void
    {
        $this->status = NotificationStatus::FAILED;
        $this->lastFailureReason = $reason;
        $this->lastAttemptAt = Carbon::now();
        $this->attemptCount++;
    }

    public function markAsPending(): void
    {
        $this->status = NotificationStatus::PENDING;
    }

    public function markAsProcessing(): void
    {
        $this->status = NotificationStatus::PROCESSING;
    }

    public function markAsDeadLetter(): void
    {
        $this->status = NotificationStatus::DEAD_LETTER;
    }

    public function incrementAttempt(): void
    {
        $this->attemptCount++;
        $this->lastAttemptAt = Carbon::now();
    }

    public function shouldRetry(int $maxAttempts): bool
    {
        return $this->status === NotificationStatus::FAILED
            && $this->attemptCount < $maxAttempts;
    }

    public function shouldMoveToDeadLetter(int $maxAttempts): bool
    {
        return $this->status === NotificationStatus::FAILED
            && $this->attemptCount >= $maxAttempts;
    }
}

