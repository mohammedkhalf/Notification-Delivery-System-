<?php

namespace Tests\Unit\Jobs;

use App\Application\Services\EventIngestionService;
use App\Application\Services\NotificationDeliveryService;
use App\Application\Services\RoutingService;
use App\Jobs\DeliverNotificationJob;
use App\Jobs\ProcessEventJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessEventJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_job_processes_event_and_dispatches_delivery_jobs(): void
    {
        $eventData = [
            'eventType' => 'USER_REGISTERED',
            'payload' => ['name' => 'John'],
            'recipient' => 'john@example.com',
            'priority' => 'normal',
        ];

        $mockIngestionService = $this->createMock(EventIngestionService::class);
        $mockRoutingService = $this->createMock(RoutingService::class);
        $mockDeliveryService = $this->createMock(NotificationDeliveryService::class);

        $event = \App\Domain\Events\Event::create(
            eventType: 'USER_REGISTERED',
            payload: ['name' => 'John'],
            recipient: 'john@example.com',
            priority: \App\Domain\Events\Priority::NORMAL
        );

        $mockIngestionService->expects($this->once())
            ->method('createEventFromArray')
            ->with($eventData)
            ->willReturn($event);

        $mockRoutingService->expects($this->once())
            ->method('determineChannels')
            ->with($event)
            ->willReturn(['email', 'sms']);

        $job = new ProcessEventJob($eventData);
        $job->handle($mockIngestionService, $mockRoutingService, $mockDeliveryService);

        Queue::assertPushed(DeliverNotificationJob::class, 2);
    }

    public function test_job_handles_no_channels_gracefully(): void
    {
        $eventData = [
            'eventType' => 'UNKNOWN_EVENT',
            'payload' => [],
            'recipient' => 'test@example.com',
            'priority' => 'normal',
        ];

        $mockIngestionService = $this->createMock(EventIngestionService::class);
        $mockRoutingService = $this->createMock(RoutingService::class);
        $mockDeliveryService = $this->createMock(NotificationDeliveryService::class);

        $event = \App\Domain\Events\Event::create(
            eventType: 'UNKNOWN_EVENT',
            payload: [],
            recipient: 'test@example.com',
            priority: \App\Domain\Events\Priority::NORMAL
        );

        $mockIngestionService->expects($this->once())
            ->method('createEventFromArray')
            ->willReturn($event);

        $mockRoutingService->expects($this->once())
            ->method('determineChannels')
            ->willReturn([]);

        $job = new ProcessEventJob($eventData);
        $job->handle($mockIngestionService, $mockRoutingService, $mockDeliveryService);

        Queue::assertNothingPushed();
    }
}

