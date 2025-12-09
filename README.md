# Notification Routing & Delivery System

A Laravel-based notification system built with Domain-Driven Design (DDD) architecture.

## Architecture Overview

The system is organized into four main layers following DDD principles:

### 1. Domain Layer (`app/Domain/`)
- **Entities**: `Event`, `Notification`
- **Value Objects**: `Priority`, `NotificationStatus`
- **Interfaces**: `ChannelInterface`, `RoutingEngineInterface`, `NotificationRepositoryInterface`
- **Exceptions**: `NotificationException`, `ChannelException`

### 2. Application Layer (`app/Application/Services/`)
- `EventIngestionService`: Handles event ingestion and validation
- `RoutingService`: Determines which channels should receive events
- `NotificationDeliveryService`: Delivers notifications through channels
- `NotificationStatusService`: Provides status queries
- `RetryService`: Handles retry logic with exponential backoff

### 3. Infrastructure Layer (`app/Infrastructure/`)
- **Repositories**: `NotificationRepository` - Database persistence
- **Routing**: `ConfigBasedRoutingEngine` - Rule-based routing
- **Channels**: `EmailChannel`, `SmsChannel`, `PushChannel`, `WebhookChannel`

### 4. Presentation Layer (`app/Http/`)
- **Controllers**: `EventController`, `NotificationStatusController`
- **Routes**: API endpoints for event ingestion and status queries

## Features

### Event Ingestion
- REST API endpoint: `POST /api/events`
- Asynchronous processing via Laravel queues
- Event validation and normalization

### Routing Rules
- Configurable rules in `config/notification.php`
- Rules can match by:
    - Event type (e.g., `USER_REGISTERED`)
    - Priority (e.g., `high`, `urgent`)
    - Combination of both
- Multiple channels can be assigned per rule

### Notification Channels
- **Email**: Simulated email delivery
- **SMS**: Simulated SMS delivery
- **Push**: Simulated push notification
- **Webhook**: HTTP webhook calls

### Retry Mechanism
- Exponential backoff (configurable base delay)
- Maximum retry attempts (configurable)
- Automatic scheduling of retries
- Dead letter queue for permanently failed notifications

### Status API
- `GET /api/notifications/{id}` - Get notification status
- `GET /api/notifications/event/{eventId}` - Get all notifications for an event
- `GET /api/notifications/failed` - List failed deliveries
- `GET /api/notifications/dead-letter` - List dead letter notifications
