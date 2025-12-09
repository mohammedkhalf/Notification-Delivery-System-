<?php

namespace Tests\Unit\Infrastructure\Channels;

use App\Domain\Channels\ChannelResult;
use App\Domain\Events\Event;
use App\Domain\Events\Priority;
use App\Infrastructure\Channels\EmailChannel;
use Tests\TestCase;

class EmailChannelTest extends TestCase
{
    private EmailChannel $channel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channel = new EmailChannel();
    }

    public function test_returns_correct_channel_name(): void
    {
        $this->assertEquals('email', $this->channel->getName());
    }

    public function test_formats_message_for_user_registered(): void
    {
        $event = Event::create(
            eventType: 'USER_REGISTERED',
            payload: ['name' => 'John Doe'],
            recipient: 'john@example.com',
            priority: Priority::NORMAL
        );

        $message = $this->channel->formatMessage($event);

        $this->assertStringContainsString('USER_REGISTERED', $message);
        $this->assertStringContainsString('registered', $message);
        $this->assertStringContainsString('John Doe', $message); // From payload
    }

    public function test_send_returns_success_result(): void
    {
        $event = Event::create(
            eventType: 'TEST',
            payload: [],
            recipient: 'test@example.com',
            priority: Priority::NORMAL
        );

        $message = $this->channel->formatMessage($event);

        $result = $this->channel->send($event, $message);

        $this->assertInstanceOf(ChannelResult::class, $result);
        $this->assertIsBool($result->success);
    }

    public function test_formats_message_with_payload(): void
    {
        $event = Event::create(
            eventType: 'PAYMENT_COMPLETED',
            payload: ['amount' => 99.99],
            recipient: 'user@example.com',
            priority: Priority::NORMAL
        );

        $message = $this->channel->formatMessage($event);

        $this->assertStringContainsString('99.99', $message);
        $this->assertStringContainsString('PAYMENT_COMPLETED', $message);
    }
}

