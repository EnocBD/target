<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;
    public $attachments;
    public $subjectPrefix;

    /**
     * Create a new message instance.
     */
    public function __construct($formData, $attachments, $subjectPrefix = '[Club Centenario - Contacto]')
    {
        $this->formData = $formData;
        $this->attachments = $attachments;
        $this->subjectPrefix = $subjectPrefix;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Try to get the subject from form data
        $subject = collect($this->formData)->firstWhere('name', 'asunto')['value'] ?? 'Nuevo mensaje de contacto';
        
        // Get sender name and email
        $senderName = collect($this->formData)->firstWhere('name', 'nombre')['value'] ?? 
                     collect($this->formData)->firstWhere('name', 'nombre_completo')['value'] ?? 
                     'Usuario del sitio web';
        
        $senderEmail = collect($this->formData)->firstWhere('name', 'email')['value'] ?? null;

        $envelope = new Envelope(
            subject: $this->subjectPrefix . ' ' . $subject,
        );

        // Set reply-to if sender email is available
        if ($senderEmail) {
            $envelope->replyTo($senderEmail, $senderName);
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: [
                'formData' => $this->formData,
                'attachments' => $this->attachments,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        foreach ($this->attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment['path'])) {
                $attachments[] = Attachment::fromPath(storage_path('app/public/' . $attachment['path']))
                    ->as($attachment['original_name'])
                    ->withMime($attachment['mime_type']);
            }
        }
        
        return $attachments;
    }
}