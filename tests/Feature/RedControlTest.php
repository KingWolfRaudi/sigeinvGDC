<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CA-5 — Test rojo de control.
 *
 * Demuestra que la suite (y por tanto el pipeline de CI) FALLA ante un test
 * incorrecto. Se ejecuta una vez con `--filter=RedControl`, se verifica el
 * fallo (exit != 0) y se revierte este archivo.
 *
 * Este test NUNCA debe quedar en verde: si lo ves fallar en CI, es correcto.
 */
class RedControlTest extends TestCase
{
    public function test_intencionalmente_fallido_para_demostrar_el_fallo_del_pipeline(): void
    {
        // Afirma lo contrario a la realidad a propósito.
        $this->assertSame(1, 2, 'CA-5: test rojo de control (debe fallar)');
    }
}
