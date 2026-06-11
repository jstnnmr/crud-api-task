<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OverdueTaskMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $tasks;
    public string $userName;

    public function __construct(Collection $tasks, string $userName)
    {
        $this->tasks = $tasks;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        $count = $this->tasks->count();
        return new Envelope(
            subject: "⏰ {$count} task(s) need attention - EaseTask",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue-task',
        );
    }
}
