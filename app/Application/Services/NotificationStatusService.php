<?php

namespace App\Application\Services;

use App\Domain\Repositories\NotificationRepositoryInterface;

class NotificationStatusService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository
    ) {}

    public function getStatus(string $notificationId): ?array
    {
        $notification = $this->notificationRepository->findById($notificationId);

        if (!$notification) {
            return null;
        }

        return $this->formatStatus($notification);
    }

    public function getStatusByEventId(string $eventId): array
    {
        $notifications = $this->notificationRepository->findByEventId($eventId);

        return array_map(fn($n) => $this->formatStatus($n), $notifications);
    }

    public function getFailedDeliveries(int $limit = 50): array
    {
        $notifications = $this->notificationRepository->findFailed($limit);

        return array_map(fn($n) => $this->formatStatus($n), $notifications);
    }

    public function getDeadLetterNotifications(int $limit = 50): array
    {
        $notifications = $this->notificationRepository->findDeadLetter($limit);

        return array_map(fn($n) => $this->formatStatus($n), $notifications);
    }

    private function formatStatus($notification): array
    {
        return [
            'id' => $notification->id,
            'event_type' => $notification->event->eventType,
            'channel' => $notification->channel,
            'status' => $notification->status->value,
            'recipient' => $notification->event->recipient,
            'attempt_count' => $notification->attemptCount,
            'last_attempt_at' => $notification->lastAttemptAt?->toIso8601String(),
            'last_failure_reason' => $notification->lastFailureReason,
            'created_at' => $notification->createdAt->toIso8601String(),
            'delivered_at' => $notification->deliveredAt?->toIso8601String(),
        ];
    }

}
