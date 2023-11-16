<?php

namespace OpenAI\Laravel\Exceptions;

use Exception;

class InvalidBaseUriException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'The OpenAI base URI is invalid. Please check the [base_uri] configuration value.'
        );
    }
}
