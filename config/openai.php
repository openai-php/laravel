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
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Maximum Retry Attempt
    |--------------------------------------------------------------------------
    |
    | The retry attempt may be used to specify how many times retry when OpenAI server return error .
    | By default, the library will try once.
    */

    'max_retry_attempt' => env('OPENAI_RETRY_ATTEMPT', 5),

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | Decides how long after a request should be repeated when a request fails.
    | By default, resend the request immediately.
    */

    'retry_delay' => env('OPENAI_RETRY_DELAY'),
];
