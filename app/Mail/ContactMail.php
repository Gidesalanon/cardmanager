<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nomClient,
        public string $emailClient,
        public string $telephone,
        public string $sujet,
        public string $messageClient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CardManager] Nouveau message : ' . $this->sujet,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }
}