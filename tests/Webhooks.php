<?php

namespace Tests;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use JsonException;
use OpenAI\Enums\Webhooks\WebhookEvent;
use OpenAI\Exceptions\WebhookVerificationException;
use OpenAI\Laravel\Events\WebhookReceived;
use OpenAI\Webhooks\WebhookSignatureVerifier;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[WithConfig('openai.webhook.enabled', true)]
#[WithConfig('openai.webhook.uri', '/openai/webhook')]
#[WithConfig('openai.webhook.secret', 'whsec_MfKQ9r8GKYqrTwjUPD8ILPZIo2LaLaSw')]
class Webhooks extends WebhookTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[WithConfig('openai.webhook.enabled', false)]
    public function webhook_route_is_not_registered_when_webhook_is_disabled(): void
    {
        $router = $this->app?->get('router');
        assert($router instanceof Router);
        $this->assertFalse($router->has('openai.webhook'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    public function webhook_route_is_registered_when_webhook_is_enabled(): void
    {
        $router = $this->app?->get('router');
        assert($router instanceof Router);
        $this->assertTrue($router->has('openai.webhook'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function valid_webhook_request_is_accepted(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = [
            'id' => 'evt_test_webhook',
            'type' => WebhookEvent::BatchFailed,
            'created_at' => $timestamp,
            'data' => [
                'id' => 'obj_test_webhook',
            ],
        ];
        $signature = $this->app?->make(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-id' => $messageId,
                'webhook-timestamp' => (string) $timestamp,
                'webhook-signature' => $signature,
            ])
            ->assertAccepted();
        Event::assertDispatched(WebhookReceived::class, static fn (WebhookReceived $event) => (
            $event->id === $messageId
            && $event->timestamp->getTimestamp() === $timestamp
            && $event->type === WebhookEvent::BatchFailed
            && $event->payload === $body['data']
        ));
    }

    #[Test]
    public function webhook_request_with_invalid_signature_is_rejected(): void
    {
        Event::fake();
        $this
            ->postJson('/openai/webhook', [
                'id' => 'evt_test_webhook',
                'object' => 'event',
                'type' => 'test.event',
                'data' => [
                    'id' => 'obj_test_webhook',
                ],
            ], [
                'webhook-id' => 'evt_test_webhook',
                'webhook-timestamp' => (string) time(),
                'webhook-signature' => 'v1,invalid_signature',
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    #[Test]
    public function webhook_request_without_signature_header_is_rejected(): void
    {
        Event::fake();
        $this
            ->postJson('/openai/webhook', [
                'id' => 'evt_test_webhook',
                'object' => 'event',
                'type' => 'test.event',
                'data' => [
                    'id' => 'obj_test_webhook',
                ],
            ], [
                'webhook-id' => 'evt_test_webhook',
                'webhook-timestamp' => (string) time(),
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function webhook_request_with_missing_id_header_is_rejected(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = [
            'id' => 'evt_test_webhook',
            'type' => WebhookEvent::BatchFailed,
            'created_at' => $timestamp,
            'data' => [
                'id' => 'obj_test_webhook',
            ],
        ];
        $signature = $this->app?->get(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-timestamp' => (string) $timestamp,
                'webhook-signature' => $signature,
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function webhook_request_with_missing_timestamp_header_is_rejected(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = [
            'id' => 'evt_test_webhook',
            'type' => WebhookEvent::BatchFailed,
            'created_at' => $timestamp,
            'data' => [
                'id' => 'obj_test_webhook',
            ],
        ];
        $signature = $this->app?->get(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-id' => $messageId,
                'webhook-signature' => $signature,
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function webhook_request_with_old_timestamp_is_rejected(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = [
            'id' => 'evt_test_webhook',
            'type' => WebhookEvent::BatchFailed,
            'created_at' => $timestamp,
            'data' => [
                'id' => 'obj_test_webhook',
            ],
        ];
        $signature = $this->app?->get(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-id' => $messageId,
                'webhook-signature' => $signature,
                'webhook-timestamp' => (string) ($timestamp - 1000),
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function webhook_request_with_future_timestamp_is_rejected(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = [
            'id' => 'evt_test_webhook',
            'type' => WebhookEvent::BatchFailed,
            'created_at' => $timestamp,
            'data' => [
                'id' => 'obj_test_webhook',
            ],
        ];
        $signature = $this->app?->get(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-id' => $messageId,
                'webhook-signature' => $signature,
                'webhook-timestamp' => (string) ($timestamp + 1000),
            ])
            ->assertForbidden();
        Event::assertNotDispatched(WebhookReceived::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws WebhookVerificationException
     */
    #[Test]
    public function webhook_request_with_invalid_payload_is_rejected(): void
    {
        $messageId = 'evt_test_webhook';
        $timestamp = time();
        $body = ['invalid_field' => 'invalid_value'];
        $signature = $this->app?->get(WebhookSignatureVerifier::class)->sign(
            $messageId,
            $timestamp,
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        Event::fake();
        $this
            ->postJson('/openai/webhook', $body, [
                'webhook-id' => $messageId,
                'webhook-timestamp' => $timestamp,
                'webhook-signature' => $signature,
            ])
            ->assertUnprocessable();
        Event::assertNotDispatched(WebhookReceived::class);
    }
}
