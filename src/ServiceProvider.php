<?php

declare(strict_types=1);

namespace OpenAI\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenAI;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Commands\InstallCommand;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;

/**
 * @internal
 */
final class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientContract::class, static function (): Client {
            $apiKey = config('openai.api_key');
            $organization = config('openai.organization');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                ->withHttpClient(new \Swis\Laravel\Bridge\PsrHttpClient\Client(
                    fn () => Http::timeout((int) config('openai.request_timeout', 30))))
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
