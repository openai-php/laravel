<?php

test('exceptions')
    ->expect('OpenAI\Laravel\Exceptions')
    ->toUseNothing();

test('facades')
    ->expect('OpenAI\Laravel\Facades\OpenAI')
    ->toOnlyUse([
        'Illuminate\Support\Facades\Facade',
    ]);

test('service providers')
    ->expect('OpenAI\Laravel\ServiceProvider')
    ->toOnlyUse([
        'Illuminate\Support\ServiceProvider',
        'OpenAI\Laravel',
        'OpenAI',

        // helpers...
        'config',
        'config_path',
    ]);
