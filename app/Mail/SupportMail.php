<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $messageText;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectText, $messageText, $user)
    {
        $this->subjectText = $subjectText;
        $this->messageText = $messageText;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Complaint: ' . $this->subjectText,
            replyTo: [
                new Address($this->user->email, $this->user->first_name . ' ' . $this->user->last_name)
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.support',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
