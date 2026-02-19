<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Generar un nonce único para scripts inline
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        // Configurar CSP para admin vs public
        if ($request->is('admin*')) {
            $csp = $this->getAdminCSP($nonce);
        } else {
            $csp = $this->getPublicCSP($nonce);
        }

        $response->headers->set('Content-Security-Policy', $csp);

        // Headers adicionales de seguridad
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * CSP policy para el área de administración
     */
    private function getAdminCSP(string $nonce): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "media-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "frame-src 'self'",
        ];

        return implode('; ', $policies);
    }

    /**
     * CSP policy para el sitio público
     */
    private function getPublicCSP(string $nonce): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://code.jquery.com https://www.youtube.com https://player.vimeo.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "media-src 'self' https:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "frame-src 'self' https://www.youtube.com https://player.vimeo.com",
        ];

        return implode('; ', $policies);
    }
}