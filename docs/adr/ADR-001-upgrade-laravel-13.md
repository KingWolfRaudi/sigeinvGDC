# ADR-001 — Migración Laravel 10 → 13 con estructura mantenida

- **Fecha:** 2026-11-13 · **Estado:** Aceptado · **Rama:** `Dev-branch`
- **Contexto:** Laravel 10 está en EOL desde 4-feb-2025 con CVEs sin parche (ver `stack-laravel.md` §3). Se necesita soporte de seguridad hasta 17-mar-2028 y PHP ≥ 8.3.

## Decisión

1. **Salto directo 10 → 13** en un solo `composer update`, con los constraints intermedios resueltos de una vez, **verificado por**:
   - `composer why-not laravel/framework 13.x` antes de actualizar (diagnóstico de conflictos),
   - suite de tests en verde tras el update,
   - `php artisan about` confirmando 13.42.x.
   *La guía oficial recomienda 10→11→12→13 un major por vez; se acepta el salto directo porque la suite, aunque pequeña, cubre el arranque de la app, y el riesgo real está en dependencias (verificadas compatibles con L13 en Packagist el 2026-11-13) y en pocos puntos de código identificados (CSRF, Sanctum, configs). Coste de 3 saltos separados ≫ beneficio en este proyecto.*

2. **Mantener la estructura de aplicación Laravel 10** (`app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, 5 providers, `bootstrap/app.php` clásico). La guía oficial 11.x lo dice explícitamente: *"we do NOT recommend that Laravel 10 applications upgrading to Laravel 11 attempt to migrate their application structure"* — L11+ sigue soportando la estructura L10. Se migra estructura solo si una futura versión la elimina.

3. **Dependencias sin cambio de versión** (todas verificadas con soporte L13 el 2026-11-13): livewire `^3.0` (3.8.11), maatwebsite/excel `^3.1` (3.1.70), spatie/laravel-permission `^6.25` (6.25.0), spatie/laravel-activitylog `^4.12` (4.12.3), robsontenorio/mary `^2.8` (2.9.11), barryvdh/laravel-dompdf `^3.1` (3.1.2), spatie/laravel-ignition `^2.0` (2.12.1).

4. **Bumps obligatorios:** `php ^8.1→^8.3`, `laravel/framework ^10.0→^13.0`, `laravel/sanctum ^3.2→^4.0`, `laravel/tinker ^2.8→^3.0`, `phpunit ^10.0→^12.0`, `nunomaduro/collision ^7.0→^8.1`; eliminar `branch-alias: dev-master 10.x-dev`.

## Consecuencias

- **Positivas:** fin del doble EOL; CVEs de L10 sin parche resueltos por el upgrade; `PreventRequestForgery` (CSRF con verificación `Sec-Fetch-Site`), `session.serialization=json` y `cache.serializable_classes=false` disponibles (hardening).
- **Negativas / riesgos:**
  - Cambios de comportamiento L11–L13 (Carbon 3, UUIDv7 en `HasUuids`, `image` sin SVG, `upsert` con `uniqueBy` no vacío) — revisados: el proyecto no usa `HasUuids`, no usa `upsert`, migraciones sin `->change()` ni `float/double`.
  - `session.serialization=json` invalida sesiones activas al desplegar → anotado en el runbook de despliegue futuro (producción no se toca en este ciclo).
  - PHPUnit 10 → 12: schema de `phpunit.xml` y asserts se revisan en F3.

## Alternativas rechazadas

- **3 PRs separados (10→11, 11→12, 12→13):** más auditables pero 3× coste de actualización de lockfile y tests; sin beneficio de seguridad diferido (el destino es L13 igualmente).
- **Laravel Shift (servicio pago):** automatiza el salto; no aporta sobre un salto ya dimensionado y verificado por tests.
- **Migrar estructura a skeleton L11+:** churn enorme (mover middleware/providers/config) sin requisito; desaconsejado oficialmente.
