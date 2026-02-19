<?php

namespace App\Http\Requests;

class ContactFormRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge($this->getCommonRules(), [
            'nombre' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|min:8|max:20',
            'asunto' => 'required|string|min:3|max:200',
            'mensaje' => 'required|string|min:10|max:2000',
            
            // Optional fields that might be present
            'empresa' => 'nullable|string|max:150',
            'sitio_web' => 'nullable|url|max:255',
            'consulta_tipo' => 'nullable|string|in:informacion,membresia,eventos,deportes,instalaciones,otro',
            
            // File uploads
            'archivo.*' => array_merge(['nullable'], $this->getFileRules('100MB', 'pdf,doc,docx,jpg,jpeg,png')),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'nombre.required' => 'Por favor ingrese su nombre completo.',
            'email.required' => 'Por favor ingrese su dirección de email.',
            'email.email' => 'Por favor ingrese un email válido.',
            'telefono.required' => 'Por favor ingrese su número de teléfono.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'asunto.required' => 'Por favor indique el asunto de su consulta.',
            'mensaje.required' => 'Por favor escriba su mensaje o consulta.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'mensaje.max' => 'El mensaje no puede exceder los 2000 caracteres.',
            'sitio_web.url' => 'Por favor ingrese una URL válida para el sitio web.',
        ]);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number
        if ($this->has('telefono')) {
            $this->merge([
                'telefono' => preg_replace('/[^\d\+\-\(\)\s]/', '', $this->telefono)
            ]);
        }

        // Trim whitespace from text fields
        $textFields = ['nombre', 'asunto', 'mensaje', 'empresa'];
        foreach ($textFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => trim($this->get($field))]);
            }
        }

        // Set form type if not present
        if (!$this->has('form_type')) {
            $this->merge(['form_type' => 'contacto']);
        }
    }
}