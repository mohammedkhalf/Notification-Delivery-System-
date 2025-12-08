<?php

namespace App\Application\Services;

use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Jobs\ProcessEventJob;

class EventIngestionService
{
    public function ingest(Event $event): void
    {
        // Dispatch to queue for async processing
        ProcessEventJob::dispatch($event->toArray());
    }

    public function createEventFromArray(array $data): Event
    {
        return Event::create(
            eventType: $data['eventType'],
            payload: $data['payload'] ?? [],
            recipient: $data['recipient'],
            priority: Priority::from($data['priority'] ?? 'normal'),
            timestamp: isset($data['timestamp']) ? \Carbon\Carbon::parse($data['timestamp']) : null,
            id: $data['id'] ?? null
        );
    }

}
