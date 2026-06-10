<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public User $inviter;
    public string $invitedEmail;
    public string $acceptUrl;

    public function __construct(Task $task, User $inviter, string $invitedEmail, string $token)
    {
        $this->task = $task;
        $this->inviter = $inviter;
        $this->invitedEmail = $invitedEmail;
        $this->acceptUrl = url('/team/invitations/' . $token . '/accept');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to a task - EaseTask',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-invitation',
        );
    }
}
