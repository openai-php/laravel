<?php

use Illuminate\Config\Repository;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Laravel\ServiceProvider;
use OpenAI\Resources\Completions;

it('resolves resources', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'openai' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    $completions = OpenAI::completions();

    expect($completions)->toBeInstanceOf(Completions::class);
});
