<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
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
        $replyTo = [];

        // Si le client a fourni un email, on peut lui répondre directement
        if (!empty($this->emailClient)) {
            $replyTo[] = new Address($this->emailClient, $this->nomClient);
        }

        return new Envelope(
            subject: '[DONAMI-CHRIST] Nouveau message : ' . $this->sujet,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }
}