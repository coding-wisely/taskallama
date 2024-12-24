<?php

namespace CodingWisely\Taskallama;

use CodingWisely\Taskallama\Services\TaskallamaService;
use CodingWisely\Taskallama\Traits\MakesHttpRequest;
use CodingWisely\Taskallama\Traits\StreamHelper;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Taskallama
{
    use MakesHttpRequest;
    use StreamHelper;

    protected static ?self $instance = null;

    protected ?TaskallamaService $modelService;

    protected mixed $selectedModel = null;

    protected mixed $model = null;

    protected mixed $prompt = null;

    protected mixed $format = null;

    protected mixed $options = [];

    protected bool $stream = false;

    protected mixed $raw = false;

    protected mixed $agent = null;

    protected ?string $image = null;

    protected string $keepAlive = '5m';

    public function __construct(TaskallamaService $modelService)
    {
        $this->modelService = $modelService;
    }

    // Method to set raw property
    public static function raw($raw): static
    {
        $instance = self::getInstance();
        $instance->raw = $raw;

        return $instance;
    }

    public static function getInstance(): static
    {
        if (! self::$instance) {
            self::$instance = app(self::class);
        }

        return self::$instance;
    }

    public static function agent(string $agent): static
    {
        $instance = self::getInstance();
        $instance->agent = $agent;

        return $instance;
    }

    public static function prompt(string $prompt): static
    {
        $instance = self::getInstance();
        $instance->prompt = $prompt;

        return $instance;
    }

    public static function model(string $model): static
    {
        $instance = self::getInstance();
        $instance->selectedModel = $model;
        $instance->model = $model;

        return $instance;
    }

    public static function format(string $format): static
    {
        $instance = self::getInstance();
        $instance->format = $format;

        return $instance;
    }

    public static function options(array $options = []): static
    {
        $instance = self::getInstance();
        $instance->options = array_merge($instance->options, $options);

        return $instance;
    }

    public static function stream(bool $stream = false): static
    {
        $instance = self::getInstance();
        $instance->stream = $stream;

        return $instance;
    }

    public static function ask(): array|Response
    {
        $instance = self::getInstance();
        $requestData = [
            'model' => $instance->model,
            'system' => $instance->agent,
            'prompt' => $instance->prompt,
            'format' => $instance->format,
            'options' => $instance->options,
            'stream' => $instance->stream,
            'raw' => $instance->raw,
            'keep_alive' => $instance->keepAlive,
        ];

        if ($instance->image) {
            $requestData['images'] = [$instance->image];
        }

        $response = $instance->sendRequest('/api/generate', $requestData);

        if ($instance->stream && $response instanceof ResponseInterface) {
            return self::doProcessStream($response, function ($chunk) {
                try {
                    logger()->info('taskallama stream chunk:', [$chunk]);
                    echo $chunk;
                    ob_flush();
                    flush();
                } catch (\Exception $e) {
                    logger()->error('Error processing stream chunk:', [
                        'error' => $e->getMessage(),
                        'chunk' => $chunk,
                    ]);
                }
            });
        }

        return $response;
    }

    public static function chat(array $conversation): array
    {
        $instance = self::getInstance();

        $response = $instance->sendRequest('/api/chat', [
            'model' => $instance->model,
            'messages' => $conversation,
            'format' => $instance->format,
            'options' => $instance->options ?: (object) [],
            'stream' => $instance->stream,
        ]);

        // Handle streaming
        if ($instance->stream && $response instanceof ResponseInterface) {
            return self::doProcessStream($response, function ($chunk) {
                try {
                    logger()->info('taskallama stream chunk:', [$chunk]);
                    echo $chunk;
                    ob_flush();
                    flush();
                } catch (\Exception $e) {
                    logger()->error('Error processing stream chunk:', [
                        'error' => $e->getMessage(),
                        'chunk' => $chunk,
                    ]);
                }
            });
        }

        return $response;
    }

    public static function embeddings(string $prompt): array
    {
        $instance = self::getInstance();

        return $instance->sendRequest('/api/embeddings', [
            'model' => $instance->model,
            'prompt' => $prompt,
        ]);
    }

    public static function listLocalModels(): array
    {
        $instance = self::getInstance();

        return $instance->modelService->listLocalModels();
    }

    public static function getModelInfo(string $model): array
    {
        $instance = self::getInstance();

        return $instance->modelService->showModelInformation($model);
    }

    public static function pull(string $model): array
    {
        $instance = self::getInstance();

        return $instance->modelService->pullModel($model);
    }

    public static function delete(string $model): array
    {
        $instance = self::getInstance();

        return $instance->modelService->deleteModel($model);
    }
}
