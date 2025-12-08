<?php

namespace App\Jobs;

use App\Application\Services\NotificationDeliveryService;
use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $notificationId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        NotificationRepositoryInterface $notificationRepository,
        NotificationDeliveryService $deliveryService
    ): void {
        $notification = $notificationRepository->findById($this->notificationId);

        if (!$notification) {
            Log::warning("Notification not found for retry", [
                'notification_id' => $this->notificationId,
            ]);
            return;
        }

        try {
            $deliveryService->deliver($notification->event, $notification->channel);
        } catch (\Exception $e) {
            Log::error("Retry failed", [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
