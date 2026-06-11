<?php

namespace App\Repositories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NoteRepository
{
    public function findAllForUser(int $userId, int $perPage = 6): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Note::where('user_id', $userId)
            ->orWhereHas('collaborators', fn($q) => $q->where('user_id', $userId))
            ->with(['owner', 'collaborators'])
            ->latest('updated_at')
            ->paginate($perPage);
    }

    public function findOwned(int $id, int $userId): ?Note
    {
        return Note::where('user_id', $userId)->find($id);
    }

    public function findForUser(int $id, int $userId): ?Note
    {
        return Note::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('collaborators', fn($cq) => $cq->where('user_id', $userId));
        })->with(['owner', 'collaborators'])->find($id);
    }

    public function create(array $data): Note
    {
        return Note::create($data);
    }

    public function update(int $id, array $data): Note
    {
        $note = Note::findOrFail($id);
        $note->update($data);
        return $note;
    }

    public function delete(int $id): void
    {
        Note::findOrFail($id)->delete();
    }

    public function addCollaborator(Note $note, User $user, string $role = 'collaborator'): void
    {
        $note->collaborators()->attach($user->id, ['role' => $role]);
    }

    public function removeCollaborator(Note $note, int $userId): void
    {
        $note->collaborators()->detach($userId);
    }
}
