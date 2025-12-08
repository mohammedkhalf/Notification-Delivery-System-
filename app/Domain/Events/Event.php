<?php

namespace App\Domain\Events;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Event
{
    public function __construct(
        public readonly string $eventType,
        public readonly array $payload,
        public readonly string $recipient,
        public readonly Carbon $timestamp,
        public readonly Priority $priority,
        public readonly ?string $id = null
    ) {
    }

    public static function create(
        string $eventType,
        array $payload,
        string $recipient,
        Priority $priority,
        ?Carbon $timestamp = null,
        ?string $id = null
    ): self {
        return new self(
            eventType: $eventType,
            payload: $payload,
            recipient: $recipient,
            timestamp: $timestamp ?? Carbon::now(),
            priority: $priority,
            id: $id ?? (string) Str::uuid()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->eventType,
            'payload' => $this->payload,
            'recipient' => $this->recipient,
            'timestamp' => $this->timestamp->toIso8601String(),
            'priority' => $this->priority->value,
        ];
    }
}

