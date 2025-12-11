<?php

namespace Tests;

use OpenAI\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase;

class WebhookTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
