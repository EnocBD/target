<?php

namespace App\Http\Requests;

class CareersFormRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge($this->getCommonRules(), [
            'nombre_completo' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|min:8|max:20',
            'edad' => 'nullable|numeric|min:18|max:70',
            'puesto_interes' => 'required|string|max:200',
            
            // Experience and availability
            'experiencia_laboral' => 'nullable|string|max:2000',
            'disponibilidad_horaria' => 'required|string|in:mañana,tarde,tiempo_completo,fines_de_semana',
            'carta_presentacion' => 'nullable|string|max:2000',
            
            // CV and documents (required)
            'curriculum' => array_merge(['required'], $this->getFileRules('5MB', 'pdf,doc,docx')),
            
            // Optional additional documents
            'carta_recomendacion' => array_merge(['nullable'], $this->getFileRules('5MB', 'pdf,doc,docx')),
            'certificados' => array_merge(['nullable'], $this->getFileRules('100MB', 'pdf,jpg,jpeg,png')),
            'portfolio' => array_merge(['nullable'], $this->getFileRules('100MB', 'pdf,jpg,jpeg,png')),
            
            // Additional fields
            'nivel_educacion' => 'nullable|string|in:secundario,terciario,universitario,postgrado',
            'idiomas' => 'nullable|string|max:500',
            'referencias' => 'nullable|string|max:1000',
            'salario_pretendido' => 'nullable|string|max:100',
            
            // Checkbox arrays
            'areas_interes' => 'nullable|array',
            'areas_interes.*' => 'string|max:100',
            'habilidades' => 'nullable|array',
            'habilidades.*' => 'string|max:100',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'nombre_completo.required' => 'Por favor ingrese su nombre completo.',
            'email.required' => 'Por favor ingrese su dirección de email.',
            'email.email' => 'Por favor ingrese un email válido.',
            'telefono.required' => 'Por favor ingrese su número de teléfono.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'puesto_interes.required' => 'Por favor indique el puesto de su interés.',
            'edad.numeric' => 'La edad debe ser un número.',
            'edad.min' => 'Debe ser mayor de edad (18 años).',
            'edad.max' => 'La edad máxima permitida es 70 años.',
            'disponibilidad_horaria.required' => 'Por favor indique su disponibilidad horaria.',
            'disponibilidad_horaria.in' => 'Seleccione una opción de disponibilidad válida.',
            'curriculum.required' => 'El curriculum vitae es obligatorio.',
            'curriculum.file' => 'El curriculum debe ser un archivo.',
            'curriculum.mimes' => 'El curriculum debe ser un archivo PDF, DOC o DOCX.',
            'curriculum.max' => 'El curriculum no puede ser mayor a 5MB.',
            'experiencia_laboral.max' => 'La descripción de experiencia no puede exceder los 2000 caracteres.',
            'carta_presentacion.max' => 'La carta de presentación no puede exceder los 2000 caracteres.',
            'nivel_educacion.in' => 'Seleccione un nivel educativo válido.',
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'areas_interes' => 'áreas de interés',
            'habilidades' => 'habilidades',
            'nivel_educacion' => 'nivel de educación',
            'idiomas' => 'idiomas',
            'referencias' => 'referencias',
            'salario_pretendido' => 'salario pretendido',
            'carta_recomendacion' => 'carta de recomendación',
            'certificados' => 'certificados',
            'portfolio' => 'portafolio',
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
        $textFields = [
            'nombre_completo', 'puesto_interes', 'experiencia_laboral', 
            'carta_presentacion', 'idiomas', 'referencias', 'salario_pretendido'
        ];
        
        foreach ($textFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => trim($this->get($field))]);
            }
        }

        // Convert edad to integer if present
        if ($this->has('edad') && !empty($this->edad)) {
            $this->merge(['edad' => (int) $this->edad]);
        }

        // Set form type if not present
        if (!$this->has('form_type')) {
            $this->merge(['form_type' => 'trabaja-con-nosotros']);
        }

        // Handle checkbox arrays - ensure they're arrays
        $arrayFields = ['areas_interes', 'habilidades'];
        foreach ($arrayFields as $field) {
            if ($this->has($field) && !is_array($this->get($field))) {
                // If it's a string, split by comma
                $value = $this->get($field);
                if (is_string($value)) {
                    $this->merge([$field => array_filter(array_map('trim', explode(',', $value)))]);
                }
            }
        }
    }
}