<?php

use Illuminate\Config\Repository;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Laravel\ServiceProvider;
use OpenAI\Resources\Completions;
use OpenAI\Responses\Completions\CreateResponse;
use PHPUnit\Framework\ExpectationFailedException;

it('resolves resources', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'openai' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    OpenAI::setFacadeApplication($app);

    $completions = OpenAI::completions();

    expect($completions)->toBeInstanceOf(Completions::class);
});

test('fake returns the given response', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'text' => 'awesome!',
                ],
            ],
        ]),
    ]);

    $completion = OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    expect($completion['choices'][0]['text'])->toBe('awesome!');
});

test('fake throws an exception if there is no more given response', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);
})->expectExceptionMessage('No fake responses left');

test('append more fake responses', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'id' => 'cmpl-1',
        ]),
    ]);

    OpenAI::addResponses([
        CreateResponse::fake([
            'id' => 'cmpl-2',
        ]),
    ]);

    $completion = OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    expect($completion)
        ->id->toBe('cmpl-1');

    $completion = OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    expect($completion)
        ->id->toBe('cmpl-2');
});

test('fake can assert a request was sent', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::assertSent(Completions::class, function (string $method, array $parameters): bool {
        return $method === 'create' &&
            $parameters['model'] === 'gpt-3.5-turbo-instruct' &&
            $parameters['prompt'] === 'PHP is ';
    });
});

test('fake throws an exception if a request was not sent', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::assertSent(Completions::class, function (string $method, array $parameters): bool {
        return $method === 'create' &&
            $parameters['model'] === 'gpt-3.5-turbo-instruct' &&
            $parameters['prompt'] === 'PHP is ';
    });
})->expectException(ExpectationFailedException::class);

test('fake can assert a request was sent on the resource', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::completions()->assertSent(function (string $method, array $parameters): bool {
        return $method === 'create' &&
            $parameters['model'] === 'gpt-3.5-turbo-instruct' &&
            $parameters['prompt'] === 'PHP is ';
    });
});

test('fake can assert a request was sent n times', function () {
    OpenAI::fake([
        CreateResponse::fake(),
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::assertSent(Completions::class, 2);
});

test('fake throws an exception if a request was not sent n times', function () {
    OpenAI::fake([
        CreateResponse::fake(),
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::assertSent(Completions::class, 2);
})->expectException(ExpectationFailedException::class);

test('fake can assert a request was not sent', function () {
    OpenAI::fake();

    OpenAI::assertNotSent(Completions::class);
});

test('fake throws an exception if a unexpected request was sent', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::assertNotSent(Completions::class);
})->expectException(ExpectationFailedException::class);

test('fake can assert a request was not sent on the resource', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->assertNotSent();
});

test('fake can assert no request was sent', function () {
    OpenAI::fake();

    OpenAI::assertNothingSent();
});

test('fake throws an exception if any request was sent when non was expected', function () {
    OpenAI::fake([
        CreateResponse::fake(),
    ]);

    OpenAI::completions()->create([
        'model' => 'gpt-3.5-turbo-instruct',
        'prompt' => 'PHP is ',
    ]);

    OpenAI::assertNothingSent();
})->expectException(ExpectationFailedException::class);
