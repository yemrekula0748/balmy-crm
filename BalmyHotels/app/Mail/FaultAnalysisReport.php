<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FaultAnalysisReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $pdfContent,
        public string $reportDate,
        public int    $totalFaults,
        public int    $criticalCount,
        public int    $findingCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🧠 Teknik Arıza Analiz Raporu — ' . $this->reportDate,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fault_analysis_report',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdfContent,
                'ArızaAnaliz_' . str_replace([' ', '/'], '_', $this->reportDate) . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
