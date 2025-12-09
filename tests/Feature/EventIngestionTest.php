<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_can_ingest_event_via_api(): void
    {
        $response = $this->postJson('/api/events', [
            'eventType' => 'USER_REGISTERED',
            'recipient' => 'john@example.com',
            'payload' => ['name' => 'John Doe'],
            'priority' => 'normal',
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'event' => [
                    'id',
                    'event_type',
                    'payload',
                    'recipient',
                    'priority',
                ],
            ]);

        Queue::assertPushed(\App\Jobs\ProcessEventJob::class);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/events', [
            'payload' => ['name' => 'John'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['eventType', 'recipient']);
    }

    public function test_validates_priority_enum(): void
    {
        $response = $this->postJson('/api/events', [
            'eventType' => 'TEST',
            'recipient' => 'test@example.com',
            'priority' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_accepts_valid_priorities(): void
    {
        $priorities = ['low', 'normal', 'high', 'urgent'];

        foreach ($priorities as $priority) {
            $response = $this->postJson('/api/events', [
                'eventType' => 'TEST',
                'recipient' => 'test@example.com',
                'priority' => $priority,
            ]);

            $response->assertStatus(202);
        }
    }

    public function test_uses_default_priority_when_not_provided(): void
    {
        $response = $this->postJson('/api/events', [
            'eventType' => 'TEST',
            'recipient' => 'test@example.com',
        ]);

        $response->assertStatus(202);
        $response->assertJsonPath('event.priority', 'normal');
    }
}

