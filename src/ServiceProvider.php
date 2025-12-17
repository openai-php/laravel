<?php

declare(strict_types=1);

namespace OpenAI\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenAI;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Commands\InstallCommand;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;
use OpenAI\Webhooks\WebhookSignatureVerifier;

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
            $project = config('openai.project');
            $baseUri = config('openai.base_uri');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw ApiKeyIsMissing::create();
            }

            $client = OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('openai.request_timeout', 30)]));

            if (is_string($project)) {
                $client->withProject($project);
            }

            if (is_string($baseUri)) {
                $client->withBaseUri($baseUri);
            }

            return $client->make();
        });

        $this->app->alias(ClientContract::class, 'openai');
        $this->app->alias(ClientContract::class, Client::class);

        $this->app
            ->when(WebhookSignatureVerifier::class)
            ->needs('$secret')
            ->give(fn () => config('openai.webhook.secret'));
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

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (config('openai.webhook.enabled')) {
            Route::group([
                'namespace' => 'OpenAI\Laravel\Http\Controllers',
                'domain' => config('openai.webhook.domain'),
                'as' => 'openai.',
            ], fn () => $this->loadRoutesFrom(__DIR__.'/../routes/web.php'));
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
            WebhookSignatureVerifier::class,
            Client::class,
            ClientContract::class,
            'openai',
        ];
    }
}
