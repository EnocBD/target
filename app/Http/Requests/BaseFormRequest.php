<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe ser un email válido.',
            'string' => 'El campo :attribute debe ser texto.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no puede tener más de :max caracteres.',
            'file' => 'El campo :attribute debe ser un archivo.',
            'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
            'image' => 'El campo :attribute debe ser una imagen.',
            'url' => 'El campo :attribute debe ser una URL válida.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'in' => 'El campo :attribute seleccionado no es válido.',
            'regex' => 'El formato del campo :attribute no es válido.',
            'unique' => 'El :attribute ya ha sido tomado.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'size' => 'El campo :attribute debe ser :size.',
            'between' => 'El campo :attribute debe estar entre :min y :max.',
            'digits' => 'El campo :attribute debe tener :digits dígitos.',
            'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
            'distinct' => 'El campo :attribute tiene un valor duplicado.',
            'filled' => 'El campo :attribute debe tener un valor.',
            'gt' => 'El campo :attribute debe ser mayor que :value.',
            'gte' => 'El campo :attribute debe ser mayor o igual que :value.',
            'lt' => 'El campo :attribute debe ser menor que :value.',
            'lte' => 'El campo :attribute debe ser menor o igual que :value.',
            'not_in' => 'El campo :attribute seleccionado no es válido.',
            'not_regex' => 'El formato del campo :attribute no es válido.',
            'present' => 'El campo :attribute debe estar presente.',
            'same' => 'El campo :attribute y :other deben coincidir.',
            'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes valores: :values',
            'ends_with' => 'El campo :attribute debe terminar con uno de los siguientes valores: :values',
            'uuid' => 'El campo :attribute debe ser un UUID válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'nombre_completo' => 'nombre completo',
            'email' => 'email',
            'telefono' => 'teléfono',
            'asunto' => 'asunto',
            'mensaje' => 'mensaje',
            'curriculum' => 'curriculum vitae',
            'carta_presentacion' => 'carta de presentación',
            'puesto_interes' => 'puesto de interés',
            'experiencia_laboral' => 'experiencia laboral',
            'disponibilidad_horaria' => 'disponibilidad horaria',
            'edad' => 'edad',
            'form_type' => 'tipo de formulario',
        ];
    }

    /**
     * Get common validation rules
     */
    protected function getCommonRules(): array
    {
        return [
            'form_type' => 'required|string|in:contacto,trabaja-con-nosotros',
        ];
    }

    /**
     * Get file validation rules
     */
    protected function getFileRules($maxSize = '5MB', $mimes = 'pdf,doc,docx'): array
    {
        $maxSizeKb = $this->convertSizeToKb($maxSize);
        
        return [
            'file',
            "max:{$maxSizeKb}",
            "mimes:{$mimes}"
        ];
    }

    /**
     * Convert size string to KB
     */
    private function convertSizeToKb($size): int
    {
        $size = strtoupper($size);
        $number = (float) $size;
        
        if (strpos($size, 'MB') !== false) {
            return (int) ($number * 1024);
        } elseif (strpos($size, 'KB') !== false) {
            return (int) $number;
        } elseif (strpos($size, 'GB') !== false) {
            return (int) ($number * 1024 * 1024);
        }
        
        return (int) $number; // Assume KB if no unit
    }
}