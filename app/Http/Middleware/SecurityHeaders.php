<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad (CA-10 / OWASP Secure Headers).
 *
 * - CSP: directivas estrictas; whitelist de los CDNs que la UI usa hoy
 *   (bootstrap-icons, chart.js → cdn.jsdelivr.net; avatares → ui-avatars.com).
 *   'unsafe-inline'/'unsafe-eval' son necesarios para Livewire 3 + Alpine
 *   (compilan directivas con new Function) — ver SECURITY.md para el plan
 *   de migración a nonce-based CSP.
 * - HSTS solo cuando la petición llega por TLS (producción hoy es HTTP puro;
 *   no se emite nada que el navegador ignore).
 * - En APP_ENV=local se permite el origen de Vite HMR para no romper `npm run dev`.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => $this->csp($request),
        ];

        // HSTS: solo sobre TLS (si no, los navegadores lo ignoran igualmente)
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        // No revelar la versión de PHP (X-Powered-By la añade expose_php)
        $response->headers->remove('X-Powered-By');

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function csp(Request $request): string
    {
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net";
        $styleSrc = "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net";
        $fontSrc = "'self' data: https://cdn.jsdelivr.net";
        $imgSrc = "'self' data: https://ui-avatars.com";
        $connectSrc = "'self'";

        // Vite HMR solo en desarrollo local
        if (config('app.env') === 'local') {
            $dev = ' http://localhost:5173 ws://localhost:5173';
            $scriptSrc .= $dev;
            $connectSrc .= $dev;
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "font-src {$fontSrc}",
            "img-src {$imgSrc}",
            "connect-src {$connectSrc}",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}
