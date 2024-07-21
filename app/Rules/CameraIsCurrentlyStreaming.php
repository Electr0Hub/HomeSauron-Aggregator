<?php

namespace App\Rules;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CameraIsCurrentlyStreaming implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $client = new Client();

        try {
            $response = $client->get($value, [
                'stream' => true,
                'sink' => fopen('php://temp', 'r+')
            ]);

            $body = $response->getBody();
            $frame = $body->read(1024);
            if (!Str::contains($frame, config('streaming.boundary'))) {
                $fail('Wrong Data From Camera');
                return;
            }
        }
        catch (\Exception $exception) {
            $fail($exception->getMessage());
            return;
        }
    }
}
