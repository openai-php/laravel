<p align="center">
    <p align="center">
        <a href="https://github.com/openai-php/laravel/actions"><img alt="GitHub Workflow Status (master)" src="https://img.shields.io/github/actions/workflow/status/openai-php/laravel/tests.yml?branch=main&label=tests&style=round-square"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/openai-php/laravel"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="Latest Version" src="https://img.shields.io/packagist/v/openai-php/laravel"></a>
        <a href="https://packagist.org/packages/openai-php/laravel"><img alt="License" src="https://img.shields.io/github/license/openai-php/laravel"></a>
    </p>
</p>

------
**OpenAI PHP** for Laravel is a community-maintained PHP API client that allows you to interact with the [Open AI API](https://beta.openai.com/docs/api-reference/introduction). If you or your business relies on this package, it's important to support the developers who have contributed their time and effort to create and maintain this valuable tool:

- Nuno Maduro: **[github.com/sponsors/nunomaduro](https://github.com/sponsors/nunomaduro)**
- Sandro Gehri: **[github.com/sponsors/gehrisandro](https://github.com/sponsors/gehrisandro)**

> **Note:** This repository contains the integration code of the **OpenAI PHP** for Laravel. If you want to use the **OpenAI PHP** client in a framework-agnostic way, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.

## Get Started

> **Requires [PHP 8.1+](https://php.net/releases/)**

First, install OpenAI via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require openai-php/laravel
```

Next, publish the configuration file:

```bash
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

This will create a `config/openai.php` configuration file in your project, which you can modify to your needs
using environment variables: 

```env
OPENAI_API_KEY=sk-...
```

Finally, you may use the `OpenAI` facade to access the OpenAI API:

```php
use OpenAI\Laravel\Facades\OpenAI;

$result = OpenAI::completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is',
]);

echo $result['choices'][0]['text']; // an open-source, widely-used, server-side scripting language.
```

## Usage

For usage examples, take a look at the [openai-php/client](https://github.com/openai-php/client) repository.

## Testing

The `OpenAI` facade comes with a `fake()` method that allows you to fake the API responses.

The fake responses are returned in the order they are provided to the `fake()` method.

All responses are having a `fake()` method that allows you to easily create a response object by only providing the parameters relevant for your test case.

```php
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Completions\CreateResponse;

OpenAI::fake([
    CreateResponse::fake([
        'choices' => [
            [
                'text' => 'awesome!',
            ],
        ],
    ]),
]);

$completion = OpenAI::completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'PHP is ',
]);

expect($completion['choices'][0]['text'])->toBe('awesome!');
```

After the requests have been sent there are various methods to ensure that the expected requests were sent:

```php
// assert completion create request was sent
OpenAI::assertSent(Completions::class, function (string $method, array $parameters): bool {
    return $method === 'create' &&
        $parameters['model'] === 'text-davinci-003' &&
        $parameters['prompt'] === 'PHP is ';
});
```

For more testing examples, take a look at the [openai-php/client](https://github.com/openai-php/client#testing) repository.

---

OpenAI PHP for Laravel is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
