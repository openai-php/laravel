<?php

namespace OpenAI\Laravel\Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;
use OpenAI\Laravel\Http\Handlers\HttpEvent;
use Tests\Http\Bootstraping;

beforeEach(function () {
    Bootstraping::load()->events()->cache();
    Event::fake();
});

it('dispatches ConnectionFailed event on ConnectException', function () {
    $mock = new MockHandler([
        new ConnectException('Connection failed', new Request('GET', 'tests')),
        new ConnectException('Connection failed', new Request('GET', 'tests')),
    ]);
    $handler = HandlerStack::create($mock);
    $handler->push(HttpEvent::failure());
    $client = new Client(['handler' => $handler]);

    try {
        $client->request('GET', 'tests');
        $client->requestAsync('GET', 'tests');
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        // expected
    }

    Event::assertDispatched(ConnectionFailed::class);
    Event::assertNotDispatched(RequestSending::class);
    Event::assertNotDispatched(ResponseReceived::class);
});

it('dispatches RequestSending and ResponseReceived events', function () {
    $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));
    $handler->push(HttpEvent::request());
    $handler->push(HttpEvent::response());
    $client = new Client(['handler' => $handler]);
    $response = $client->request('GET', 'test');
    Event::assertDispatched(RequestSending::class);
    Event::assertDispatched(ResponseReceived::class);
});

it('dispatches RequestSending and ResponseReceived events in async request', function () {
    $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));
    $handler->push(HttpEvent::request());
    $handler->push(HttpEvent::response());
    $client = new Client(['handler' => $handler]);
    $promise = $client->requestAsync('GET', '/test');
    $response = $promise->wait();
    Event::assertDispatched(RequestSending::class);
    Event::assertDispatched(ResponseReceived::class);
});

it('dispatches RequestSending and ResponseReceived events using HttpEvent __invoke', function () {

    $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'PHP is ')]));

    $handler->push(static function (callable $handler) {
        return function ($request, array $config) use ($handler) {
            return (new HttpEvent)->withHandler($handler)($request, $config);
        };
    });

    $client = new Client(['handler' => $handler]);
    $response = $client->request('GET', 'test');
    Event::assertDispatched(RequestSending::class);
    Event::assertDispatched(ResponseReceived::class);
});
