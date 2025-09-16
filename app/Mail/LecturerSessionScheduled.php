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

class LecturerSessionScheduled extends Mailable
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
            subject: 'Your Attendance Session Has Been Scheduled!',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $classeTitle = $this->session->classe->department->title . ' ' . $this->session->classe->level->title;

        $sectionCount = Classe::where('department_id', $this->session->classe->department->id)
            ->where('level_id', $this->session->classe->level->id)
            ->distinct('section_id')
            ->count('section_id');

        $classeTitle .= $sectionCount > 1 ? ' ' . $this->session->classe->section->title : '';

        $sessionStartsAt = Carbon::parse($this->session->starts_at)->format('l d F, Y H:i');

        $sessionEndsAt = Carbon::parse($this->session->ends_at)->format('l d F, Y H:i');

        return new Content(
            markdown: 'mail.session.lecturer.scheduled',
            with: [
                'classeTitle' => $classeTitle,
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
