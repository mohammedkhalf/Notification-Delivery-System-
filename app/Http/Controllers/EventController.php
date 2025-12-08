<?php

namespace App\Http\Controllers;

use App\Application\Services\EventIngestionService;
use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function __construct(
        private readonly EventIngestionService $eventIngestionService
    ) {}

    /**
     * Receive and process an event
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'eventType' => 'required|string',
            'payload' => 'nullable|array',
            'recipient' => 'required|string',
            'timestamp' => 'nullable|date',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::create(
                eventType: $request->input('eventType'),
                payload: $request->input('payload', []),
                recipient: $request->input('recipient'),
                priority: Priority::from($request->input('priority', 'normal')),
                timestamp: $request->has('timestamp') ? \Carbon\Carbon::parse($request->input('timestamp')) : null
            );

            $this->eventIngestionService->ingest($event);

            return response()->json([
                'success' => true,
                'message' => 'Event received and queued for processing',
                'event' => $event->toArray(),
            ], 202);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process event',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
