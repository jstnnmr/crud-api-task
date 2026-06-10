<?php

namespace App\Services;

use App\Mail\NoteInvitationMail;
use App\Models\User;
use App\Repositories\NoteRepository;
use App\Support\ServiceReturn;
use Illuminate\Support\Facades\Mail;

class NoteService
{
    public function __construct(
        protected NoteRepository $noteRepository
    ) {}

    public function getAll(int $userId): ServiceReturn
    {
        $notes = $this->noteRepository->findAllForUser(userId: $userId);
        return ServiceReturn::success(data: $notes);
    }

    public function getById(int $id, int $userId): ServiceReturn
    {
        $note = $this->noteRepository->findForUser(id: $id, userId: $userId);
        if (!$note) {
            return ServiceReturn::error(message: 'Note not found.', status: 404);
        }
        return ServiceReturn::success(data: $note);
    }

    public function create(int $userId, array $data): ServiceReturn
    {
        $note = $this->noteRepository->create([
            'user_id' => $userId,
            'title'   => $data['title'] ?? 'Untitled',
            'content' => $data['content'] ?? '',
            'color'   => $data['color'] ?? '#fff9c4',
        ]);

        return ServiceReturn::success(data: $note, message: 'Note created.', status: 201);
    }

    public function update(int $id, int $userId, array $data): ServiceReturn
    {
        $note = $this->noteRepository->findForUser(id: $id, userId: $userId);
        if (!$note) {
            return ServiceReturn::error(message: 'Note not found.', status: 404);
        }

        $this->noteRepository->update(id: $id, data: $data);
        $note = $this->noteRepository->findForUser(id: $id, userId: $userId);

        return ServiceReturn::success(data: $note, message: 'Note updated.');
    }

    public function delete(int $id, int $userId): ServiceReturn
    {
        $note = $this->noteRepository->findOwned(id: $id, userId: $userId);
        if (!$note) {
            return ServiceReturn::error(message: 'Note not found.', status: 404);
        }

        $this->noteRepository->delete(id: $id);
        return ServiceReturn::success(message: 'Note deleted.');
    }

    public function invite(int $noteId, int $userId, array $data): ServiceReturn
    {
        $note = $this->noteRepository->findOwned(id: $noteId, userId: $userId);
        if (!$note) {
            return ServiceReturn::error(message: 'Note not found.', status: 404);
        }

        $invited = User::where('email', $data['invited_email'])->first();
        if (!$invited) {
            return ServiceReturn::error(message: 'User not found with that email.', status: 404);
        }

        if ($note->collaborators()->where('user_id', $invited->id)->exists()) {
            return ServiceReturn::error(message: 'User is already a collaborator.', status: 422);
        }

        $this->noteRepository->addCollaborator(note: $note, user: $invited);

        try {
            Mail::to($invited->email)->send(new NoteInvitationMail(
                note: $note,
                inviter: User::findOrFail($userId)
            ));
        } catch (\Exception $e) {
            // Email is best-effort
        }

        return ServiceReturn::success(message: 'Collaborator added.');
    }

    public function removeCollaborator(int $noteId, int $userId, int $collaboratorId): ServiceReturn
    {
        $note = $this->noteRepository->findOwned(id: $noteId, userId: $userId);
        if (!$note) {
            return ServiceReturn::error(message: 'Note not found.', status: 404);
        }

        $this->noteRepository->removeCollaborator(note: $note, userId: $collaboratorId);
        return ServiceReturn::success(message: 'Collaborator removed.');
    }
}
