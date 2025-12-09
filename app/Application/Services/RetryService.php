<?php

namespace App\Application\Services;

use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Jobs\RetryNotificationJob;
class RetryService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
        private int $maxAttempts,
        private readonly int $baseDelaySeconds = 60
    ) {}

    public function retryFailedNotifications(int $limit = 50): void
    {
        $notifications = $this->notificationRepository->findPendingRetries($this->maxAttempts, $limit);

        foreach ($notifications as $notification) {
            if ($notification->shouldMoveToDeadLetter($this->maxAttempts)) {
                $notification->markAsDeadLetter();
                $this->notificationRepository->save($notification);
                Log::warning("Notification moved to dead letter", [
                    'notification_id' => $notification->id,
                    'attempts' => $notification->attemptCount,
                ]);
                continue;
            }

            if ($notification->shouldRetry($this->maxAttempts)) {
                $this->scheduleRetry($notification);
            }
        }
    }

    private function scheduleRetry($notification): void
    {
        $delay = $this->calculateDelay($notification->attemptCount);

        $notification->incrementAttempt();
        $notification->markAsPending();
        $this->notificationRepository->save($notification);

        // Dispatch to queue with delay
        RetryNotificationJob::dispatch($notification->id)
            ->delay(now()->addSeconds($delay));

        Log::info("Notification retry scheduled", [
            'notification_id' => $notification->id,
            'attempt' => $notification->attemptCount,
            'delay_seconds' => $delay,
        ]);
    }

    private function calculateDelay(int $attemptCount): int
    {
        // Exponential backoff: baseDelay * 2^(attemptCount - 1)
        return $this->baseDelaySeconds * pow(2, $attemptCount - 1);
    }
}
