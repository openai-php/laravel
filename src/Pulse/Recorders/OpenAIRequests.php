<?php

namespace OpenAI\Laravel\Pulse\Recorders;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Laravel\Pulse\Pulse;
use Laravel\Pulse\Recorders\Concerns\Groups;
use Laravel\Pulse\Recorders\Concerns\Ignores;
use Laravel\Pulse\Recorders\Concerns\Sampling;
use OpenAI\Events\RequestHandled;

/**
 * @internal
 */
class OpenAIRequests
{
    use Groups, Ignores, Sampling;

    /**
     * The events to listen for.
     *
     * @var list<class-string>
     */
    public array $listen = [
        RequestHandled::class,
    ];

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse $pulse,
        protected Repository $config,
    ) {
        //
    }

    /**
     * Record the request.
     */
    public function record(RequestHandled $event): void
    {
        [$timestamp, $method, $uri, $userId] = [
            CarbonImmutable::now()->getTimestamp(),
            $event->payload->method->value,
            $event->payload->uri->toString(),
            $this->pulse->resolveAuthenticatedUserId(),
        ];

        $this->pulse->lazy(function () use ($timestamp, $method, $uri, $userId) {
            if (! $this->shouldSample() || $this->shouldIgnore($uri)) {
                return;
            }

            $this->pulse->record(
                type: 'openai_request_handled_per_user',
                key: json_encode($userId, flags: JSON_THROW_ON_ERROR),
                timestamp: $timestamp,
            )->count();

            $this->pulse->record(
                type: 'openai_request_handled_per_endpoint',
                key: json_encode([$method, $this->group($uri)], flags: JSON_THROW_ON_ERROR),
                timestamp: $timestamp,
            )->count();
        });
    }
}
