<?php

namespace Tests\Http;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

final class Bootstraping
{
    /**
     * Create a new instance
     */
    public function __construct(protected Container $container)
    {
        // Clear any existing facade application instance
        Facade::clearResolvedInstances();

        // Set the new container as the facade application
        Facade::setFacadeApplication($container);
    }

    /**
     * load the container instance
     */
    public static function load(): self
    {
        return new self(new Container);
    }

    /**
     * Add the cache binding
     */
    public function events(): self
    {

        $this->container->singleton('events', fn ($app) => new \Illuminate\Events\Dispatcher($app));

        return $this;
    }

    /**
     * Add the cache binding
     */
    public function cache(): self
    {
        $this->container->singleton('cache', fn ($app) => new \Illuminate\Cache\CacheManager($app));

        // You also need to bind a configuration to avoid a "config" binding error
        $this->container->singleton('config', function () {
            return [
                'cache' => [
                    'default' => 'array',
                    'stores' => [
                        'array' => [
                            'driver' => 'array',
                            'serialize' => false,
                        ],
                    ],
                ],
            ];
        });

        return $this;
    }
}
