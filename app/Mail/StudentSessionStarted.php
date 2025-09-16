<?php

namespace App\Mail;

use App\Models\Classe;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentSessionStarted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Session $session)
    {
        //
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Attendance Session Has Started!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $sessionStartsAt = Carbon::parse($this->session->starts_at)->format('l d F, Y H:i');
        $sessionEndsAt = Carbon::parse($this->session->ends_at)->format('l d F, Y H:i');

        return new Content(
            markdown: 'mail.session.student.started',
            with: [
                'starts_at' => $sessionStartsAt,
                'ends_at' => $sessionEndsAt
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
