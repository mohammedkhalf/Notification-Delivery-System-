<?php

namespace Tests\Feature;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Domain\Notifications\NotificationStatus;
use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_notification_status(): void
    {
        $repository = app(NotificationRepositoryInterface::class);

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $notification = $repository->create(
            event: $event,
            channel: 'email',
            status: NotificationStatus::DELIVERED
        );
        $notification->markAsDelivered();
        $repository->save($notification);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'channel' => 'email',
                    'status' => 'delivered',
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_notification(): void
    {
        $response = $this->getJson('/api/notifications/nonexistent-id');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_get_notifications_by_event_id(): void
    {
        $repository = app(NotificationRepositoryInterface::class);

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $notification1 = $repository->create($event, 'email', NotificationStatus::PENDING);
        $notification2 = $repository->create($event, 'sms', NotificationStatus::PENDING);

        $response = $this->getJson("/api/notifications/event/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_failed_notifications(): void
    {
        $repository = app(NotificationRepositoryInterface::class);

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $notification = $repository->create($event, 'email', NotificationStatus::FAILED);
        $notification->markAsFailed('Test error');
        $repository->save($notification);

        $response = $this->getJson('/api/notifications/failed');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_dead_letter_notifications(): void
    {
        $repository = app(NotificationRepositoryInterface::class);

        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $notification = $repository->create($event, 'email', NotificationStatus::FAILED);
        $notification->attemptCount = 3;
        $notification->markAsDeadLetter();
        $repository->save($notification);

        $response = $this->getJson('/api/notifications/dead-letter');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}

