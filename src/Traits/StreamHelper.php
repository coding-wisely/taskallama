<?php

namespace CodingWisely\Taskallama\Traits;

use Psr\Http\Message\ResponseInterface;

trait StreamHelper
{
    protected static function doProcessStream(ResponseInterface $response, \Closure $handleJsonObject): array
    {
        $body = $response->getBody();
        // Use a buffer to hold incomplete JSON object parts
        $buffer = '';

        $jsonObjects = [];

        while (! $body->eof()) {
            $chunk = $body->read(256);
            $buffer .= $chunk;

            // Split the buffer by newline as a delimiter
            while (($pos = strpos($buffer, "\n")) !== false) {
                $json = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                // Attempt to decode the JSON object
                $data = json_decode($json, true);
                $data = $data['response'] ?? $data;

                // Check if JSON decoding was successful
                // if so, pass the object to the handler
                if ($data !== null) {
                    $handleJsonObject($data);
                    $jsonObjects[] = $data;

                    // Ensure real-time output for streams
                    ob_flush();
                    flush();
                } else {
                    // If JSON decoding fails, it means this is an incomplete object,
                    // So, we append this part back to the buffer to be processed with the next chunk
                    $buffer = $json."\n".$buffer;
                    break;
                }
            }
        }

        // Process any remaining data in the buffer
        if (! empty($buffer)) {
            $data = json_decode($buffer, true);
            $data = $data['response'] ?? $data;

            if ($data !== null) {
                $handleJsonObject($data);
                $jsonObjects[] = $data;

                // Ensure final real-time output
                ob_flush();
                flush();
            } else {
                // we shouldn't hit this, except when ollama is unexpectedly killed
                throw new \Exception('Incomplete JSON object remaining: '.$buffer);
            }
        }

        return $jsonObjects;
    }
}
