<?php

namespace App\Http\Controllers;

use App\Application\Services\NotificationStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationStatusController extends Controller
{
    public function __construct(
        private readonly NotificationStatusService $statusService
    ) {}

    /**
     * Get status of a specific notification
     */
    public function show(string $id): JsonResponse
    {
        $status = $this->statusService->getStatus($id);

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Get status of all notifications for an event
     */
    public function byEvent(string $eventId): JsonResponse
    {
        $statuses = $this->statusService->getStatusByEventId($eventId);

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Get failed deliveries
     */
    public function failed(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $failed = $this->statusService->getFailedDeliveries($limit);

        return response()->json([
            'success' => true,
            'data' => $failed,
            'count' => count($failed),
        ]);
    }

    /**
     * Get dead letter notifications
     */
    public function deadLetter(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $deadLetter = $this->statusService->getDeadLetterNotifications($limit);

        return response()->json([
            'success' => true,
            'data' => $deadLetter,
            'count' => count($deadLetter),
        ]);
    }
}
