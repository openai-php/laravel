<?php

return [

    //默认服务商
    'default' => env('DEFAULT_OPENAI_DRIVER', 'open_ai'),

    //openai服务商
    'open_ai' => [
        'api_key' => env('OPENAI_SECRET_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    //微软Azure服务商
    'azure_open_ai' => [
        'driver' => 'azure_open_ai',
        'base_url' => env('AZURE_OPENAI_BASE_URL'),
        'api_key' => env('AZURE_OPENAI_SECRET_KEY'),
        'api_version' => env('AZURE_OPENAI_API_VERSION'),
        'model' => 'gpt-3.5-turbo',
        'models' => [
            'text-embedding-ada-002' => 'text-embedding-ada-002',
            'gpt-3.5-turbo' => 'gpt-35-turbo-0613',
            'gpt-3.5-turbo-instruct' => 'gpt-35-turbo-instruct-0613',
            'gpt-3.5-turbo-0613' => 'gpt-35-turbo-0613',
            'gpt-3.5-turbo-16k' => 'gpt-35-turbo-16k-0613',
            'gpt-4-1106-preview' => 'gpt-4-1106-preview',
        ],
    ],

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
];
