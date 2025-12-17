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
        'Illuminate\Support\Facades\Route',
    ]);

test('service providers')
    ->expect('OpenAI\Laravel\ServiceProvider')
    ->toOnlyUse([
        'GuzzleHttp\Client',
        'Illuminate\Support\ServiceProvider',
        'OpenAI\Laravel',
        'OpenAI',
        'Illuminate\Contracts\Support\DeferrableProvider',
        'Illuminate\Support\Facades\Route',

        // helpers...
        'config',
        'config_path',
    ]);
