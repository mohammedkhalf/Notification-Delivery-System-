Summary
Built a Laravel notification routing and delivery system using Domain-Driven Design (DDD). Implemented:
Architecture layers
Domain layer — Core business logic:
Event and Notification entities
Priority and NotificationStatus value objects
Interfaces for channels, routing, and repositories
Application layer — Services:
EventIngestionService — Receives and validates events
RoutingService — Determines target channels
NotificationDeliveryService — Delivers notifications
NotificationStatusService — Status queries
RetryService — Retry logic with exponential backoff
Infrastructure layer — Implementations:
NotificationRepository — Database persistence
ConfigBasedRoutingEngine — Rule-based routing
Four channels: Email, SMS, Push, Webhook
Presentation layer — API:
REST endpoints for event ingestion and status queries
Controllers with validation
Features
Event ingestion via POST /api/events
Configurable routing rules (event type and/or priority)
Multiple channels (Email, SMS, Push, Webhook)
Retry mechanism with exponential backoff
Dead letter queue for permanently failed notifications
Status API endpoints
Asynchronous processing via Laravel queues
Extensible architecture for adding new channels
