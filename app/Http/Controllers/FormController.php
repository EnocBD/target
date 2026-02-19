<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Block;
use App\Mail\ContactFormMail;
use App\Mail\CareersFormMail;
use App\Mail\AutoReplyMail;
use App\Models\FormSubmission;
use ReCaptcha\ReCaptcha;

class FormController extends Controller
{
    /**
     * Handle form submissions
     */
    public function submit(Request $request)
    {
        try {
            // Get form type
            $formType = $request->input('form_type', 'contacto');
            
            // Get block configuration
            $block = Block::where('block_type', $formType)->where('is_active', 1)->first();
            
            if (!$block) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formulario no encontrado.'
                ], 404);
            }

            // Get form configuration
            $formFields = $block->data->form_fields ?? [];
            $formSettings = $block->data->form_settings ?? [];

            // Validate reCAPTCHA
            if(env('RECAPTCHA_SITE_KEY') && env('RECAPTCHA_SECRET_KEY')) {
                if (!$this->verifyRecaptcha($request)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de verificación reCAPTCHA. Intente nuevamente.'
                    ], 422);
                }
            }

            // Build validation rules
            $validationRules = $this->buildValidationRules($formFields);
            
            // Validate request
            $validator = Validator::make($request->all(), $validationRules);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process form data
            $formData = $this->processFormData($request, $formFields);
            
            // Handle file uploads
            $uploadedFiles = $this->handleFileUploads($request, $formFields);
            
            // Save form submission to database
            $submission = FormSubmission::create([
                'form_type' => $formType,
                'form_data' => $formData,
                'uploaded_files' => $uploadedFiles,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'new'
            ]);

            // Send emails
            $emailResult = $this->sendEmails($formType, $formData, $uploadedFiles, $formSettings, $request);
            
            if (!$emailResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el formulario. Intente nuevamente.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => $formSettings->success_message ?? 'Formulario enviado correctamente.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Form submission error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Build validation rules from form fields configuration
     */
    private function buildValidationRules($formFields)
    {
        $rules = [];
        
        foreach ($formFields as $field) {
            $fieldName = $field->name ?? '';
            $fieldType = $field->type ?? 'text';
            $isRequired = ($field->required ?? 'false') === 'true';
            $validation = $field->validation ?? '';
            
            if (empty($fieldName)) continue;
            
            $fieldRules = [];
            
            // Required validation
            if ($isRequired) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            // Type-specific validations
            switch ($fieldType) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'tel':
                    $fieldRules[] = 'string';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    
                    // File size validation
                    $maxSize = $field->max_size ?? '5MB';
                    $maxSizeKb = $this->convertSizeToKb($maxSize);
                    if ($maxSizeKb) {
                        $fieldRules[] = "max:{$maxSizeKb}";
                    }
                    
                    // File type validation
                    $accept = $field->accept ?? '';
                    if ($accept) {
                        $mimeTypes = $this->getMimeTypesFromExtensions($accept);
                        if (!empty($mimeTypes)) {
                            $fieldRules[] = 'mimes:' . implode(',', $mimeTypes);
                        }
                    }
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                default:
                    $fieldRules[] = 'string';
            }
            
            // Custom validation rules
            if (!empty($validation)) {
                $customRules = explode('|', $validation);
                foreach ($customRules as $rule) {
                    $rule = trim($rule);
                    if (!empty($rule) && !in_array($rule, $fieldRules)) {
                        $fieldRules[] = $rule;
                    }
                }
            }
            
            $rules[$fieldName] = $fieldRules;
            
            // Handle checkbox arrays
            if ($fieldType === 'checkbox') {
                $rules[$fieldName . '.*'] = 'string';
            }
        }
        
        return $rules;
    }

    /**
     * Process form data and format it properly
     */
    private function processFormData(Request $request, $formFields)
    {
        $formData = [];
        
        foreach ($formFields as $field) {
            $fieldName = $field->name ?? '';
            $fieldType = $field->type ?? 'text';
            $fieldLabel = $field->label ?? $fieldName;
            
            if (empty($fieldName)) continue;
            
            $value = $request->input($fieldName);
            
            // Process different field types
            switch ($fieldType) {
                case 'checkbox':
                    if (is_array($value)) {
                        $value = implode(', ', array_filter($value));
                    }
                    break;
                case 'file':
                    // Skip file fields in form data, they're handled separately
                    continue 2;
                default:
                    // Standard processing
                    break;
            }
            
            $formData[] = [
                'label' => $fieldLabel,
                'name' => $fieldName,
                'value' => $value,
                'type' => $fieldType
            ];
        }
        
        return $formData;
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads(Request $request, $formFields)
    {
        $uploadedFiles = [];
        
        foreach ($formFields as $field) {
            $fieldName = $field->name ?? '';
            $fieldType = $field->type ?? 'text';
            $fieldLabel = $field->label ?? $fieldName;
            
            if ($fieldType !== 'file' || empty($fieldName)) continue;
            
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                
                if ($file->isValid()) {
                    // Generate unique filename
                    $timestamp = now()->format('Y-m-d_H-i-s');
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = "{$originalName}_{$timestamp}.{$extension}";
                    
                    // Store file
                    $path = $file->storeAs('form-uploads', $filename, 'public');
                    
                    $uploadedFiles[] = [
                        'label' => $fieldLabel,
                        'name' => $fieldName,
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
        }
        
        return $uploadedFiles;
    }

    /**
     * Send emails based on form type
     */
    private function sendEmails($formType, $formData, $uploadedFiles, $formSettings, Request $request)
    {
        try {
            $recipientEmail = $formSettings->recipient_email ?? 'info@clubcentenario.org.py';
            $subjectPrefix = $formSettings->subject_prefix ?? '[Club Centenario]';
            
            // Send main form email
            if ($formType === 'trabaja-con-nosotros') {
                Mail::to($recipientEmail)->send(new CareersFormMail($formData, $uploadedFiles, $subjectPrefix));
            } else {
                Mail::to($recipientEmail)->send(new ContactFormMail($formData, $uploadedFiles, $subjectPrefix));
            }
            
            // Send auto-reply if enabled
            $autoReply = $formSettings->auto_reply ?? 'false';
            if ($autoReply === 'true') {
                $userEmail = $this->getUserEmail($formData);
                if ($userEmail) {
                    $autoReplyMessage = $formSettings->auto_reply_message ?? '';
                    Mail::to($userEmail)->send(new AutoReplyMail($autoReplyMessage, $subjectPrefix, $formType));
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Email sending error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract user email from form data
     */
    private function getUserEmail($formData)
    {
        foreach ($formData as $field) {
            if (in_array($field->name, ['email', 'correo', 'email_address'])) {
                return $field->value;
            }
        }
        return null;
    }

    /**
     * Convert size string to KB
     */
    private function convertSizeToKb($size)
    {
        if (empty($size)) return null;
        
        $size = strtoupper($size);
        $number = (float) $size;
        
        if (strpos($size, 'MB') !== false) {
            return $number * 1024;
        } elseif (strpos($size, 'KB') !== false) {
            return $number;
        } elseif (strpos($size, 'GB') !== false) {
            return $number * 1024 * 1024;
        }
        
        return $number; // Assume KB if no unit
    }

    /**
     * Get MIME types from file extensions
     */
    private function getMimeTypesFromExtensions($accept)
    {
        $extensions = array_map('trim', explode(',', str_replace('.', '', $accept)));
        $mimeMap = [
            'pdf' => 'pdf',
            'doc' => 'doc',
            'docx' => 'docx',
            'txt' => 'txt',
            'rtf' => 'rtf',
            'jpg' => 'jpg',
            'jpeg' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'svg' => 'svg'
        ];
        
        $mimeTypes = [];
        foreach ($extensions as $ext) {
            if (isset($mimeMap[$ext])) {
                $mimeTypes[] = $mimeMap[$ext];
            }
        }
        
        return $mimeTypes;
    }

    /**
     * Get form for testing/preview purposes
     */
    public function show($layout)
    {
        $block = Block::where('layout', $layout)->where('status', 'active')->first();
        
        if (!$block) {
            abort(404, 'Formulario no encontrado');
        }
        
        return view("blocks.{$layout}", compact('block'));
    }

    /**
     * Verify reCAPTCHA token
     */
    private function verifyRecaptcha(Request $request)
    {
        $secretKey = env('RECAPTCHA_SECRET_KEY');
        
        if (empty($secretKey)) {
            // If no secret key configured, skip validation (for development)
            return true;
        }

        $recaptchaToken = $request->input('g-recaptcha-response');
        
        if (empty($recaptchaToken)) {
            return false;
        }

        $recaptcha = new ReCaptcha($secretKey);
        $response = $recaptcha->verify($recaptchaToken, $request->ip());
        
        return $response->isSuccess() && $response->getScore() >= 0.5;
    }
}