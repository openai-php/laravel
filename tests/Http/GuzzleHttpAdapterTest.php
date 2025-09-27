<?php

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Events\Dispatcher;
use OpenAI\Laravel\Http\Adapters\GuzzleHttpAdapter;

beforeEach(function () {
    app()->instance(DispatcherContract::class, new Dispatcher(app()));
    app()->alias(DispatcherContract::class, 'events'); // Important
});

it('create GuzzleHttp client instance', function () {
    expect((new GuzzleHttpAdapter)->make())->toBeInstanceOf(GuzzleHttp\Client::class);
    expect((new GuzzleHttpAdapter)->client())->toBeInstanceOf(GuzzleHttp\Client::class);
    expect((new GuzzleHttpAdapter)->timeout(3))->toBeInstanceOf(GuzzleHttp\Client::class);
});

it('create GuzzleHttp client instance with options timeout and handler', function () {
    $timeout = 0;
    $handler = HandlerStack::create(new MockHandler([new GuzzleResponse(200, [], 'OK')]));
    $handler->push(Middleware::tap(
        after: function ($req, array $options) use (&$timeout) {
            $timeout = $options['timeout'];
            expect($options['handler'])->toBeInstanceOf(HandlerStack::class);
        }
    ));
    $client = GuzzleHttpAdapter::make(['timeout' => 3], $handler);
    $response = $client->request('GET', 'https://example.com');

    expect($client)->toBeInstanceOf(\GuzzleHttp\Client::class);
    expect($response)->toBeInstanceOf(\Psr\Http\Message\ResponseInterface::class);
    expect($response->getBody()->getContents())->toBe('OK');
    expect($response->getStatusCode())->toBe(200);
    expect($timeout)->toBe(3);
});

it('runs middleware before and after callbacks to callable events', function () {
    $calledBefore = false;
    $calledAfter = false;

    $mock = new MockHandler([new GuzzleResponse(200, [], 'OK')]);
    $stack = HandlerStack::create($mock);

    $stack->push(Middleware::tap(
        before: function () use (&$calledBefore) {
            $calledBefore = true;
        },
        after: function ($req, array $options, $promise) use (&$calledAfter) {
            $calledAfter = true;
        }
    ));

    $client = (new GuzzleHttpAdapter)->client([], $stack);

    $response = $client->request('GET', 'https://example.com');

    // Force the response to complete
    expect($response->getBody()->getContents())->toBe('OK');
    expect($calledBefore)->toBeTrue();
    expect($calledAfter)->toBeTrue();
    expect($response->getStatusCode())->toBe(200);
});

it('handles rejected promise without Laravel events', function () {

    $mock = new MockHandler([new RequestException('Error', new GuzzleRequest('GET', 'test'))]);
    $handler = HandlerStack::create($mock);
    $client = (new GuzzleHttpAdapter)->client([], $handler);

    $promise = $client->getAsync('https://example.com');

    $rejection = null;
    $promise->then(null, function ($reason) use (&$rejection) {
        $rejection = $reason;
    })->wait();

    expect($rejection)->toBeInstanceOf(RequestException::class);
});

it('handles rejected promise and normalizes ConnectionException', function () {
    $calledAfter = false;
    $reason = null;

    // Mock a rejected promise
    $request = new GuzzleRequest('GET', 'test');
    $mock = new MockHandler([new RequestException('Connection failed', $request)]);
    $stack = HandlerStack::create($mock);

    // Push tap middleware to observe "after"
    $stack->push(Middleware::tap(
        after: function ($req, array $o, $promise) use (&$calledAfter, &$reason) {
            $promise->otherwise(function ($responseReason) use (&$calledAfter, &$reason) {
                $reason = $responseReason;
                $calledAfter = true;
            });
        }
    ));

    try {
        GuzzleHttpAdapter::make([], $stack)->request('GET', 'https://example.com');
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // expected
    }

    expect($calledAfter)->toBeTrue(); // ensure the "after" handler ran
    expect($reason)->toBeInstanceOf(RequestException::class);
});
