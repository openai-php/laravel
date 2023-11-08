<?php

namespace OpenAI\Laravel;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class GuzzleTransporter
{
    /**
     * Get Guzzle Client
     */
    public static function getClient(): Client
    {
        $stack = HandlerStack::create();
        $stack->push((new GuzzleTransporter)->getRetryMiddleware());

        return new Client([
            'timeout' => config('openai.request_timeout', 30),
            'handler' => $stack,
        ]);
    }

    /**
     * Get retry middleware callable
     */
    public function getRetryMiddleware(): callable
    {
        return Middleware::retry($this->getDecider(), $this->getDelayDuration());
    }

    /**
     * Get decider logic
     */
    public function getDecider(): Closure
    {
        $maxRetries = config('openai.max_retry_attempt');

        return function (
            int $retries,
            ?RequestInterface $request = null,
            ?ResponseInterface $response = null,
            ?RuntimeException $e = null
        ) use ($maxRetries): bool {
            if ($retries > $maxRetries) {
                return false;
            }

            if ($e instanceof RequestException || $e instanceof ConnectException) {
                return true;
            }

            return false;
        };
    }

    /**
     * Get delay duration
     */
    public function getDelayDuration(): callable
    {
        return function () {
            return 1000 * config('openai.retry_delay');
        };
    }
}
