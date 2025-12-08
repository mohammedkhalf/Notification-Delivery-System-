<?php

namespace App\Application\Services;

use App\Domain\Events\Event;
use App\Domain\Routing\RoutingEngineInterface;

class RoutingService
{
    public function __construct(
        private readonly RoutingEngineInterface $routingEngine
    ) {}

    /**
     * Determine which channels should receive this event
     *
     * @return string[]
     */
    public function determineChannels(Event $event): array
    {
        return $this->routingEngine->route($event);
    }

}
