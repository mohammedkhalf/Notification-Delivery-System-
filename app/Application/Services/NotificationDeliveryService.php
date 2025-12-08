<?php

namespace App\Application\Services;

use App\Domain\Channels\ChannelInterface;
use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use App\Domain\Notifications\Notification;
use App\Domain\Notifications\NotificationStatus;
use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Log;

class NotificationDeliveryService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly array $channels
    ) {}

    public function deliver(Event $event, string $channelName): Notification
    {
        $channel = $this->getChannel($channelName);

        $notification = $this->notificationRepository->create(
            event: $event,
            channel: $channelName,
            status: NotificationStatus::PENDING
        );

        try {
            $notification->markAsProcessing();
            $this->notificationRepository->save($notification);

            $formattedMessage = $channel->formatMessage($event);
            $result = $channel->send($event, $formattedMessage);

            if ($result->success) {
                $notification->markAsDelivered();
                Log::info("Notification delivered", [
                    'notification_id' => $notification->id,
                    'channel' => $channelName,
                    'event_type' => $event->eventType,
                ]);
            } else {
                $notification->markAsFailed($result->error ?? 'Unknown error');
                Log::warning("Notification delivery failed", [
                    'notification_id' => $notification->id,
                    'channel' => $channelName,
                    'error' => $result->error,
                ]);
            }
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("Notification delivery exception", [
                'notification_id' => $notification->id,
                'channel' => $channelName,
                'exception' => $e->getMessage(),
            ]);
        }

        $this->notificationRepository->save($notification);

        return $notification;
    }

    private function getChannel(string $channelName): ChannelInterface
    {
        if (!isset($this->channels[$channelName])) {
            throw new \InvalidArgumentException("Channel '{$channelName}' not found");
        }

        return $this->channels[$channelName];
    }



}
