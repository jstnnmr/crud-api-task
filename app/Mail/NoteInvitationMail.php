<?php

namespace App\Mail;

use App\Models\Note;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoteInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Note $note;
    public User $inviter;

    public function __construct(Note $note, User $inviter)
    {
        $this->note = $note;
        $this->inviter = $inviter;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to a note - EaseTask',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.note-invitation',
        );
    }
}
