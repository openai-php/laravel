<?php

test('exceptions')
    ->expect('OpenAI\Laravel\Exceptions')
    ->toUseNothing();

test('facades')
    ->expect('OpenAI\Laravel\Facades\OpenAI')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
        'OpenAI\Contracts\ResponseContract',
        'OpenAI\Laravel\Testing\OpenAIFake',
        'OpenAI\Responses\StreamResponse',
    ]);

test('service providers')
    ->expect('OpenAI\Laravel\ServiceProvider')
    ->toOnlyUse([
        'GuzzleHttp\Client',
        'Illuminate\Support\ServiceProvider',
        'Illuminate\Container\Container',
        'OpenAI\Laravel',
        'OpenAI',
        'Illuminate\Contracts\Support\DeferrableProvider',

        // helpers...
        'config',
        'config_path',
    ]);
