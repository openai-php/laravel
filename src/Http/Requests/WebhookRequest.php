<?php

namespace OpenAI\Laravel\Http\Requests;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenAI\Enums\Webhooks\WebhookEvent;

class WebhookRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'starts_with:evt_'],
            'type' => ['required', 'string', Rule::enum(WebhookEvent::class)],
            'created_at' => ['required', 'integer', 'min:0'],
            'data' => ['required', 'array'],
        ];
    }

    public function getEventType(): WebhookEvent
    {
        $type = $this->input('type');
        assert(is_string($type));

        return WebhookEvent::from($type);
    }

    public function getEventId(): string
    {
        $id = $this->input('id');
        assert(is_string($id));

        return $id;
    }

    public function getTimestamp(): DateTimeInterface
    {
        $timestamp = $this->input('created_at');
        assert(is_int($timestamp));

        return (new DateTimeImmutable)->setTimestamp($timestamp);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getData(): array
    {
        $data = $this->input('data', []);
        assert(is_array($data));

        return $data;
    }
}
