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
        'OpenAI\Laravel',
        'OpenAI',
        'Illuminate\Contracts\Support\DeferrableProvider',

        // helpers...
        'config',
        'config_path',
    ]);

test('guzzle transporter')
    ->expect('OpenAI\Laravel\GuzzleTransporter')
    ->toOnlyUse([
        'Closure',
        'GuzzleHttp\Client',
        'GuzzleHttp\Exception\ConnectException',
        'GuzzleHttp\Exception\RequestException',
        'GuzzleHttp\HandlerStack',
        'GuzzleHttp\Middleware',
        'Psr\Http\Message\RequestInterface',
        'Psr\Http\Message\ResponseInterface',
        'RuntimeException',

        // helpers...
        'config',
    ]);
