<?php

test('exceptions')
    ->expect('OpenAI\Laravel\Exceptions')
    ->toDependOnNothing();

test('facades')
    ->expect('OpenAI\Laravel\Facades\OpenAI')
    ->toOnlyDependOn([
        'Illuminate\Support\Facades\Facade',
    ]);

test('service providers')
    ->expect('OpenAI\Laravel\ServiceProvider')
    ->toOnlyDependOn([
        'Illuminate\Support\ServiceProvider',
        'OpenAI\Laravel',
        'OpenAI',

        // helpers...
        'config',
        'config_path',
    ]);
