<?php

namespace App\Mail;

use App\Models\UserTask;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskDeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public UserTask $task) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Görev Hatırlatıcı: "' . $this->task->title . '" — Yarın son gün!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_deadline',
        );
    }
}
