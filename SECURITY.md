# SECURITY.md — sigeinvGDC

> **Alcance:** repositorio local y rama `Dev-branch`. La producción (172.17.11.31) no se modifica en este ciclo.
> **Última revisión:** 2026-11-24 · **Próxima revisión global:** 2027-02-24

## 1. Versiones soportadas

| Componente | Versión en el proyecto | Mínimo recomendado | Notas |
|---|---|---|---|
| PHP | 8.3.36 (local) · CI fija **8.3.37** | **8.3.37** (release de seguridad 2026-11-19, fuente: php.net releases 8.3) | Soporte de seguridad 8.3 hasta 2027-12-31 · ver R7 |
| Laravel | **13.44.1** | ≥ **13.27.0** (GHSA-f9x3-7vq9-cph6; NVD cita 13.29.0 — ver §5) | Soporte de seguridad hasta 2028-03-17 |
| Composer (local) | 2.9.4 | **≥ 2.10.3** | ⚠️ 2.9.4 afectado por CVE-2026-59944 (ver R9); el CI instala el Composer actual de `setup-php` |
| gitleaks (CI) | **8.30.1** | 8.30.1 | Descarga verificada por SHA-256 en el workflow |
| PHPUnit | 12.5.44 | ≥ 12.5 | Sin advisories |

## 2. Modelo de seguridad (resumen)

- **Autenticación:** sesión por cookie (`laravel_session`, `HttpOnly`, `SameSite=Lax`, `expire_on_close=true`). El login es una acción Livewire; no hay endpoints de autenticación por token expuestos.
- **Contraseñas:** bcrypt con coste **12** (`config/hashing.php`), salt único por usuario, verificación timing-safe de Laravel (`Hash::check`).
- **Autorización:** `spatie/laravel-permission` + `->can()` en rutas + `authorize()` en controladores + `abort_if(Gate::denies())` en componentes Livewire + `@can` en la UI. Matriz completa en [`docs/matriz-permisos.md`](docs/matriz-permisos.md).
- **Rate limiting:** 5 intentos fallidos/minuto por `identificador + IP` (`RateLimiter::for('login')` + `LoginThrottle`); el 6.º recibe **429**.
- **Cabeceras:** CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`; HSTS solo sobre TLS; `X-Powered-By` eliminado (`header_remove` en `public/index.php`).
- **CSRF:** middleware `PreventRequestForgery` activo en el grupo web (Laravel 13).
- **CORS:** allowlist explícita (`APP_URL` / `CORS_ALLOWED_ORIGINS`), métodos y cabeceras acotados.
- **Secretos:** `.env` fuera de git; `gitleaks` limpio en repo e historial.
- **Dependencias:** `composer audit --locked` → **0 advisories** (2026-11-24). SBOM CycloneDX en [`docs/sbom/composer-sbom.json`](docs/sbom/composer-sbom.json) (148 componentes, 1:1 con `composer.lock`).

## 3. Waivers activos

| ID | Regla | Justificación | Compensación | Revisión |
|---|---|---|---|---|
| **W1** | Dominio E — secretos nunca en el repositorio | `encriptar.py` contiene una contraseña real **solo en disco local**. Verificado el 2026-11-13: el fichero **nunca estuvo en git** (excluido por `*.py`, sin historial y ausente del remoto). El riesgo de filtración pública no existió. | Borrar el fichero local y rotar la credencial si se usa en algún servicio (backlog). No se versiona ningún valor. | **2026-12-13** |
| **W2** | Dominio A — MFA en superficie administrativa | MFA (TOTP) fuera del alcance de este ciclo por decisión del usuario. | Rate limiting + bcrypt ≥ 12 + autorización por endpoint + registro de accesos fallidos. | **2027-02-13** |

## 4. Riesgos residuales (propuestos, pendientes de confirmación del usuario)

| ID | Riesgo | Impacto | Mitigación actual | Revisión |
|---|---|---|---|---|
| R1 | Producción sin TLS (HTTP en puerto 80) | Credenciales y sesión viajan en claro en la red interna; `SESSION_SECURE_COOKIE=false` y no se emite HSTS | Red interna segmentada; cookie `HttpOnly` + `SameSite=Lax`; CSP con `unsafe-*` limitada a Livewire (ver R2). Al habilitar TLS: `SESSION_SECURE_COOKIE=true` y HSTS se activa solo | 2027-02-24 |
| R2 | CSP con `script-src 'unsafe-inline' 'unsafe-eval'` | Reduce la defensa en profundidad frente a XSS | Salidas Blade escapadas; `{!! !!}` auditados y endurecidos (`e()`, `JSON_HEX_*`); SRI en los CDN. Plan: CSP con nonce cuando Livewire 3 lo permita | 2027-02-24 |
| R3 | MFA ausente | Robo de credenciales → acceso administrativo | Ver W2 | 2027-02-13 |
| R4 | SAST/DAST parciales | Hallazgos no detectados automáticamente | `php -l` (166 ficheros), grep dirigido (SQL crudo, eval/exec, deserialización, `verify=false`), ZAP baseline local (0 FAIL / 6 WARN revisados). Larastan excluido del CI por decisión del usuario; DAST contra staging no ejecutado (no hay staging y producción no se toca) | 2027-02-24 |
| R5 | Permisos de `dependencias` reutilizan `ver-departamentos` | Granularidad menor de la deseada | Aceptado en F1 §4.7; sin ampliación de alcance en este ciclo | 2027-02-24 |
| R6 | `activity_log` guarda IP y user-agent; sin retención automatizada | Dato personal en logs de auditoría | Acceso restringido a `admin-auditoria` (ruta, controlador, componente y UI); exportaciones con el mismo permiso; purga manual pendiente de política formal | 2027-02-24 |
| R7 | Runtime local 8.3.36 < 8.3.37 | Correcciones de seguridad de la rama 8.3 sin aplicar en el entorno local | CI fija 8.3.37; **actualizar Laragon antes del despliegue** | **2026-12-13** |
| R8 | Límites de recursos dependen de PHP/nginx | Agotamiento de recursos por payloads grandes; headers en estáticos fuera de la app | `ValidatePostSize` + validación estricta del reporte masivo + paginación; revisar `post_max_size`/`client_max_body_size`/`expose_php=Off`/headers en despliegue | 2027-02-24 |
| R9 | Composer local 2.9.4 afectado por CVE-2026-59944 (media, 6.1) | Riesgo de permisos/rutas en `vendor/bin` al instalar paquetes con binarios enlazados fuera del paquete | Es **herramienta de build local**, no runtime; el CI usa el Composer actual de `setup-php` (≥2.10.3) y el `vendor/` se instala desde lock. **Actualizar Composer a ≥2.10.3 antes de la próxima actualización de dependencias** | **2026-12-13** |

## 5. CVEs considerados (verificación en fuentes primarias, 2026-11-24)

| CVE | GHSA | Severidad | Afecta | Estado en el proyecto |
|---|---|---|---|---|
| **CVE-2026-114509** | [GHSA-f9x3-7vq9-cph6](https://github.com/advisories/GHSA-f9x3-7vq9-cph6) | Crítica (CVSS 4.0: 9.2) | `laravel/framework` `<10.50.4`, `11.x <11.56.1`, `12.x <12.68.0`, `13.x <13.27.0` (GHSA) | **No afectado**: 13.44.1. ⚠️ NVD describe el parche de 13.x como 13.29.0; GHSA y las releases oficiales indican **13.27.0**. La discrepancia no cambia el estado (13.44.1 > ambas) |
| **CVE-2026-59944** | [GHSA-96h3-5x6v-m776](https://github.com/advisories/GHSA-96h3-5x6v-m776) | Media (CVSS 3.1: 6.1) | `composer/composer` `2.3.0 – <2.10.3` (y `1.0 – <2.2.30`) | ⚠️ **Herramienta local 2.9.4 afectada** (ver R9); el runtime de la app no lo usa. CI con Composer actual |
| CVE-2026-17544 | [GHSA-x692-q9x7-8c3f](https://github.com/php/php-src/security/advisories/GHSA-x692-q9x7-8c3f) | NVD CVSS 3.1 **9.8 Crítica** · GHSA **High** (CVSS 4.0 8.1) | PHP `8.4.x <8.4.24`, `8.5.x <8.5.9` | **No aplica a 8.3.x** (verificado en NVD: rangos 8.4/8.5) |
| CVE-2026-7263 | [GHSA-4jhr-8w89-j733](https://github.com/php/php-src/security/advisories/GHSA-4jhr-8w89-j733) | Alta (NVD CVSS 3.1: 7.5) | PHP `8.4.x <8.4.21`, `8.5.x <8.5.6` | **No aplica a 8.3.x** |
| CVE-2026-6104 | [GHSA-74r9-qxhc-fx53](https://github.com/php/php-src/security/advisories/GHSA-74r9-qxhc-fx53) | ⚠️ NVD **Crítica** (CVSS 3.1 9.1) vs GHSA **Moderate** (6.3) | PHP `8.4.x <8.4.21`, `8.5.x <8.5.6` | **No aplica a 8.3.x**. Discrepancia NVD↔GHSA documentada |

> Los GHSA de PHP citados son **advisories del repositorio `php/php-src`** (visibles en GitHub) y **no** están en la GitHub Advisory Database global; por eso no aparecen en `composer audit` ni en Dependabot.
> No se citan CVEs de los 65 avisos del baseline F2 porque el `composer.lock` actual ya no los contiene (`composer audit` = 0). El baseline histórico queda en [`PLAN.md`](PLAN.md) §6.b.

## 6. Reporte de vulnerabilidades

- **Canal:** GitHub Security Advisories del repositorio (`KingWolfRaudi/sigeinvGDC` → *Security* → *Report a vulnerability*).
- **No** abrir issues públicos con detalles de explotación ni datos reales.
- **Plazo objetivo:** acuse en 5 días hábiles; evaluación y plan en 30 días.

## 7. Artefactos de verificación

- SBOM: `docs/sbom/composer-sbom.json` (CycloneDX 1.5, 148 componentes, generado 2026-11-24, sincronizado 1:1 con `composer.lock`).
- DAST local: `docs/seguridad/zap-baseline.html`, `.json`, `.md` (ZAP 2.17.0, 2026-11-24, **0 FAIL / 6 WARN**).
- Tests: `docs/seguridad/phpunit.xml` (29 tests, 100 assertions).
- Runbook: `docs/runbook-seguridad-rollback.md` (despliegue, rotación de `APP_KEY`, TLS, rollback, incidentes).
- Verificación SBOM en CI: `scripts/verify-sbom.php`.
- Informe del gate F6: `docs/seguridad/informe-gate-F6.md`.
- CI: `.github/workflows/ci.yml` (pint + phpunit + composer audit + gitleaks; Actions por commit SHA).
