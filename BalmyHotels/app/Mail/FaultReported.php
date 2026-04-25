<?php

namespace App\Mail;

use App\Models\Fault;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FaultReported extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Fault $fault) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔧 Yeni Arıza Bildirimi — ' . $this->fault->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fault_reported',
        );
    }
}
