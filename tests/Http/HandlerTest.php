<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;
use OpenAI\Laravel\Http\Handler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\Http\Bootstraping;

beforeEach(function () {
    Bootstraping::load()->events()->cache();
    Event::fake();
});

/**
 * Create common http client assets
 */
function httpClientAsserts(
    Client $client,
    mixed $toBe,
    $toBeStatus = 200,
    string $method = 'GET',
    string $uri = 'test',
    bool $async = true
) {
    // Synchronous request
    $response = $client->request($method, $uri);
    expect($response->getStatusCode())->toBe($toBeStatus);
    expect((string) $response->getBody())->toBe($toBe);

    $asyncResponse = null;

    // Asynchronous request
    if ($async) {
        $promise = $client->requestAsync($method, $uri);
        $asyncResponse = $promise->wait();
        expect($asyncResponse->getStatusCode())->toBe($toBeStatus);
        expect((string) $asyncResponse->getBody())->toBe($toBe);
    }

    return [
        'response' => $response,
        'asyncResponse' => $asyncResponse,
    ];
}

it('resolve callable', function () {

    $callback = function (RequestInterface $request, $config) {
        $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));

        return $handler($request, $config);
    };

    $client = new Client(['handler' => Handler::resolve($callback)]);

    httpClientAsserts($client, 'PHP is ');
});

it('resolve the class from the container', function () {

    $clasName = new class
    {
        public function __invoke(RequestInterface $request, array $config)
        {
            $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));

            return $handler($request, $config);
        }
    };

    // Bind an invokable class to the container
    app()->instance('TestHandler', $clasName);

    $client = new Client(['handler' => Handler::resolve('TestHandler')]);

    httpClientAsserts($client, 'PHP is ');
});

it('resolve the class-string with __invoke', function () {

    $clasName = new class
    {
        public function __invoke(RequestInterface $request, array $config)
        {
            $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));

            return $handler($request, $config);
        }
    };

    $client = new Client(['handler' => Handler::resolve($clasName::class)]);

    httpClientAsserts($client, 'PHP is ');
});

it('resolve the class-string without __invoke returns null', function () {
    $clasName = new class {};
    expect(Handler::resolve($clasName::class))->toBe(null);
});

it('resolve argument null always returns null', function () {
    expect(Handler::resolve(null))->toBe(null);
});

// ------------------- mapped failure -------------------

it('mapped failure calls callback on ConnectException', function () {
    $mock = new MockHandler([new ConnectException('failure', new Request('GET', '/'))]);

    $called = false;
    $handler = HandlerStack::create($mock);
    $handler->push(Handler::mapFailure(function ($exception) use (&$called) {
        expect($exception)->toBeInstanceOf(ConnectException::class);
        $called = true;
    }));

    $client = new Client(['handler' => $handler]);

    expect(fn () => $client->request('GET', 'test'))->toThrow(ConnectException::class);
    expect($called)->toBeTrue();
});

it('mapped failure calls callback on non-ConnectException', function () {
    $mock = new MockHandler([new \RuntimeException('failure')]);

    $called = false;
    $handler = HandlerStack::create($mock);
    $handler->push(Handler::mapFailure(function ($e) use (&$called) {
        $called = true;
    }));
    $client = new Client(['handler' => $handler]);

    expect(fn () => $client->request('GET', '/test'))->toThrow(\RuntimeException::class, 'failure');
    expect($called)->toBeTrue();
});

it('mapped failure does not call callback on success response', function () {
    $mock = new MockHandler([new Response(200, [], 'PHP is')]);

    $called = false;
    $handler = HandlerStack::create($mock);
    $handler->push(Handler::mapFailure(function () use (&$called) {
        $called = true;
    }));

    $client = new Client(['handler' => $handler]);
    $response = $client->request('GET', 'test');

    expect($called)->toBeFalse();
    expect($response)->toBeInstanceOf(Response::class);
    expect((string) $response->getBody())->toBe('PHP is');
});

// ------------------- handler invokes -------------------

it('__invoke handler stack returns response with Laravel http events', function () {

    Handler::shouldEvent(true);

    $mock = new MockHandler([
        new Response(200, [], 'PHP is '), // for sync
        new Response(200, [], 'PHP is '), // for async
    ]);

    $client = new Client(['handler' => (new Handler)->withHandler($mock)]);

    httpClientAsserts($client, 'PHP is ');

    Event::assertDispatched(RequestSending::class);
    Event::assertDispatched(ResponseReceived::class);
});

it('__invoke handler stack failure with Laravel http event ConnectionFailed ', function () {

    Handler::shouldEvent(true);

    // Mock handler: simulate connection failures for sync and async requests
    $mock = new MockHandler([
        new ConnectException('Connection failed', new Request('GET', 'test-sync')),
        new ConnectException('Connection failed', new Request('GET', 'test-async')),
    ]);

    // Client with our custom OpenAI handler
    $client = new Client(['handler' => (new Handler)->withHandler($mock)]);

    try {
        $client->request('GET', 'tests');
        $client->requestAsync('GET', 'tests')->wait();
    } catch (ConnectException $e) {
    }

    // Assert Laravel events
    Event::assertDispatched(ConnectionFailed::class);
    Event::assertDispatched(RequestSending::class);
    Event::assertNotDispatched(ResponseReceived::class);
});

it('__invoke handler stack without events when shouldEvent is false', function () {

    // two responses: one for sync, one for async
    $mock = new MockHandler([new Response(200, [], 'PHP is '), new Response(200, [], 'PHP is ')]);

    Handler::shouldEvent(false);

    $client = new Client(['handler' => (new Handler)->withHandler($mock)]);

    httpClientAsserts($client, 'PHP is ');

    Event::assertNothingDispatched();
});

it('__invoke handler stack by extending the handler class', function () {

    // two responses: one for sync, one for async
    $mock = new MockHandler([new Response(200, [], 'init'), new Response(200, [], 'init')]);

    // Custom handler that modifies the response body
    $customHandler = new class extends Handler
    {
        public function handle(HandlerStack $handler, $request, array $config)
        {
            $handler->push(Middleware::mapResponse(function (ResponseInterface $response) {
                return $response->withBody(\GuzzleHttp\Psr7\Utils::streamFor('PHP is '));
            }));

            // MUST return the response/promise
            return $handler($request, $config);
        }
    };

    $customHandler->withHandler($mock);

    $client = new Client(['handler' => $customHandler]);

    httpClientAsserts($client, 'PHP is ');
});

// ------------------- fluent methods -------------------

it('fluent methods exists ===================', function () {
    expect(true)->toBe(true);
});

it('handle executes handler stack correctly', function () {

    $mock = new MockHandler([new Response(200, [], 'PHP is')]);
    $handlerStack = HandlerStack::create($mock);

    $request = (new Handler)->handle($handlerStack, new Request('GET', 'test'), []);
    $response = $request->wait();

    expect($response)->toBeInstanceOf(Response::class);
    expect((int) $response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toBe('PHP is');
});

it('shouldEvent can enable and disable event dispatching', function () {
    Handler::shouldEvent(false);
    expect(Handler::isEventEnabled())->toBeFalse();

    Handler::shouldEvent(true);
    expect(Handler::isEventEnabled())->toBeTrue();
});

it('handlerStack creates handler stack if not already cached', function () {
    $handler = new Handler;
    $stack = (new ReflectionClass($handler))->getProperty('handlerStack');
    $stack->setAccessible(true);

    expect($stack->getValue($handler))->toBeNull();

    $created = (new ReflectionMethod($handler, 'getHandlerStack'))->invoke($handler);
    expect($created)->toBeInstanceOf(HandlerStack::class);
    expect($stack->getValue($handler))->toBe($created);
});

it('getHandlerStack uses existing handler stack when already cached', function () {
    $handler = new Handler;

    $fakeStack = HandlerStack::create();
    $property = new ReflectionProperty($handler, 'handlerStack');
    $property->setAccessible(true);
    $property->setValue($handler, $fakeStack);

    $stack = (new ReflectionMethod($handler, 'getHandlerStack'))->invoke($handler);

    expect($stack)->toBe($fakeStack);
});

it('withHandler sets handler and returns static', function () {
    $handler = new Handler;

    $callable = fn () => true;
    $result = $handler->withHandler($callable);

    expect($result)->toBeInstanceOf(Handler::class);
    $property = new ReflectionProperty($handler, 'handler');
    $property->setAccessible(true);

    expect($property->getValue($handler))->toBe($callable);
});
