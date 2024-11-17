<?php
namespace CodingWisely\Taskallama;

use CodingWisely\Taskallama\Services\TaskallamaService;
use CodingWisely\Taskallama\Traits\MakesHttpRequest;
use CodingWisely\Taskallama\Traits\StreamHelper;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Response;

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
    protected string $keepAlive = "5m";

    /**
     * Singleton pattern to manage the Taskallama instance.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (!self::$instance) {
            self::$instance = app(self::class);
        }
        return self::$instance;
    }

    /**
     * Sets the agent for generation.
     *
     * @param string $agent
     * @return static
     */
    public static function agent(string $agent): static
    {
        $instance = self::getInstance();
        $instance->agent = $agent;
        return $instance;
    }

    /**
     * Sets the prompt for generation.
     *
     * @param string $prompt
     * @return static
     */
    public static function prompt(string $prompt): static
    {
        $instance = self::getInstance();
        $instance->prompt = $prompt;
        return $instance;
    }

    /**
     * Sets the model for subsequent operations.
     *
     * @param string $model
     * @return static
     */
    public static function model(string $model): static
    {
        $instance = self::getInstance();
        $instance->selectedModel = $model;
        $instance->model = $model;
        return $instance;
    }

    /**
     * Sets the format for generation.
     *
     * @param string $format
     * @return static
     */
    public static function format(string $format): static
    {
        $instance = self::getInstance();
        $instance->format = $format;
        return $instance;
    }

    /**
     * Sets additional options for generation.
     *
     * @param array $options
     * @return static
     */
    public static function options(array $options = []): static
    {
        $instance = self::getInstance();
        $instance->options = array_merge($instance->options, $options); // Merge with existing options
        return $instance;
    }

    /**
     * Sets whether to use streaming in the response.
     *
     * @param bool $stream
     * @return static
     */
    public static function stream(bool $stream = false): static
    {
        $instance = self::getInstance();
        $instance->stream = $stream;
        return $instance;
    }

    /**
     * Sets whether to return the response in raw format.
     *
     * @param bool $raw
     * @return static
     */
    public static function raw(bool $raw): static
    {
        $instance = self::getInstance();
        $instance->raw = $raw;
        return $instance;
    }

    /**
     * Controls how long the model will stay loaded into memory following the request
     *
     * @param string $keepAlive
     * @return static
     */
    public static function keepAlive(string $keepAlive): static
    {
        $instance = self::getInstance();
        $instance->keepAlive = $keepAlive;
        return $instance;
    }

    /**
     * Generates content using the specified model.
     */
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

        return $instance->sendRequest('/api/generate', $requestData);
    }

    /**
     * Generates a chat completion using the specified model and conversation.
     *
     * @param array $conversation
     * @return array
     */
    public static function chat(array $conversation): array
    {
        $instance = self::getInstance();

        return $instance->sendRequest('/api/chat', [
            'model' => $instance->model,
            'messages' => $conversation,
            'format' => $instance->format,
            'options' => $instance->options ?: (object)[], // Ensure `options` is an object or empty map
            'stream' => $instance->stream,
        ]);
    }

    /**
     * @param StreamInterface $body
     * @param \Closure $handleJsonObject
     * @return array
     * @throws \Exception
     */
    public static function processStream(StreamInterface $body, \Closure $handleJsonObject): array
    {
        return self::doProcessStream($body, $handleJsonObject);
    }
}
