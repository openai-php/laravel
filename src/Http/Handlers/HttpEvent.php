<?php

declare(strict_types=1);

namespace OpenAI\Laravel\Http\Handlers;

use GuzzleHttp\Exception\ConnectException;
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
use OpenAI\Laravel\Http\Handler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpEvent
{
    /**
     * The underlying handler used by the middleware stack.
     *
     * @var callable|null
     */
    private $handler = null;

    /**
     * Cached the handler stack.
     */
    protected ?HandlerStack $handlerStack = null;

    /**
     * Set the underlying handler to be used by this middleware.
     */
    public function withHandler(callable $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get or create the handler stack.
     */
    protected function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack ??= HandlerStack::create($this->handler);
    }

    /**
     * Handle the given HTTP request using a composed handler stack.
     *
     * @param  array<string|int, mixed>  $config
     * @return ResponseInterface|PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $config = [])
    {

        $handlerStack = $this->getHandlerStack();

        $handlerStack->push(static::request(), 'request-event');
        $handlerStack->push(static::response(), 'response-event');
        $handlerStack->push(static::failure(), 'failure-event');

        return $handlerStack($request, $config);
    }

    /**
     * Middleware to dispatch the Laravel HTTP RequestSending event before sending a request.
     *
     * @see https://api.laravel.com/docs/12.x/Illuminate/Http/Client/Events/RequestSending.html
     */
    public static function request(): callable
    {
        return Middleware::tap(before: function (RequestInterface $request) {
            Event::dispatch(new RequestSending(new Request($request)));
        });
    }

    /**
     * Middleware to dispatch the Laravel HTTP ResponseReceived event after a response is returned.
     *
     * @see https://api.laravel.com/docs/12.x/Illuminate/Http/Client/Events/ResponseReceived.html
     */
    public static function response(): callable
    {
        return Middleware::tap(
            after: function (RequestInterface $request, array $_o, PromiseInterface $promise) {
                // $promise is the Response promise
                $promise->then(function (ResponseInterface $response) use ($request) {
                    Event::dispatch(new ResponseReceived(
                        new Request($request),
                        new Response($response)
                    ));
                });
            }
        );
    }

    /**
     * Middleware to dispatch the Laravel HTTP ConnectionFailed event on connection errors.
     *
     * @see https://api.laravel.com/docs/12.x/Illuminate/Http/Client/Events/ConnectionFailed.html
     */
    public static function failure(): callable
    {
        return Handler::mapFailure(function ($e) {
            if ($e instanceof ConnectException) {
                $exception = new ConnectionException($e->getMessage(), $e->getCode());
                Event::dispatch(new ConnectionFailed(new Request($e->getRequest()), $exception));
            }
        });
    }
}
