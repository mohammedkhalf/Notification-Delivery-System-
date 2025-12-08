<?php

namespace App\Jobs;

use App\Application\Services\EventIngestionService;
use App\Application\Services\NotificationDeliveryService;
use App\Application\Services\RoutingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $eventData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        EventIngestionService $eventIngestionService,
        RoutingService $routingService,
        NotificationDeliveryService $deliveryService
    ): void {
        try {
            $event = $eventIngestionService->createEventFromArray($this->eventData);

            // Determine which channels should receive this event
            $channels = $routingService->determineChannels($event);

            if (empty($channels)) {
                Log::warning("No channels found for event", [
                    'event_type' => $event->eventType,
                    'priority' => $event->priority->value,
                ]);
                return;
            }

            // Dispatch delivery jobs for each channel
            foreach ($channels as $channel) {
                DeliverNotificationJob::dispatch($event->toArray(), $channel);
            }

            Log::info("Event processed and dispatched to channels", [
                'event_type' => $event->eventType,
                'channels' => $channels,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process event", [
                'event_data' => $this->eventData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
