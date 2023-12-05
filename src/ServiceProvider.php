<?php

declare(strict_types=1);

namespace OpenAI\Laravel;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Livewire\Livewire;
use OpenAI;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Commands\InstallCommand;
use OpenAI\Laravel\Events\DispatcherDecorator;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;
use OpenAI\Laravel\Pulse\Livewire\OpenAIRequestsCard;

/**
 * @internal
 */
final class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientContract::class, static function (Container $container): Client {
            $apiKey = config('openai.api_key');
            $organization = config('openai.organization');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('openai.request_timeout', 30)]))
                ->withEventDispatcher(new DispatcherDecorator($container->make(DispatcherContract::class))) // @phpstan-ignore-line
                ->make();
        });

        $this->app->alias(ClientContract::class, 'openai');
        $this->app->alias(ClientContract::class, Client::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/openai.php' => config_path('openai.php'),
            ]);

            $this->commands([
                InstallCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'openai-php');

        if (class_exists(Livewire::class)) {
            Livewire::component('openai.pulse.requests', OpenAIRequestsCard::class);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Client::class,
            ClientContract::class,
            'openai',
        ];
    }
}
