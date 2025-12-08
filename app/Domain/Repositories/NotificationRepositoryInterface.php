<?php

namespace App\Domain\Repositories;

use App\Domain\Events\Event;
use App\Domain\Notifications\Notification;
use App\Domain\Notifications\NotificationStatus;

interface NotificationRepositoryInterface
{
    public function create(Event $event, string $channel, NotificationStatus $status): Notification;

    public function save(Notification $notification): void;

    public function findById(string $id): ?Notification;

    public function findByEventId(string $eventId): array;

    public function findFailed(int $limit = 50): array;

    public function findDeadLetter(int $limit = 50): array;

    public function findPendingRetries(int $maxAttempts, int $limit = 50): array;
}
