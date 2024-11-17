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
     */
    protected mixed $selectedModel = null;

    /**
     * model
     */
    protected mixed $model = null;

    /**
     * prompt
     */
    protected mixed $prompt = null;

    protected mixed $format = null; // Initialize with a default value

    /**
     * options
     */
    protected mixed $options = [];

    /**
     * stream
     */
    protected bool $stream = false;

    /**
     * raw
     */
    protected mixed $raw = false;

    /**
     * agent
     */
    protected mixed $agent = null;

    /**
     * Base64 encoded image.
     */
    protected ?string $image = null;

    /**
     * keep alive
     *
     * @ var mixed
     */
    protected string $keepAlive = '5m';

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
     * @return $this
     */
    public function keepAlive(string $keepAlive): static
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    /**
     * Lists available local models.
     */
    public function models(): array
    {
        return $this->modelService->listLocalModels();
    }

    /**
     * Shows information about the selected model.
     */
    public function show(): array
    {
        return $this->modelService->showModelInformation($this->selectedModel);
    }

    /**
     * Copies a model.
     *
     * @return $this
     *
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
     *
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
     *
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
     * @return $this
     *
     * @throws \Exception
     */
    public function image(string $imagePath): static
    {
        if (! file_exists($imagePath)) {
            throw new \Exception("Image file does not exist: $imagePath");
        }

        $this->image = base64_encode(file_get_contents($imagePath));

        return $this;
    }

    /**
     * Generates embeddings from the selected model.
     *
     * @throws \Exception
     */
    public function embeddings(string $prompt): array
    {
        return $this->modelService->generateEmbeddings($this->selectedModel, $prompt);
    }

    /**
     * Generates content using the specified model.
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
