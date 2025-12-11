<?php

use Illuminate\Support\Facades\Route;
use OpenAI\Laravel\Http\Controllers\WebhookController;
use OpenAI\Laravel\Http\Middleware\VerifyWebhookSignature;

Route::group(['middleware' => config('openai.webhook.middleware', ['web'])], function () {
    $webhookUri = config('openai.webhook.uri', '/openai/webhook');
    assert(is_string($webhookUri));

    Route::post($webhookUri, WebhookController::class)
        ->name('webhook')
        ->middleware(VerifyWebhookSignature::class);
});
