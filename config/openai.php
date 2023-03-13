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

    /**
     * ------------------------------------------------------------------------
     * Guzzle config
     * ------------------------------------------------------------------------
     * more configuration information https://docs.guzzlephp.org/en/stable/
     * can set the proxy in this way, if necessary
     */
    'guzzle' => [
//        'proxy' => [
//            'https' => env('OPENAI_API_HTTPS_PROXY'),
//        ],
    ]
];
