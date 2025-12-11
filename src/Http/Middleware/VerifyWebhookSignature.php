<?php

/**
 * This file is part of openai-php-laravel, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2025 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace OpenAI\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OpenAI\Exceptions\WebhookVerificationException;
use OpenAI\Webhooks\WebhookSignatureVerifier;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class VerifyWebhookSignature
{
    public function __construct(
        private WebhookSignatureVerifier $verifier,
        private PsrHttpFactory $psrBridge,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @throws AccessDeniedHttpException
     * @throws RuntimeException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $psrRequest = $this->psrBridge->createRequest($request);

        try {
            $this->verifier->verify($psrRequest);
        } catch (WebhookVerificationException $exception) {
            throw new AccessDeniedHttpException(
                'Invalid webhook signature',
                $exception,
            );
        }

        return $next($request);
    }
}
