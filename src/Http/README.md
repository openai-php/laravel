## HTTP Handlers and middleware

The HTTP handler allows you to flexibly control requests and responses with custom middleware for logging, retries, and other features.

Out of the box, The `\OpenAI\Laravel\Http\Handler` is ready to use and automatically dispatches [Laravel HTTP events](https://laravel.com/docs/12.x/http-client#events) such as `RequestSending`, `ResponseReceived`, and `ConnectionFailed`.

For more advanced use cases, you can create a custom handler to add `logging`, `retries` , `with headers`, request/response transformations, or any other [Guzzle middleware](https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware), allowing you to fully tailor the behavior of your HTTP requests.

### Handler Configuration

Configure your handler in `config/openai.php`:

```php
'http_handler' => \OpenAI\Laravel\Http\Handler::class,
```

**Add Custom Handler**

Need custom logging, retries, or middleware? Create your own:

```php
'http_handler' => \App\Http\Handlers\CustomHandler::class,
```

Accepts: callable, class and service container resolvable class.

### HTTP Handler default features

The built-in `\OpenAI\Laravel\Http\Handler` provides:

- Laravel HTTP events (`RequestSending`, `ResponseReceived`, `ConnectionFailed`)
- Custom handler can use the `handle(...)` method through the handler's `__invoke(...)`.
- Add middleware and map failures through `Handler::mapFailure`
- Control whether events are dispatched with `Handler::shouldEvent(true)`

Perfect for seamless integration with zero configuration.

### HTTP Custom handler

You can create a custom handler to interact with the HTTP client and implement custom logic.

```php
class CustomHandler
{
    /**
     * Invoke the handler stack with the given request and options.
     */
    public function __invoke($request, array $config)
    {
        $handler = \GuzzleHttp\HandlerStack::create();

        // Add custom logic here: logging, retries, modifying requests, etc.

        return $handler($request, $config);
    }
}
```

**Creating the handler by extending the `Handler` Class**

Extending the Handler class allow to create handler with `handle(...)`, including a `HandlerStack` instance. Additionally, it dispatches Laravel HTTP events and handles failures, logging, and more through middleware.

```php
use OpenAI\Laravel\Http\Handler;

class CustomHandler extends Handler
{
    /**
     * Invoke the handler stack with the given request and options.
     */
    public function handle($handler, $request, array $config)
    {
        // Add custom logic here: logging, retries, modifying requests, etc.

        return $handler($request, $config);
    }
}
```
### HTTP Handler with guzzle middleware

This example demonstrates how to interact with the HTTP client using Guzzle middleware. You can create your own custom [middleware](https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware) or continue using available [Guzzle middleware](https://github.com/guzzle/guzzle/blob/7.10/src/Middleware.php) like `tap`, `mapRequest`, `mapResponse`, `retry`, etc.

The middleware can be added to the handler stack using $handler->push, whether you’re using the __invoke method or hanlde(...) method by extending core handler.

```php
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CustomHandler
{
    /**
     * Invoke the handler.
     */
    public function __invoke($request, array $config)
    {
        $handler = \GuzzleHttp\HandlerStack::create();

        // Example: modify request URI in mapRequest middleware
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withUri(\GuzzleHttp\Psr7\Utils::uriFor('new-path'));
        }));

        // Example: modify response body in mapResponse middleware
        $handler->push(Middleware::mapResponse(function (ResponseInterface $response) {
            return $response->withBody(\GuzzleHttp\Psr7\Utils::streamFor('Hello'));
        }));

        return $handler($request, $config);
    }
}
```

**Adding Custom Headers to Requests**

Here’s an example of adding a custom header to the request using middleware. This example extends Laravel's Handler class to handle the request and apply the custom header:

```php
use GuzzleHttp\Middleware;
use OpenAI\Laravel\Http\Handler;

class CustomHeaderHandler extends Handler
{
    /**
     * Invoke the handler.
     */
    public function handle($handler, $request, array $config)
    {
        // Example: modify request URI in mapRequest middleware
        $handler->push(Middleware::mapRequest(function ($request) {
            return $request->withHeader('X-Custom-Name', 'Laravel');
        }));

        return $handler($request, $config);
    }
}
```

#### HTTP Handler add retry middleware with guzzle

You can add retry http client request by creating a custom handler that pushes a [guzzle retry middleware](https://github.com/guzzle/guzzle/blob/7.10/src/Middleware.php#L179) onto the handler stack. 

This example middleware will automatically retry requests in case of server errors (5xx responses) or other conditions you define.

```php
use GuzzleHttp\Middleware;
use OpenAI\Laravel\Http\Handler;

class RetryHandler extends Handler
{
    /**
     * Invoke the handler.
     */
    public function handle(\GuzzleHttp\HandlerStack $handler, $request, array $config)
    {
        // Example: add retry middleware
        $handler->push(Middleware::retry(function ($retries, $request, $response = null, $exception = null) {

            // For instance, 3-retry for 5xx responses
            if ($retries < 3 && $response && $response->getStatusCode() >= 500) {
                return true; // Retry on server errors
            }

            return false; // Don't retry if the conditions above are not met
        }));

        return $handler($request, $config);

    }
}
```

### HTTP Handler usage with client factory

You can configure the OpenAI client to use a HTTP client handler when creating the client via the factory

```php
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Laravel\Http\Handler;

$client = OpenAI::factory()
    // Other configuration...
    ->withHttpClient(new \GuzzleHttp\Client([
        'handler' => Handler::resolve(config('openai.http_handler')),
    ]));
```
