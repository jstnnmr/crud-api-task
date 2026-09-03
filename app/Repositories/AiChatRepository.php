<?php

namespace App\Repositories;

use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use Illuminate\Database\Eloquent\Collection;

class AiChatRepository
{
    public function getSessions(int $userId): Collection
    {
        return AiChatSession::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function findSession(int $id, int $userId): ?AiChatSession
    {
        return AiChatSession::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function createSession(int $userId, ?string $title = null): AiChatSession
    {
        return AiChatSession::create([
            'user_id' => $userId,
            'title'   => $title,
        ]);
    }

    public function getMessages(int $sessionId, int $userId): Collection
    {
        $session = $this->findSession($sessionId, $userId);
        if (!$session) {
            return new Collection();
        }

        return $session->messages()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function addMessage(int $sessionId, string $role, string $content, ?array $metadata = null): AiChatMessage
    {
        // Touch the parent session's updated_at timestamp so it bubbles to the top of lists
        $session = AiChatSession::findOrFail($sessionId);
        $session->touch();

        return AiChatMessage::create([
            'session_id' => $sessionId,
            'role'       => $role,
            'content'    => $content,
            'metadata'   => $metadata,
        ]);
    }

    public function getLatestMessage(int $sessionId, int $userId): ?AiChatMessage
    {
        $session = $this->findSession($sessionId, $userId);
        if (!$session) {
            return null;
        }

        return $session->messages()
            ->where('role', 'assistant')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function deleteSession(int $id, int $userId): void
    {
        $session = $this->findSession($id, $userId);
        if ($session) {
            $session->delete();
        }
    }

    public function forkSession(int $sessionId, int $userId): ?AiChatSession
    {
        $original = $this->findSession($sessionId, $userId);
        if (!$original) {
            return null;
        }

        $newSession = $this->createSession($userId, 'Fork of ' . ($original->title ?? 'Untitled Session'));

        foreach ($original->messages as $message) {
            $this->addMessage($newSession->id, $message->role, $message->content);
        }

        return $newSession;
    }
}
