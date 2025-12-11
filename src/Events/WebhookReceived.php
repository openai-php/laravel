<?php

namespace OpenAI\Laravel\Events;

use DateTimeInterface;
use OpenAI\Enums\Webhooks\WebhookEvent;

readonly class WebhookReceived
{
    /**
     * @param  array<array-key, mixed>  $payload
     */
    public function __construct(
        public WebhookEvent $type,
        public string $id,
        public DateTimeInterface $timestamp,
        public array $payload,
    ) {}
}
