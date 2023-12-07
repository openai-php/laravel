<?php
namespace OpenAI\Laravel;

use OpenAI;
use InvalidArgumentException;

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

class AzureOpenai
{
    protected static ?AzureOpenai $instance = null;
    protected static array $config = [];
    protected static array $openAis = [];
    protected static string $parentFunction = '';

    public static function instance($config = []): ?AzureOpenai
    {
        if (!static::$instance) {
            static::$instance = new static;
            static::$config = $config;
        }
        return static::$instance;
    }

    /**
     * @description 切换逻辑
     * @param $name
     * @param $arguments
     * @return AzureOpenai|null
     */
    public function __call($name, $arguments)
    {
        //如果是父类方法返回本身
        if (method_exists(OpenAI\Client::class, $name)) {
            self::$parentFunction = $name;
            return self::$instance;
        }
        $model = $arguments[0]['model'] ?? '';
        if (!isset(self::$openAis[$model])) {
            self::$openAis[$model] = $this->createOpenAi($model);
        }
        //切换模型调用方法
        if (!method_exists(self::$parentFunction, $name)) {
            return self::$openAis[$model]->{self::$parentFunction}()->$name(...$arguments);
        }
        return self::$openAis[$model]->{$name}();
    }

    /**
     * @description create openai
     * @param string $model
     * @return OpenAI\Client
     */
    protected function createOpenAi(string $model = ''): OpenAI\Client
    {
        $baseUrl = self::$config['base_url'] ?? '';
        $apiKey = self::$config['api_key'] ?? '';
        $apiVersion = self::$config['api_version'] ?? '';

        if ($model && !isset(self::$config['models'][$model])) {
            throw new InvalidArgumentException('Model not found in config');
        } else {
            $model = self::$config['models'][self::$config['model']] ?? '';
        }

        if (empty($apiKey) || empty($baseUrl) || empty($model) || empty($apiVersion)) {
            throw new InvalidArgumentException('Azure OpenAI config is missing');
        }
        return OpenAI::factory()
            ->withBaseUri($baseUrl . 'openai/deployments/' . $model)
            ->withHttpHeader('api-key', $apiKey)
            ->withQueryParam('api-version', $apiVersion)
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('openai.request_timeout', 30)]))
            ->make();
    }
}
