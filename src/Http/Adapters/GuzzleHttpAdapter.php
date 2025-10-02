<?php

namespace OpenAI\Laravel\Http\Adapters;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 *
 * This class hooks into GuzzleHttp's middleware and fires
 * Laravel's HTTP client events.
 */
class GuzzleHttpAdapter
{
    /**
     * Create a new GuzzleHttp client instance that dispatches Laravel HTTP events.
     *
     * @param  array<string, mixed>  $options
     */
    public function client(array $options = [], ?callable $handler = null): Client
    {
        $stack = HandlerStack::create($handler);

        // Push middleware that hooks into Guzzle’s request lifecycle
        $stack->push(Middleware::tap(
            // Before sending the request → dispatch "sending" event
            function (RequestInterface $request) {
                event(new RequestSending(new Request($request)));

                return $request;
            },

            // After sending → handle promise (success or failure)
            function (RequestInterface $request, array $_opt, PromiseInterface $promise) {
                return $promise
                    ->then(function (ResponseInterface $response) use ($request) {
                        // Dispatch "response received" event
                        Event::dispatch(new ResponseReceived(
                            new Request($request),
                            new Response($response)
                        ));

                        return $response;
                    })
                    ->otherwise(function ($reason) use ($request) {
                        // Normalize the failure into a Laravel ConnectionException
                        $exception = $reason instanceof RequestException
                            ? new ConnectionException($reason->getMessage(), $reason->getCode(), $reason)
                            : new ConnectionException(
                                new RequestException('Connection failed', $request)
                            );

                        // Dispatch "connection failed" event
                        Event::dispatch(new ConnectionFailed(new Request($request), $exception));

                        // Re-throw the original rejection
                        return \GuzzleHttp\Promise\Create::rejectionFor($reason);
                    });
            }
        ));

        return new Client(array_merge($options, ['handler' => $stack]));
    }

    /**
     * Create a GuzzleHttp with optional options and handler stack.
     *
     * @param  array<string, mixed>  $options
     */
    public static function make(array $options = [], ?callable $stack = null): Client
    {
        return (new self)->client($options, $stack);
    }

    /**
     * Create GuzzleHttp with specify the timeout (in seconds) for the client.
     */
    public static function timeout(mixed $seconds): Client
    {
        return static::make(['timeout' => $seconds]);
    }
}
