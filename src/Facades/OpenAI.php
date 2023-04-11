<?php

declare(strict_types=1);

namespace OpenAI\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use OpenAI\Contracts\ResponseContract;
use OpenAI\Laravel\Testing\OpenAIFake;
use OpenAI\Responses\StreamResponse;

/**
 * @method static \OpenAI\Resources\Audio audio()
 * @method static \OpenAI\Resources\Chat chat()
 * @method static \OpenAI\Resources\Completions completions()
 * @method static \OpenAI\Resources\Embeddings embeddings()
 * @method static \OpenAI\Resources\Edits edits()
 * @method static \OpenAI\Resources\Files files()
 * @method static \OpenAI\Resources\FineTunes fineTunes()
 * @method static \OpenAI\Resources\Images images()
 * @method static \OpenAI\Resources\Models models()
 * @method static \OpenAI\Resources\Moderations moderations()
 */
final class OpenAI extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'openai';
    }

    /**
     * @param  array<array-key, ResponseContract|StreamResponse|string>  $responses
     */
    public static function fake(array $responses = []): OpenAIFake /** @phpstan-ignore-line */
    {
        $fake = new OpenAIFake($responses);
        self::swap($fake);

        return $fake;
    }
}
