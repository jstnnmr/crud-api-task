<?php

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use Generator;

class GroqProvider implements AiProvider
{
    public function __construct(
        protected array $config
    ) {}

    public function chat(array $messages): string
    {
        $response = Http::withToken($this->config['key'])
            ->timeout(60)
            ->post($this->config['url'], [
                'model'       => $this->config['model'],
                'messages'    => $messages,
                'max_tokens'  => config('ai.limits.max_response_tokens'),
                'stream'      => false,
            ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Groq chat failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('AI provider error: ' . $response->status());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function stream(array $messages): Generator
    {
        $response = Http::withToken($this->config['key'])
            ->timeout(120)
            ->withHeaders(['Accept' => 'text/event-stream'])
            ->withOptions(['stream' => true])
            ->send('POST', $this->config['url'], [
                'json' => [
                    'model'      => $this->config['model'],
                    'messages'   => $messages,
                    'max_tokens' => config('ai.limits.max_response_tokens'),
                    'stream'     => true,
                ]
            ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Groq stream failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            yield '[API Error: ' . $response->status() . ']';
            return;
        }

        $body = $response->getBody();

        if ($body === null) {
            yield '[API Error: Empty response body]';
            return;
        }

        $resource = null;
        try {
            $resource = $body->detach();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Groq stream detach failed', [
                'error' => $e->getMessage(),
            ]);
        }

        if (!$resource || !is_resource($resource)) {
            yield '[API Error: Unable to read response stream]';
            return;
        }

        while (!feof($resource)) {
            $line = fgets($resource);
            if ($line === false) {
                break;
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, 'data: ')) {
                $dataStr = trim(substr($line, 6));
                if ($dataStr === '[DONE]') {
                    break;
                }

                $data = json_decode($dataStr, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                if (isset($data['choices'][0]['delta']['content'])) {
                    yield $data['choices'][0]['delta']['content'];
                }
            }
        }

        fclose($resource);
    }
}
