<?php

use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Exceptions\ApiKeyIsMissing;
use OpenAI\Laravel\ServiceProvider;
use Tests\Fixtures\NullEventDispatcher;

beforeEach(function () {
    $app = app();

    $app->bind(Dispatcher::class, fn () => new NullEventDispatcher());
});

it('binds the client on the container', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'openai' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    expect($app->get(Client::class))->toBeInstanceOf(Client::class);
});

it('binds the client on the container as singleton', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'openai' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    $client = $app->get(Client::class);

    expect($app->get(Client::class))->toBe($client);
});

it('requires an api key', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([]));

    (new ServiceProvider($app))->register();
})->throws(
    ApiKeyIsMissing::class,
    'The OpenAI API Key is missing. Please publish the [openai.php] configuration file and set the [api_key].',
);

it('provides', function () {
    $app = app();

    $provides = (new ServiceProvider($app))->provides();

    expect($provides)->toBe([
        Client::class,
        ClientContract::class,
        'openai',
    ]);
});
