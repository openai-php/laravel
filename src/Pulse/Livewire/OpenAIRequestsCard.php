<?php

namespace OpenAI\Laravel\Pulse\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns\HasPeriod;
use Laravel\Pulse\Livewire\Concerns\RemembersQueries;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use OpenAI\Laravel\Pulse\Recorders\OpenAIRequests;

/**
 * @internal
 */
#[Lazy]
class OpenAIRequestsCard extends Card
{
    use HasPeriod, RemembersQueries;

    /**
     * The type of request aggregation to show.
     *
     * @var 'user'|'endpoint'|null
     */
    public ?string $type = null;

    /**
     * The openai requests type.
     *
     * @var 'user'|'endpoint'
     */
    #[Url]
    public string $openaiRequests = 'user';

    #[Computed]
    public function label(): string
    {
        return match ($this->type ?? $this->openaiRequests) {
            'user' => 'Top 10 OpenAI Users',
            'endpoint' => 'Top 10 OpenAI Endpoints',
        };
    }

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        $aggregate = $this->type ?? $this->openaiRequests;

        [$requests, $time, $runAt] = $this->remember(
            function () use ($aggregate) {
                /** @var Collection<int, object{key: string, count: int}> $counts */
                $counts = Pulse::aggregate(
                    match ($aggregate) {
                        'user' => 'openai_request_handled_per_user',
                        'endpoint' => 'openai_request_handled_per_endpoint',
                    },
                    'count', // @phpstan-ignore-line
                    $this->periodAsInterval(),
                    limit: 10,
                );

                if ($aggregate === 'user') {
                    /** @var Collection<int, array{id: string|int, name: string, email?: ?string, avatar?: ?string, extra?: ?string}> $users */
                    $users = Pulse::resolveUsers($counts->pluck('key'));

                    return $counts->map(function ($row) use ($users) {
                        $user = $users->firstWhere('id', $row->key);

                        return (object) [
                            'user' => (object) [
                                'id' => $row->key,
                                'name' => $user['name'] ?? ($row->key === 'null' ? 'Guest' : 'Unknown'),
                                'extra' => $user['extra'] ?? $user['email'] ?? '',
                                'avatar' => $user['avatar'] ?? (($user['email'] ?? false)
                                        ? sprintf('https://gravatar.com/avatar/%s?d=mp', hash('sha256', trim(strtolower($user['email']))))
                                        : null),
                            ],
                            'count' => (int) $row->count,
                        ];
                    });
                }

                return $counts->map(function ($row) {
                    [$method, $uri] = json_decode($row->key, flags: JSON_THROW_ON_ERROR); // @phpstan-ignore-line

                    return (object) [
                        'uri' => $uri,
                        'method' => $method,
                        'count' => (int) $row->count,
                    ];
                });
            },
            $aggregate
        );

        return View::make('openai-php::livewire.openai-requests', [
            'time' => $time,
            'runAt' => $runAt,
            'config' => Config::get('pulse.recorders.'.OpenAIRequests::class),
            'requests' => $requests,
            'aggregate' => $aggregate,
        ]);
    }
}
