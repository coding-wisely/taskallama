<?php

use CodingWisely\Taskallama\Services\TaskallamaService;
use CodingWisely\Taskallama\Taskallama;
use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    $this->taskallama = new Taskallama(new TaskallamaService);
});

it('trigger proper instance of Taskallama', function () {
    expect($this->taskallama)->toBeInstanceOf(Taskallama::class);
});
it('sets properties correctly and returns instance', function ($method, $value) {
    expect($this->taskallama->$method($value))->toBeInstanceOf(Taskallama::class);
})->with([
    'agent' => ['agent', 'Act as Vladimir Nikolic'],
    'prompt' => ['prompt', 'Who are you?'],
    'model' => ['model', 'llama3.2'],
    'format' => ['format', 'json'],
    'options' => ['options', ['temperature' => 0.7]], // Pass an array instead of a string
    'stream' => ['stream', false],
    'raw' => ['raw', true],
]);
it('correctly processes ask method with real API call without stream', function () {
    $response = $this->taskallama->agent('You are a weather expert...')
        ->prompt('Why is the sky blue? answer only in 4 words')
        ->model('llama3.2')
        ->options(['temperature' => 0.8])
        ->stream(false)
        ->ask();

    expect($response)->toBeInstanceOf(Response::class);
});
it('correctly processes ask method with real API call with stream', function () {
    $response = $this->taskallama->agent('You are a weather expert...')
        ->prompt('Why is the sky blue? answer only in 4 words')
        ->model('llama3.2')
        ->options(['temperature' => 0.8])
        ->stream(true)
        ->ask();

    expect($response)->toBeArray();
});
it('lists available local models', function () {
    $models = $this->taskallama::listLocalModels();
    expect($models)->toBeArray();
});

it('shows information about the selected model', function () {
    $models = $this->taskallama::listLocalModels();
    $model = $models['models'][0];
    $this->taskallama->model($model['name']);
    $info = $this->taskallama::getModelInfo($model['name']);
    expect($info)->toBeArray();
});
