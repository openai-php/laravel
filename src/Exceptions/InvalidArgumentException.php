<?php

declare(strict_types=1);

namespace OpenAI\Laravel\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class ApiUrlIsMissing extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'The OpenAI API Url is missing. Please publish the [openai.php] configuration file and set the [api_url].'
        );
    }
}
