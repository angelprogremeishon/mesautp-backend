<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu enlace de acceso a MesaUTP');
    }

    public function content(): Content
    {
        $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        return new Content(view: 'emails.magic-link', with: [
            'url' => "{$frontend}/auth/callback?token={$this->token}",
        ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
