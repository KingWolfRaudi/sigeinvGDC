<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Los tests no dependen del build de front-end: `public/build` está en
        // .gitignore y en CI no existe el manifest de Vite (sin esto, cualquier
        // vista con @vite falla con ViteManifestNotFoundException → 500).
        $this->withoutVite();
    }
}
