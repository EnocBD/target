<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customMessage;
    public $subjectPrefix;
    public $formType;

    /**
     * Create a new message instance.
     */
    public function __construct($customMessage, $subjectPrefix, $formType = 'contacto')
    {
        $this->customMessage = $customMessage;
        $this->subjectPrefix = $subjectPrefix;
        $this->formType = $formType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Confirmación de ';
        
        if ($this->formType === 'trabaja-con-nosotros') {
            $subject .= 'Postulación Recibida';
        } else {
            $subject .= 'Mensaje Recibido';
        }

        return new Envelope(
            subject: $this->subjectPrefix . ' ' . $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auto-reply',
            with: [
                'customMessage' => $this->customMessage,
                'formType' => $this->formType,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}