<?php

test('exceptions')
    ->expect('OpenAI\Laravel\Exceptions')
    ->toUseNothing();

test('facades')
    ->expect('OpenAI\Laravel\Facades\OpenAI')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
        'OpenAI\Contracts\Response',
        'OpenAI\Laravel\Testing\OpenAIFake',
    ]);

test('service providers')
    ->expect('OpenAI\Laravel\ServiceProvider')
    ->toOnlyUse([
        'Illuminate\Support\ServiceProvider',
        'OpenAI\Laravel',
        'OpenAI',
        'Illuminate\Contracts\Support\DeferrableProvider',

        // helpers...
        'config',
        'config_path',
    ]);
