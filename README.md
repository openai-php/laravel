<p align="center">
    <p align="center">
        <a href="https://github.com/openai-php/laravel/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/openai-php/laravel/tests.yml?branch=main&label=tests&style=round-square"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/openai-php/laravel"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="Latest Version" src="https://img.shields.io/packagist/v/openai-php/laravel"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="License" src="https://img.shields.io/github/license/openai-php/laravel"></a>
    </p>
</p>

------
**OpenAI PHP** for Laravel is a community-maintained PHP API client that allows you to interact with the [Open AI API](https://platform.openai.com/docs/api-reference/introduction). If you or your business relies on this package, it's important to support the developers who have contributed their time and effort to create and maintain this valuable tool:

- Nuno Maduro: **[github.com/sponsors/nunomaduro](https://github.com/sponsors/nunomaduro)**
- Sandro Gehri: **[github.com/sponsors/gehrisandro](https://github.com/sponsors/gehrisandro)**

> **Note:** This repository contains the integration code of the **OpenAI PHP** for Laravel. If you want to use the **OpenAI PHP** client in a framework-agnostic way, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.


## Get Started

> **Requires [PHP 8.2+](https://www.php.net/releases/)**

First, install OpenAI via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require openai-php/laravel
```

Next, execute the install command:

```bash
php artisan openai:install
```

This will create a `config/openai.php` configuration file in your project, which you can modify to your needs
using environment variables.
Blank environment variables for the OpenAI API key and organization id are already appended to your `.env` file.

```env
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...
```

Finally, you may use the `OpenAI` facade to access the OpenAI API:

```php
use OpenAI\Laravel\Facades\OpenAI;

$response = OpenAI::responses()->create([
    'model' => 'gpt-5',
    'input' => 'Hello!',
]);

echo $response->outputText; // Hello! How can I assist you today?
```

## Configuration

Configuration is done via environment variables or directly in the configuration file (`config/openai.php`).

### OpenAI API Key and Organization

Specify your OpenAI API Key and organization. This will be
used to authenticate with the OpenAI API - you can find your API key
and organization on your OpenAI dashboard, at https://openai.com.

```env
OPENAI_API_KEY=
OPENAI_ORGANIZATION=
```

### OpenAI Project

For implementations that require a project ID, you can specify 
the OpenAI project ID in your environment variables.

```env
OPENAI_PROJECT=proj_...
```

### OpenAI API Base URL

The base URL for the OpenAI API. By default, this is set to `api.openai.com/v1`.

```env
OPENAI_BASE_URL=
```

### Request Timeout

The timeout may be used to specify the maximum number of seconds to wait
for a response. By default, the client will time out after 30 seconds.

```env
OPENAI_REQUEST_TIMEOUT=
```

## Usage

For usage examples, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.

## Testing

The `OpenAI` facade comes with a `fake()` method that allows you to fake the API responses.

The fake responses are returned in the order they are provided to the `fake()` method.

All responses are having a `fake()` method that allows you to easily create a response object by only providing the parameters relevant for your test case.

```php
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;

OpenAI::fake([
    CreateResponse::fake([
        'choices' => [
            [
                'text' => 'awesome!',
            ],
        ],
    ]),
]);

$response = OpenAI::responses()->create([
    'model' => 'gpt-5',
    'input' => 'PHP is ',
]);

expect($response->outputText)->toBe('awesome!');
```

After the requests have been sent there are various methods to ensure that the expected requests were sent:

```php
use OpenAI\Resources\Responses;

// assert completion create request was sent
OpenAI::assertSent(Responses::class, function (string $method, array $parameters): bool {
    return $method === 'create' &&
        $parameters['model'] === 'gpt-5' &&
        $parameters['prompt'] === 'PHP is ';
});
```

For more testing examples, take a look at the [openai-php/client](https://github.com/openai-php/client#testing) repository.

---

OpenAI PHP for Laravel is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
