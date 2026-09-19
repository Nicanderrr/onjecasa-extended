<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyAdminReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $report)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Monthly Business Report - '.$this->report['period_label'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-admin-report',
            with: [
                'report' => $this->report,
            ],
        );
    }
}
