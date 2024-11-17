<?php

namespace CodingWisely\Taskallama\Services;

use CodingWisely\Taskallama\Traits\MakesHttpRequest;

class TaskallamaService
{
    use MakesHttpRequest;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('taskallama.url');
    }

    public function listLocalModels()
    {
        return $this->sendRequest('/api/tags', [], 'get');
    }

    public function showModelInformation(string $modelName)
    {
        return $this->sendRequest('/api/show', ['name' => $modelName]);
    }

    public function copyModel(string $source, string $destination)
    {
        return $this->sendRequest('/api/copy', [
            'source' => $source,
            'destination' => $destination,
        ]);
    }

    public function deleteModel(string $modelName)
    {
        return $this->sendRequest('/api/delete', ['name' => $modelName], 'delete');
    }

    public function pullModel(string $modelName)
    {
        return $this->sendRequest('/api/pull', ['name' => $modelName]);
    }

    public function generateEmbeddings(string $modelName, string $prompt)
    {
        return $this->sendRequest('/api/embeddings', [
            'model' => $modelName,
            'prompt' => $prompt,
        ]);
    }
}
