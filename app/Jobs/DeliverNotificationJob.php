<?php

namespace App\Jobs;

use App\Application\Services\EventIngestionService;
use App\Application\Services\NotificationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeliverNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $eventData,
        public string $channel
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        EventIngestionService $eventIngestionService,
        NotificationDeliveryService $deliveryService
    ): void {
        try {
            $event = $eventIngestionService->createEventFromArray($this->eventData);
            $deliveryService->deliver($event, $this->channel);
        } catch (\Exception $e) {
            Log::error("Failed to deliver notification", [
                'event_data' => $this->eventData,
                'channel' => $this->channel,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
