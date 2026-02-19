<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CareersFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;
    public $attachments;
    public $subjectPrefix;

    /**
     * Create a new message instance.
     */
    public function __construct($formData, $attachments, $subjectPrefix = '[Club Centenario - Postulación Laboral]')
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
        // Get candidate info from form data
        $candidateName = collect($this->formData)->firstWhere('name', 'nombre_completo')['value'] ?? 
                        collect($this->formData)->firstWhere('name', 'nombre')['value'] ?? 
                        'Candidato';
        
        $positionInterest = collect($this->formData)->firstWhere('name', 'puesto_interes')['value'] ?? 
                           'Posición no especificada';
        
        $senderEmail = collect($this->formData)->firstWhere('name', 'email')['value'] ?? null;

        $subject = "Postulación de {$candidateName} para {$positionInterest}";

        $envelope = new Envelope(
            subject: $this->subjectPrefix . ' ' . $subject,
        );

        // Set reply-to if sender email is available
        if ($senderEmail) {
            $envelope->replyTo($senderEmail, $candidateName);
        }

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.careers-form',
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