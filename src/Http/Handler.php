<?php

declare(strict_types=1);

namespace OpenAI\Laravel\Http;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Handler
{
    /**
     * The underlying handler used by the middleware stack.
     *
     * @var callable|null
     */
    protected $handler = null;

    /**
     * Cached the handler stack.
     */
    protected ?HandlerStack $handlerStack = null;

    /**
     * Indicates whether Laravel HTTP events should be dispatched globally.
     */
    protected static bool $shouldEvent = true;

    /**
     * Whether Laravel HTTP events should be dispatched.
     */
    public static function shouldEvent(bool $enabled = true)
    {
        static::$shouldEvent = $enabled;
    }

    /**
     * Determine if event dispatching is currently enabled.
     */
    public static function isEventEnabled(): bool
    {
        return static::$shouldEvent;
    }

    /**
     * Resolve the config-defined handler into a callable or null.
     *
     * @param  mixed  $handler  The handler to resolve.
     */
    public static function resolve($handler = null): ?callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler) && is_callable($instance = app($handler))) {
            return $instance;
        }

        return null;
    }

    /**
     * Middleware that maps request failures (rejected promises) through a callback.
     *
     * @see https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware
     */
    public static function mapFailure(callable $fn): callable
    {
        return static function (callable $handler) use ($fn): callable {

            return static function (RequestInterface $request, array $config) use ($handler, $fn) {

                /** @var \GuzzleHttp\Promise\PromiseInterface $promise */
                $promise = $handler($request, $config);

                return $promise->then(null, onRejected: static function ($reason) use ($fn) {

                    $fn($reason); // side effects only

                    // continue rejection chain
                    return \GuzzleHttp\Promise\Create::rejectionFor($reason);
                }
                );
            };
        };
    }

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
     * Invoke the handler stack with the given request and options.
     *
     * @param  array<string, mixed>  $config
     * @return ResponseInterface|PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $config)
    {
        $handlerStack = $this->getHandlerStack();

        // handler the defult event / logger

        // Pass the stack directly to handle()
        return $this->handle($handlerStack, $request, $config);
    }

    /**
     * Execute the handler stack with the given request and options.
     *
     * @param  RequestInterface  $request
     * @param  array<string, mixed>  $config
     * @return ResponseInterface|PromiseInterface
     */
    public function handle(HandlerStack $handler, $request, array $config)
    {
        // Now you can push additional middleware directly
        // Example:
        // $stack->push(Middleware::mapRequest(fn(RequestInterface $request) => $request));

        return $handler($request, $config);
    }
}
