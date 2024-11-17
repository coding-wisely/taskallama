<?php

namespace CodingWisely\Taskallama\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

trait MakesHttpRequest
{
    /**
     * sendRequest
     *
     * @return mixed
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post')
    {
        $url = config('taskallama.url').$urlSuffix;

        if (! empty($data['stream']) && $data['stream'] === true) {
            $client = new Client;
            $response = $client->request($method, $url, [
                'json' => $data,
                'stream' => true,
                'timeout' => config('taskallama.connection.timeout'),
            ]);

            return $response;
        } else {
            $response = Http::timeout(config('taskallama.connection.timeout'))->$method($url, $data);

            return $response->json();
        }
    }
}
