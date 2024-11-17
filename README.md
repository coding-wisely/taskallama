# Taskallama: Laravel Integration with Ollama LLM API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codingwisely/taskallama.svg?style=for-the-badge&logo=packagist)](https://packagist.org/packages/codingwisely/taskallama)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/codingwisely/taskallama/fix-php-code-style-issues.yml?branch=main&label=Code%20Style&style=for-the-badge&logo=github)](https://github.com/coding-wisely/taskallama/actions/workflows/fix-php-code-style-issues.yml?branch=main)
[![Total Downloads](https://img.shields.io/packagist/dt/codingwisely/taskallama.svg?style=for-the-badge&logo=packagist)](https://packagist.org/packages/codingwisely/taskallama)

**Taskallama** is a Laravel package that provides seamless integration with Ollama's LLM API. 
It simplifies generating AI-powered content, from professional task writing to conversational agents, with minimal effort. Whether you're building a task management system, an HR assistant for job posts, or blog content generation, Taskallama has you covered.

---

## Features

- Simple API for generating AI responses via the Ollama LLM.
- Supports task creation, conversational AI, embeddings, and more.
- Customizable agent personalities for tailored responses.
- Integration with Laravel Livewire for real-time interactions.
- Configurable options like streaming, model selection, and temperature.

---

---

## Prerequisites

1. **Ollama Installation**
    - Taskallama requires [Ollama](https://ollama.com/) to be installed and running locally on your machine. You can download and install Ollama from their official website:
        - [Ollama Installation Guide](https://ollama.com/)

2. **Ollama Configuration**
    - By default, Taskallama connects to Ollama at `http://127.0.0.1:11434`. Ensure that Ollama is running and accessible at this address. You can update the `OLLAMA_URL` in the config file if it's hosted elsewhere.

3. **System Requirements**
    - PHP `^8.3` or higher.
    - Laravel `^11.0` or higher.

---

## Installation

You can install the package via composer:

```bash
composer require codingwisely/taskallama
```

Next, you should publish the package's configuration file:

```bash
php artisan vendor:publish --tag="taskallama-config"
```

This will publish a `taskallama.php` file in your `config` directory where you can configure your Ollama API key and other settings.

```php
return [
    'model' => env('OLLAMA_MODEL', 'llama3.2'),
    'default_format' => 'json',
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello Taskavelian, how can I assist you today?'),
    'connection' => [
        'timeout' => env('OLLAMA_CONNECTION_TIMEOUT', 300),
    ],
];
```

### Usage

#### Basic Example

Generate a response using a prompt:

```php
use CodingWisely\Taskallama\Facades\Taskallama;

$response = Taskallama::agent('You are a professional task creator...')
    ->prompt('Write a task for implementing a new feature in a SaaS app.')
    ->model('llama3.2')
    ->options(['temperature' => 0.5])
    ->stream(false)
    ->ask();

return $response['response'];
```
#### Chat Example

Create a conversational agent:

```php
use CodingWisely\Taskallama\Facades\Taskallama;
$messages = [
    ['role' => 'user', 'content' => 'Tell me about Laravel'],
    ['role' => 'assistant', 'content' => 'Laravel is a PHP framework for web development.'],
    ['role' => 'user', 'content' => 'Why is it so popular?'],
];

$response = Taskallama::agent('You are a Laravel expert.')
    ->model('llama3.2')
    ->options(['temperature' => 0.7])
    ->chat($messages);
```

#### Livewire Integration Example

Integrate Taskallama into a Livewire component for real-time task generation:

```php
namespace App\Livewire;

use CodingWisely\Taskallama\Taskallama;
use Livewire\Component;

class AskTaskallama extends Component
{
    public $question = '';
    public $response = '';

    public function ask()
    {
        if (empty(trim($this->question))) {
            $this->response = "Please provide a valid question.";
            return;
        }

        try {
            $this->response = Taskallama::agent('You are a task-writing assistant.')
                ->prompt($this->question)
                ->model('llama3.2')
                ->options(['temperature' => 0])
                ->stream(false)
                ->ask()['response'] ?? "No response received.";
        } catch (\Exception $e) {
            $this->response = "Error: " . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.ask-taskallama');
    }
}
```
#### Embeddings Example

Generate embeddings for advanced search or semantic analysis:
```php
$embeddings = Taskallama::agent('Embedding Assistant')
    ->model('llama3.2')
    ->options(['temperature' => 0.5])
    ->ask();

print_r($embeddings);
```
### Additional Methods

#### List Local Models
```php
$models = Taskallama::getInstance()->listLocalModels();
print_r($models);
```
#### Retrieve Model Information
```php
$modelInfo = Taskallama::getInstance()->getModelInfo('llama3.2');
print_r($modelInfo);
```

#### Retrieve Model Settings
```php
$modelSettings = Taskallama::getInstance()->getModelSettings('llama3.2');
print_r($modelSettings);
```

#### Pull or Delete a Model

If you're pulling model, make sure you set this a background job, as it may take a while to download the model.

```php
$pullModel = Taskallama::getInstance()->pull('mistral');
$deleteModel = Taskallama::getInstance()->delete('mistral');
```

### Testing
Run the tests with:

```bash
composer test
```

### License

This package is open-source software licensed under the MIT License. Please see the [LICENSE.md](LICENSE.md) file for more information.
