<?php

declare(strict_types=1);

namespace OpenAI\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenAI;
use OpenAI\Client;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;

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
        $this->app->singleton(Client::class, static function (): Client {
            $apiKey = config('openai.api_key');
            $organization = config('openai.organization');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            return OpenAI::client($apiKey, $organization);
        });

        $this->app->alias(Client::class, 'openai');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/openai.php' => config_path('openai.php'),
        ]);
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
        ];
    }
}
