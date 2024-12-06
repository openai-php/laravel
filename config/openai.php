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
    | Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI is the root URL that the client will use to make requests.
    | By default, the client will use the OpenAI API base URI.
    | The schema can be specified (http or https).
    | e.g.: http://localhost:11434/v1 for a local Ollama instance.
    */
    'base_uri' => env('OPENAI_BASE_URI', 'api.openai.com/v1'),
];
