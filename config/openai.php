<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Project
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API project. This is used optionally in
    | situations where you are using a legacy user API key and need association
    | with a project. This is not required for the newer API keys.
    */
    'project' => env('OPENAI_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Base URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API base URL used to make requests. This
    | is needed if using a custom API endpoint. Defaults to: api.openai.com/v1
    */
    'base_uri' => env('OPENAI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    'webhook' => [
        /*
        |--------------------------------------------------------------------------
        | Webhook
        |--------------------------------------------------------------------------
        | This option controls whether the OpenAI webhook endpoint is
        | enabled. Set this to true to enable automatic handling of webhook
        | requests from OpenAI.
        */
        'enabled' => env('OPENAI_WEBHOOK_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Webhook URI / Subdomain
        |--------------------------------------------------------------------------
        |
        | This value is the URI path where OpenAI will send webhook requests to.
        | You may change this path to anything you like. Make sure to update
        | your OpenAI webhook settings to match this URI.
        | If necessary, you may also specify a custom domain for the webhook route.
        */
        'uri' => env('OPENAI_WEBHOOK_URI', '/openai/webhook'),
        'domain' => env('OPENAI_WEBHOOK_DOMAIN'),

        /*
        |--------------------------------------------------------------------------
        | Webhook Middleware
        |--------------------------------------------------------------------------
        | Here you may specify the middleware that will be applied to
        | the OpenAI webhook route.
        | Note that the signature verification middleware is always applied.
        */
        'middleware' => env('OPENAI_WEBHOOK_MIDDLEWARE', 'web'),

        /*
        |--------------------------------------------------------------------------
        | Webhook Signing secret
        |--------------------------------------------------------------------------
        |
        | This value is the signing secret used to verify incoming webhook
        | requests from OpenAI. You can find this secret in your OpenAI
        | dashboard, in the webhook settings for your application.
        */
        'secret' => env('OPENAI_WEBHOOK_SECRET'),
    ],
];
