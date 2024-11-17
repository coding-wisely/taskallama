<?php

namespace CodingWisely\Taskallama;

use CodingWisely\Taskallama\Services\TaskallamaService;
use CodingWisely\Taskallama\Traits\MakesHttpRequest;

class Taskallama
{
    use MakesHttpRequest;

    protected ?TaskallamaService $modelService;

    /**
     * selectedModel
     *
     * @var mixed
     */
    protected mixed $selectedModel = null;

    /**
     * model
     *
     * @var mixed
     */
    protected mixed $model = null;

    /**
     * prompt
     *
     * @var mixed
     */
    protected mixed $prompt = null;


    protected mixed $format = null; // Initialize with a default value
    /**
     * options
     *
     * @var mixed
     */
    protected mixed $options = [];

    /**
     * stream
     *
     * @var bool
     */
    protected bool $stream = false;

    /**
     * raw
     *
     * @var mixed
     */
    protected mixed $raw = false;

    /**
     * agent
     *
     * @var mixed
     */
    protected mixed $agent = null;

    /**
     * Base64 encoded image.
     *
     * @var string|null
     */
    protected ?string $image = null;

    /**
     * keep alive
     *
     * @ var mixed
     */
    protected string $keepAlive = "5m";

    /**
     * Ollama class constructor.
     */
    public function __construct(TaskallamaService $modelService)
    {
        $this->modelService = $modelService;
        $this->model = config('taskallama.model');
        $this->format = config('taskallama.default_format', 'json'); // Set a default format
    }

    /**
     * Sets the agent for generation.
     *
     * @param string $agent
     * @return $this
     */
    public function agent(string $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * Sets the prompt for generation.
     *
     * @param string $prompt
     * @return $this
     */
    public function prompt(string $prompt): static
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Sets the model for subsequent operations.
     *
     * @param string $model
     * @return $this
     */
    public function model(string $model): static
    {
        $this->selectedModel = $model;
        $this->model = $model;
        return $this;
    }

    /**
     * Sets the format for generation.
     *
     * @param string $format
     * @return $this
     */
    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Sets additional options for generation.
     *
     * @param array $options
     * @return $this
     */
    public function options(array $options = []): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Sets whether to use streaming in the response.
     *
     * @param bool $stream
     * @return $this
     */
    public function stream(bool $stream = false): static
    {
        $this->stream = $stream;
        return $this;
    }

    /**
     * Sets whether to return the response in raw format.
     *
     * @param bool $raw
     * @return $this
     */
    public function raw(bool $raw): static
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * Controls how long the model will stay loaded into memory following the request
     *
     * @param string $keepAlive
     * @return $this
     */
    public function keepAlive(string $keepAlive): static
    {
        $this->keepAlive = $keepAlive;
        return $this;
    }

    /**
     * Lists available local models.
     *
     * @return array
     */
    public function models(): array
    {
        return $this->modelService->listLocalModels();
    }

    /**
     * Shows information about the selected model.
     *
     * @return array
     */
    public function show(): array
    {
        return $this->modelService->showModelInformation($this->selectedModel);
    }

    /**
     * Copies a model.
     *
     * @param string $destination
     * @return $this
     * @throws \Exception
     */
    public function copy(string $destination): static
    {
        $this->modelService->copyModel($this->selectedModel, $destination);
        return $this;
    }

    /**
     * Deletes a model.
     *
     * @return $this
     * @throws \Exception
     */
    public function delete(): static
    {
        $this->modelService->deleteModel($this->selectedModel);
        return $this;
    }

    /**
     * Pulls a model.
     *
     * @return $this
     * @throws \Exception
     */
    public function pull(): static
    {
        $this->modelService->pullModel($this->selectedModel);
        return $this;
    }

    /**
     * Sets an image for generation.
     *
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function image(string $imagePath): static
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file does not exist: $imagePath");
        }

        $this->image = base64_encode(file_get_contents($imagePath));
        return $this;
    }


    /**
     * Generates embeddings from the selected model.
     *
     * @param string $prompt
     * @return array
     * @throws \Exception
     */
    public function embeddings(string $prompt): array
    {
        return $this->modelService->generateEmbeddings($this->selectedModel, $prompt);
    }

    /**
     * Generates content using the specified model.
     *
     * @return array
     */
    public function ask(): array
    {
        $requestData = [
            'model' => $this->model,
            'system' => $this->agent,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'raw' => $this->raw,
            'keep_alive' => $this->keepAlive,
        ];

        if ($this->image) {
            $requestData['images'] = [$this->image];
        }

        return $this->sendRequest('/api/generate', $requestData);
    }

    /**
     * Generates a chat completion using the specified model and conversation.
     *
     * @param array $conversation
     * @return array
     */
    public function chat(array $conversation): array
    {
        return $this->sendRequest('/api/chat', [
            'model' => $this->model,
            'messages' => $conversation,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
        ]);
    }
}
