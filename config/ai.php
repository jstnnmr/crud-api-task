<?php

return [
    'default' => env('AI_PROVIDER', 'opencodego'),

    'providers' => [
        'opencodego' => [
            'key'   => env('OPENAICODE_API_KEY'),
            'model' => env('AI_MODEL', 'deepseek-v4-flash'),
            'url'   => 'https://opencode.ai/zen/go/v1/chat/completions',
        ],
        'groq' => [
            'key'   => env('GROQ_API_KEY'),
            'model' => env('AI_MODEL', 'llama-3.3-70b-versatile'),
            'url'   => 'https://api.groq.com/openai/v1/chat/completions',
        ],
    ],

    'limits' => [
        'max_response_tokens' => 1024,
        'max_context_messages' => 20,
    ],
];
