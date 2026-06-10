<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiAssistantService
{
    public function chat(array $messages, array $tasks = []): string
    {
        $apiKey = config('services.groq.key');
        if (empty($apiKey)) {
            throw new \RuntimeException(
                'Groq API key is not configured. Set GROQ_API_KEY in your .env file.'
            );
        }

        $model = config('services.groq.model', 'llama3-70b-8192');

        $systemPrompt = $this->buildSystemPrompt($tasks);

        $groqMessages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($messages as $msg) {
            $groqMessages[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'      => $model,
            'messages'   => $groqMessages,
            'max_tokens' => 1024,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Groq API error: ' . $response->body()
            );
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function buildSystemPrompt(array $tasks): string
    {
        $taskList = '';
        if (!empty($tasks)) {
            $rows = [];
            foreach ($tasks as $task) {
                $due = $task['due_date'] ?? '—';
                $rows[] = sprintf(
                    '- [%s] %s (priority: %s, due: %s)',
                    $task['status'] ?? 'pending',
                    $task['title'],
                    $task['priority'] ?? 'medium',
                    $due
                );
            }
            $taskList = "Here are the user's current tasks:\n" . implode("\n", $rows);
        }

        return <<<PROMPT
You are an AI productivity assistant integrated into a task management app. 
Your role is to help the user manage, prioritize, and reason about their tasks.

Keep responses concise and actionable.

$taskList
PROMPT;
    }
}
