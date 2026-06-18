<?php

namespace App\Services\Ai;

class StructuredReplyParser
{
    public function parse(string $raw): array
    {
        if (!preg_match('/```subtasks\s*(\[.*?\])\s*```/s', $raw, $matches)) {
            return ['prose' => trim($raw), 'subtasks' => null, 'parent_task_title' => null];
        }

        $prose = trim(str_replace($matches[0], '', $raw));
        $decoded = json_decode($matches[1], true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return ['prose' => $prose, 'subtasks' => null, 'parent_task_title' => null];
        }

        $parentTaskTitle = $decoded['parent_task_title'] ?? null;
        $items = $decoded['items'] ?? $decoded;

        if (!is_array($items)) {
            return ['prose' => $prose, 'subtasks' => null, 'parent_task_title' => null];
        }

        $subtasks = array_values(array_filter(array_map(function ($item) {
            if (!isset($item['title']) || !is_string($item['title'])) {
                return null;
            }
            return [
                'title' => mb_substr($item['title'], 0, 60),
                'estimate_minutes' => isset($item['estimate_minutes']) && is_int($item['estimate_minutes'])
                    ? max(1, min(480, $item['estimate_minutes']))
                    : null,
            ];
        }, $items)));

        return [
            'prose' => $prose,
            'subtasks' => $subtasks ?: null,
            'parent_task_title' => $parentTaskTitle,
        ];
    }
}
