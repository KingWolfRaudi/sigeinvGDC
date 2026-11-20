<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Cabeceras de seguridad — CA-10 / OWASP Secure Headers.
 */
class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present_on_responses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);

        // Sin TLS no se emite HSTS (producción hoy es HTTP puro)
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_emitted_only_over_https(): void
    {
        // URL absoluta con esquema https: garantiza que la petición llegue
        // como segura (withServerVariables no surte efecto en este harness).
        $response = $this->get('https://localhost/login');

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security');
    }

    public function test_csp_script_src_rejects_data_and_blob_uris(): void
    {
        $csp = (string) $this->get('/login')->headers->get('Content-Security-Policy');

        $scriptSrc = '';
        foreach (explode(';', $csp) as $directive) {
            $directive = trim($directive);
            if (str_starts_with($directive, 'script-src ')) {
                $scriptSrc = $directive;
            }
        }

        $this->assertNotSame('', $scriptSrc, 'Falta la directiva script-src');
        $this->assertStringNotContainsString('data:', $scriptSrc);
        $this->assertStringNotContainsString('blob:', $scriptSrc);
    }
}
