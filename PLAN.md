# PLAN.md — Actualización a Laravel 13 + hardening de seguridad

> **Fase F0 · Rama:** `Dev-branch` · **Fecha:** 2026-11-13
> **Alcance:** solo local. **Producción (172.17.11.31) no se toca en ninguna fase.**

## 1. Objetivo

1. Migrar el proyecto de **Laravel 10 → Laravel 13** en la rama `Dev-branch`.
2. Aplicar el protocolo **secure-by-default** (gate de seguridad 20 ítems) al código.
3. Establecer CI con `pint`, `phpunit`, `composer audit` y `gitleaks`.

## 2. Criterios de aceptación verificables

| # | Criterio | Verificación |
|---|---|---|
| CA-1 | Framework en Laravel 13 | `php artisan about` muestra `13.x` · `composer show laravel/framework` ≥ 13.42 |
| CA-2 | PHP mínimo 8.3 fijado | `composer.json` → `"php": "^8.3"` |
| CA-3 | Tests en verde | `vendor/bin/phpunit` → 0 fallos |
| CA-4 | CI operativo | `.github/workflows/ci.yml` presente con pint + phpunit + composer audit + gitleaks |
| CA-5 | Test rojo de control | Un test deliberadamente fallido hace fallar el pipeline (demostrado, luego revertido) |
| CA-6 | Rate limit en login | `RateLimiter::for('login')` existe + test que verifica 429 tras intentos fallidos |
| CA-7 | bcrypt ≥ 12 | `config/hashing.php` → `rounds` por defecto ≥ 12 |
| CA-8 | Authz en todas las rutas web | Toda ruta de negocio de `routes/web.php` tiene `->can()` o `authorize()` en el controlador (las rutas sin permiso de negocio —`/login`, `/perfil`, `/logout`, `/force-logout-tab`— quedan cubiertas por los middleware `guest`/`auth`) · matriz recurso×rol completa |
| CA-9 | Tests de authz negativos | Tests que demuestran redirección 302 a `/login` sin sesión (no aplica 401: la app usa sesiones por cookie) y 403 sin permiso para rutas protegidas |
| CA-10 | Cabeceras de seguridad | Middleware que emite CSP, Referrer-Policy, Permissions-Policy, X-Content-Type-Options · test que las verifica |
| CA-11 | Secretos | `gitleaks` sin hallazgos nuevos · sin `dd()`/`dump()` en código |
| CA-12 | Gate 20 ítems | Informe en formato §6 de la skill, sin `✅` sobre ítem incumplido |
| CA-13 | Tests transversales T1–T5 | Ejecutados por subagentes con contexto fresco, en verde |
| CA-14 | Artefactos F7 | SBOM (CycloneDX) + `SECURITY.md` con waivers W1/W2 y fechas de revisión |

## 3. Clasificación de datos

| Dato | Sensibilidad | Notas |
|---|---|---|
| Credenciales de usuario (bcrypt) | Alta | Nunca en logs ni respuestas |
| Nombres, cédulas, correos de trabajadores (PII) | Media | Exportables vía Excel/PDF solo con permiso |
| Inventario (bienes nacionales, hardware) | Baja–Media | Datos del Estado |
| `APP_KEY`, `DB_PASSWORD`, `MAIL_PASSWORD` | Alta | Solo `.env`, nunca versionados |
| Logs de auditoría (activity_log) | Media | Contienen IP + user-agent |

## 4. Superficie expuesta

- **Web** (Livewire + Blade) sobre `/` … `/admin/*`, `/reportes/*` — sesión por cookie.
- **API**: solo `GET /api/user` con `auth:sanctum` (mínima).
- **Ninguna** salida a red nueva en este ciclo (sin webhooks, sin clientes HTTP salientes nuevos).

## 5. Stack (elegido y motivo)

- **Laravel 13** (13.42.x, PHP 8.3–8.5, soporte de seguridad hasta 17-mar-2028): fin del doble EOL de Laravel 10 + PHP 8.1.
- **Livewire 3** (ya en uso, soporta L13) · **spatie/laravel-permission 6.25** · **spatie/laravel-activitylog 4.12** — sin cambio de versión (verificados con soporte L13 el 2026-11-13).
- Estructura de aplicación **L10 clásica mantenida** (la guía oficial 11.x desaconseja migrar la estructura al actualizar).

## 6. Waivers activos (§7 secure-by-default)

| ID | Regla MUST | Motivo | Compensación | Revisión |
|---|---|---|---|---|
| **W1** | Dominio E — secretos nunca commiteados | `encriptar.py` contiene contraseña real en disco local. **Verificado 2026-11-13: el archivo NUNCA estuvo en git** (excluido por `*.py` del `.gitignore`, sin historial, ausente del repo público de GitHub) → el riesgo de filtración pública **no existió** | Riesgo residual: fichero local con contraseña en claro. Acción: borrar `encriptar.py` del disco local y rotar `superpassword26*` si se usa en algún servicio (backlog) | **2026-12-13** |
| **W2** | Dominio A — MFA en superficie administrativa | MFA (TOTP) fuera de alcance de este ciclo | Rate limiting + bcrypt ≥12 + auditoría de permisos por endpoint + registro en `SECURITY.md` | **2027-02-13** |

## 6.b Baseline de SCA (F2, 2026-11-13)

`composer audit --locked` → **65 avisos en 16 paquetes** (gate del CI en rojo hasta F3):

| Paquete | Avisos | Severidad máxima | Nota |
|---|---|---|---|
| `laravel/framework` | 5 | **critical** (CVE-2026-114509 / GHSA-f9x3-7vq9-cph6: TokenGuard auth bypass; afecta `<10.50.4`, `11.x <11.56.1`, `12.x <12.68.0`, `13.x <13.27.0`) | Resuelto al instalar 13.42.x |
| `phpoffice/phpspreadsheet` | 8 | critical | Transitive de maatwebsite/excel |
| `league/commonmark` | 12 | high | Transitive (mary/dompdf) |
| `guzzlehttp/guzzle` | 9 | high | |
| `dompdf/dompdf` | 6 | medium | |
| `symfony/*` (7 paquetes) | 18 | high | Transitive de laravel/framework |
| Otros (`livewire`, `maatwebsite/excel`, `flysystem`, `polyfill`) | 7 | high | |

**Objetivo F3:** `composer audit --locked` → exit 0 tras el update. Cualquier aviso residual se evalúa (fix por bump o waiver §7 con compensación).

## 7. Fuera de alcance (confirmado con el usuario)

- Servidor de producción (código, nginx, phpMyAdmin, TLS, logs).
- `.env` local (se mantiene con sus diferencias respecto a producción).
- `package-lock.json` (divergencia local/prod aceptada y postergada).
- Rotación de la contraseña W1 · MFA W2 · Larastan (CI lleva solo pint + phpunit + composer audit + gitleaks).

## 8. Riesgos heredados

- Laravel 10 EOL (4-feb-2025) con CVEs sin parche → **resuelto por CA-1**.
- Contraseña en `encriptar.py` (local, nunca en git — verificado) → W1 con fecha de revisión; borrar fichero + rotación en backlog.
- phpMyAdmin en `0.0.0.0:8080` en producción → pendiente (fuera de alcance este ciclo).
