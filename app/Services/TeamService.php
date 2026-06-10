<?php

namespace App\Services;

use App\Mail\TaskInvitationMail;
use App\Models\User;
use App\Repositories\TeamRepository;
use App\Support\ServiceReturn;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamService
{
    public function __construct(
        protected TeamRepository $teamRepository
    ) {}

    public function getMyTasks(int $userId): ServiceReturn
    {
        $tasks = $this->teamRepository->findTasksForUser(userId: $userId);
        return ServiceReturn::success(data: $tasks);
    }

    public function getCollaborators(int $taskId, int $userId): ServiceReturn
    {
        $task = $this->teamRepository->findOwnedTask(taskId: $taskId, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found.', status: 404);
        }

        $task->load('collaborators');
        return ServiceReturn::success(data: $task->collaborators);
    }

    public function invite(int $userId, array $data): ServiceReturn
    {
        $task = $this->teamRepository->findOwnedTask(taskId: $data['task_id'], userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found.', status: 404);
        }

        $invited = User::where('email', $data['invited_email'])->first();
        if ($invited && $task->collaborators()->where('user_id', $invited->id)->exists()) {
            return ServiceReturn::error(message: 'User is already a collaborator.', status: 422);
        }

        $existing = $task->invitations()
            ->where('invited_email', $data['invited_email'])
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return ServiceReturn::error(message: 'Invitation already sent to this email.', status: 422);
        }

        $token = Str::random(40);

        $task->invitations()->create([
            'invited_by'    => $userId,
            'invited_email' => $data['invited_email'],
            'token'         => $token,
            'status'        => 'pending',
        ]);

        try {
            $inviter = User::findOrFail($userId);
            Mail::to($data['invited_email'])->send(new TaskInvitationMail(
                task: $task,
                inviter: $inviter,
                invitedEmail: $data['invited_email'],
                token: $token
            ));
        } catch (\Exception $e) {
            // Email is best-effort; invitation is still saved in DB
        }

        $this->teamRepository->logActivity(
            taskId: $task->id,
            userId: $userId,
            action: 'invited',
            changes: ['invited_email' => $data['invited_email']]
        );

        return ServiceReturn::success(message: 'Invitation sent successfully.');
    }

    public function getInvitations(int $userId): ServiceReturn
    {
        $user = User::findOrFail($userId);
        $invitations = $this->teamRepository->findPendingInvitationsByEmail(email: $user->email);
        return ServiceReturn::success(data: $invitations);
    }

    public function acceptInvitation(int $userId, string $token): ServiceReturn
    {
        $invitation = $this->teamRepository->findInvitationByToken(token: $token);
        if (!$invitation || $invitation->status !== 'pending') {
            return ServiceReturn::error(message: 'Invalid or expired invitation.', status: 404);
        }

        $user = User::findOrFail($userId);
        if ($user->email !== $invitation->invited_email) {
            return ServiceReturn::error(message: 'This invitation is not for you.', status: 403);
        }

        $invitation->task->collaborators()->attach($userId, ['role' => 'collaborator']);
        $invitation->update(['status' => 'accepted']);

        $this->teamRepository->logActivity(
            taskId: $invitation->task_id,
            userId: $userId,
            action: 'joined',
            changes: ['email' => $user->email]
        );

        return ServiceReturn::success(message: 'You have joined the task.');
    }

    public function declineInvitation(int $userId, string $token): ServiceReturn
    {
        $invitation = $this->teamRepository->findInvitationByToken(token: $token);
        if (!$invitation || $invitation->status !== 'pending') {
            return ServiceReturn::error(message: 'Invalid or expired invitation.', status: 404);
        }

        $user = User::findOrFail($userId);
        if ($user->email !== $invitation->invited_email) {
            return ServiceReturn::error(message: 'This invitation is not for you.', status: 403);
        }

        $invitation->update(['status' => 'declined']);

        return ServiceReturn::success(message: 'Invitation declined.');
    }

    public function removeCollaborator(int $taskId, int $ownerId, int $collaboratorId): ServiceReturn
    {
        $task = $this->teamRepository->findOwnedTask(taskId: $taskId, userId: $ownerId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found.', status: 404);
        }

        $task->collaborators()->detach($collaboratorId);

        $this->teamRepository->logActivity(
            taskId: $task->id,
            userId: $ownerId,
            action: 'removed_collaborator',
            changes: ['removed_user_id' => $collaboratorId]
        );

        return ServiceReturn::success(message: 'Collaborator removed.');
    }

    public function getActivities(int $taskId, int $userId): ServiceReturn
    {
        $task = $this->teamRepository->findTaskForUser(taskId: $taskId, userId: $userId);
        if (!$task) {
            return ServiceReturn::error(message: 'Task not found.', status: 404);
        }

        $activities = $this->teamRepository->getActivities(taskId: $taskId);
        return ServiceReturn::success(data: $activities);
    }
}
