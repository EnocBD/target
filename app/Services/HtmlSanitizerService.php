<?php

namespace App\Services;

class HtmlSanitizerService
{
    /**
     * Sanitiza contenido HTML usando métodos básicos de PHP
     */
    public function sanitize(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        return $this->basicSanitize($html);
    }

    /**
     * Sanitización inteligente que no usa HTMLPurifier para evitar timeouts
     */
    public function smartSanitize(string $html, string $context = 'default'): string
    {
        if (empty($html)) {
            return '';
        }

        return $this->basicSanitize($html);
    }

    /**
     * Sanitización estricta
     */
    public function sanitizeStrict(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        return $this->basicSanitize($html);
    }

    /**
     * Sanitización para contextos específicos
     */
    public function sanitizeForContext(string $html, string $context): string
    {
        return $this->basicSanitize($html);
    }

    /**
     * Sanitización básica usando solo PHP nativo
     */
    private function basicSanitize(string $html): string
    {
        // Remover scripts y eventos peligrosos
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $html = preg_replace('/vbscript\s*:/i', '', $html);
        $html = preg_replace('/data\s*:/i', '', $html);

        // Escapar otros elementos potencialmente peligrosos
        $html = str_replace(['<object', '<embed'], ['&lt;object', '&lt;embed'], $html);

        return $html;
    }

    /**
     * Valida si el HTML contiene elementos potencialmente peligrosos
     */
    public function validateHtml(string $html): array
    {
        $issues = [];

        // Verificar scripts inline
        if (preg_match('/<script[^>]*>.*?<\/script>/is', $html)) {
            $issues[] = 'Contiene etiquetas script que serán removidas';
        }

        // Verificar eventos JavaScript
        if (preg_match('/on\w+\s*=/i', $html)) {
            $issues[] = 'Contiene eventos JavaScript que serán removidos';
        }

        // Verificar protocolos peligrosos
        if (preg_match('/(?:javascript|vbscript|data):/i', $html)) {
            $issues[] = 'Contiene protocolos potencialmente peligrosos';
        }

        return $issues;
    }

    /**
     * Crea el directorio de caché si no existe (placeholder)
     */
    public function ensureCacheDirectory(): void
    {
        // No hacer nada por ahora
    }
}