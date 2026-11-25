# Informe — Gate de seguridad F6

- **Proyecto:** sigeinvGDC · **Rama:** `Dev-branch`
- **Fecha:** 2026-11-24 · **Alcance:** local (producción 172.17.11.31 intacta)
- **Base:** Laravel **13.44.1** · PHP 8.3.36 local (CI fija 8.3.37) · Composer 2.9.4 local (ver R9)
- **Artefactos:** `SECURITY.md` · `docs/sbom/composer-sbom.json` · `docs/seguridad/zap-baseline.*` · `docs/seguridad/phpunit.xml`

## 1. Resultado del gate

**GATE SEGURIDAD: 13/20 ✅ · 7 ⚠️ · 0 ❌.**

Los 7 ítems ⚠️ (3, 8, 14, 15, 18, 19 y 20) **no** se marcan ✅ porque dependen de
decisiones o entornos fuera de este ciclo, o de waivers aún no aceptados. Causas:
**R1** (producción sin TLS), **R2** (CSP `unsafe-*` por Livewire 3), **R4**
(SAST/DAST sin staging), **R6** (retención de logs/request-id) y **R8**
(límites de infraestructura). Detalle en §2 y `SECURITY.md` §4.

| # | Ítem | Estado | Evidencia |
|---|---|---|---|
| 1 | Sin secretos en repo ni historial | ✅ | gitleaks 8.30.1: 45 commits escaneados, *no leaks found* (modo git, igual que CI); `.env` en `.gitignore`; W1: fichero local nunca versionado |
| 2 | Hashing de contraseña correcto | ✅ | `config/hashing.php` bcrypt rounds 12; `Hash::make/check`; salt único; factory coherente |
| 3 | Sesiones/JWT seguras | ⚠️ R1 | `HttpOnly=true`, `SameSite=Lax`, `expire_on_close`, `encrypt=false`; `Secure=false` porque producción es HTTP; sin JWT (Sanctum solo sesión). Plan en `SECURITY.md` R1 |
| 4 | Authz en todos los endpoints | ✅ | Matriz `docs/matriz-permisos.md` §2/§6; `->can()` + `authorize()` + `abort_if(Gate::denies())` + `@can` en UI; T-AUTH-1..7 + tests UI. Criterio evaluado: **presencia** de authz en cada endpoint; la granularidad de `dependencias` es la deuda R5 |
| 5 | Anti-mass-assignment | ✅ | Sin `$guarded = []`; modelos con `$fillable`; Livewire valida antes de persistir |
| 6 | SQL parametrizado | ✅ | Sin concatenación de entrada; `whereRaw('1 = 0')` y `orderByRaw` con literales constantes; Eloquent usa bindings en todos los `where` |
| 7 | Sin `eval`/`exec`/`unserialize` | ✅ | Grep en `app/`, `routes/`, `config/`, `database/` → 0 coincidencias; `php -l` 167/167 |
| 8 | Salida escapada / CSP sin unsafe-* | ⚠️ R2 | `{{ }}` por defecto; `{!! !!}` auditados: constantes o `e()`/`JSON_HEX_*`; SRI en CDN. CSP conserva `'unsafe-inline' 'unsafe-eval'` por Livewire 3 + Alpine (waiver R2) |
| 9 | Cripto: AEAD/nonce/CSPRNG/clave fuera del repo | ✅ | Laravel Encrypter autenticado (AES-256-CBC + HMAC); `APP_KEY` en `.env`; `Str::random`/`random_bytes`; rotación de `APP_KEY` documentada en [`docs/runbook-seguridad-rollback.md`](../runbook-seguridad-rollback.md) §5 |
| 10 | TLS verificado en cliente | ✅ N/A | Sin salidas HTTP (`Http::`, Guzzle, cURL: 0 usos en `app/`); sin `verify=false` |
| 11 | Validación allowlist en el borde | ✅ | Login `required\|string\|max:255`; reporte masivo re-validado en servidor (módulo allowlist, forma del payload) con tests de rechazo (redirect + `error`); reglas en componentes Livewire |
| 12 | CORS allowlist + CSRF + rate limit | ✅ | `config/cors.php` origen explícito; `PreventRequestForgery`; `RateLimiter::for('login')` + test 429 |
| 13 | SSRF mitigado | ✅ N/A | Sin salidas HTTP ni URLs controladas por usuario |
| 14 | Límites de recursos | ⚠️ R8 | Paginación; `ValidatePostSize`; validación estricta del payload masivo; `post_max_size`/`client_max_body_size` dependen de PHP/nginx de producción (fuera de alcance) |
| 15 | Logs sin secretos/PII; eventos y request-id | ⚠️ R6 | Sin contraseñas/tokens en logs; `activity_log` guarda IP/UA **a propósito** (auditoría); `APP_DEBUG=false` por defecto; sin request-id ni alertas automáticas |
| 16 | Errores fail-closed | ✅ | `abort_if`/`abort(403/429)`; `Gate::denies`; `MassiveReportExport` lanza excepción ante módulo desconocido; sin `catch` vacío en rutas sensibles |
| 17 | Supply chain | ✅ | `composer.lock` versionado; SBOM CycloneDX 1.5 reproducible (148 componentes, 1:1 con lock); `composer audit` 0; Actions por commit SHA; gitleaks verificado por SHA-256; CI ejecuta `scripts/verify-sbom.php` y falla si el SBOM se desincroniza |
| 18 | Headers de seguridad | ⚠️ R1/R8 | `SecurityHeadersTest` verifica CSP, nosniff, DENY, Referrer-Policy, Permissions-Policy, COOP y ausencia de HSTS/`X-Powered-By` **en respuestas dinámicas**; los estáticos y HSTS dependen del despliegue (nginx, TLS) |
| 19 | Privacidad | ⚠️ R6 | Exportaciones con PII gated (`reportes-excel`/`reportes-pdf`/`admin-auditoria`, ruta+controlador+UI); sin PII en `.env`; retención de `activity_log` pendiente de política formal |
| 20 | Checks en verde | ⚠️ R4 | Pint ✅ · PHPUnit 31/31 ✅ · composer audit 0 ✅ · gitleaks ✅ · ZAP local 0 FAIL/6 WARN (revisados) · SBOM verificado en CI · SAST one-off PHPStan 2.3.7 (nivel 1) → 0 hallazgos (Larastan sigue excluido del CI por decisión del usuario) · DAST autenticado sin staging |

## 2. Evidencia de comandos (2026-11-24)

```
pint --test                          → passed
phpunit --log-junit                  → OK (31 tests, 108 assertions) · docs/seguridad/phpunit.xml
composer audit --locked              → No security vulnerability advisories found (exit 0)
gitleaks 8.30.1 (git, modo CI)       → 45 commits scanned · no leaks found (exit 0) · gate ejecutado antes del commit de F6; el CI re-escanea el historial completo en cada push
php -l (167 ficheros, app/routes/config/database/tests/public/scripts) → 0 errores
ZAP baseline (local :8000)           → FAIL-NEW: 0 · WARN-NEW: 6 (artefactos archivados)
SBOM CycloneDX 1.5 (reproducible)    → 148 componentes · 1:1 con composer.lock · scripts/verify-sbom.php exit 0
```

## 3. Hallazgos corregidos en F6

1. **Actions sin pin por SHA** → `actions/checkout`, `actions/cache` (commits) y `setup-php` (commit *peeled* `eb7c497e`) fijados; gitleaks actualizado a **8.30.1** con verificación SHA-256.
2. **CORS `allowed_origins: ['*']`** → allowlist explícita (`APP_URL`/`CORS_ALLOWED_ORIGINS`), métodos y cabeceras acotados.
3. **`.env.example`** → `APP_DEBUG=false`, `LOG_LEVEL=warning`, `APP_ENV=production` (con nota para local) y `SESSION_SECURE_COOKIE` explícito; `SESSION_LIFETIME` unificado a 60 (config y ejemplo).
4. **`X-Powered-By`** → eliminado a nivel SAPI (`header_remove` en `public/index.php`) y en el middleware; test añadido.
5. **CDN sin SRI y chart.js flotante** → chart.js fijado a 4.6.2 con `integrity`; bootstrap-icons con SRI; **páginas de error 401/403/404/419/429/500/503** también con SRI (Bootstrap 5.3.2 + bootstrap-icons 1.11.1).
6. **Salidas `{!! !!}`** → `e()` en arrays de RAM/discos; `JSON_HEX_*` en datos de gráficos.
7. **Validación de login** → tipo y longitud máxima 255; tests de vacío, longitud y metacaracteres SQL.
8. **PHP del CI** → 8.3.37 (release de seguridad del 2026-11-19; local 8.3.36, R7).
9. **Reporte masivo sin re-validación** → allowlist de módulos + forma del payload en el servidor (`back()->with('error')`) y `default` explícito en el export; tests de rechazo añadidos.
10. **UI ≠ servidor en exportaciones** → `@can('reportes-excel')` en gestión de incidencias y usuarios; logs de auditoría con `@can('admin-auditoria')`; 2 tests de UI (oculta/visible).
11. **`MainDashboard::$esResolutor` nunca se activaba** → flag recalculado en `mount()` (un resolutor ahora solo ve sus casos).
12. **Código muerto** → componente `Dashboard` (sin ruta ni referencias) y su vista eliminados.
13. **`env()` en la vista** → `config('app.org_nombre')` (evita perder el valor con `config:cache`).
14. **Docs incoherentes** → `manual_tecnico_parte2.md` (slug `tipos-dispositivo`, permisos de `software`/`roles`, ruta `seed-permissions` eliminada, redirección sin rol → 403, cabeceras reales), `PLAN.md` CA-9 (302 en vez de 401), matriz con nota de estado F1 y resolución §6.
15. **SBOM desfasado tras el update a 13.44.1** → regenerado reproducible y **comprobación en CI**.

**Correcciones tras la verificación T4/T5 (passes 2–4; los residuos de los passes 3 y 4 están enumerados en §6):**

16. Filtro **"Cerrados"** de `/incidencias/gestion` enviaba `cerrados` y el servidor espera `cerrado` (filtro inoperante) → valor corregido (O-1).
17. Acciones Livewire `Gestion::crear()`/`guardar()` **sin revalidar `crear-ticket`** → `abort_if(Gate::denies(...))` + tests 403/200 (O-2).
18. `Admin\Usuarios` creaba contraseñas sin `max:255` mientras el login exige ≤255 → regla unificada `string|min:8|max:255` (O-3).
19. Manual: fecha de versión, permisos de roles/catálogos (`resolver-incidencias`, `admin-configuracion-global`, `categorias-insumos`, `problemas`, `ver-panel-soporte`), excepciones de exportaciones/PDF y "sin rol → 403" (O-4, O-6, O-7, O-8).
20. **Semántica de error unificada** en el reporte masivo: toda entrada inválida → `back()->with('error')` (antes mezclaba redirect y `abort(400)`); tests actualizados (O-5).
21. Matriz §2 fila `/` y `PLAN.md` CA-8 corregidas (`redirect` en componente, `authorize()` en controlador) (O-9, O-11).
22. Evidencia del ítem 6 sin `DB::raw` (el único uso estaba en el componente legado ya eliminado) (O-10).
23. **IDOR (CWE-639) en `MainDashboard::ver($id)`**: la ruta solo exige `ver-dashboard` y la acción abría cualquier incidencia → acotada a `ver-incidencias` ∨ propietario (`created_by`) ∨ técnico asignado (`user_id`); 2 tests nuevos (atacante 403 / propietario 200) (O-12).
24. **Sincronía SBOM en CI** → `scripts/verify-sbom.php` compara lock↔SBOM por nombre+versión (el `git diff` anterior era vacío sin commit y fallaría por metadatos); referencia a `coherence-audit.md` corregida (T3-D3, T3-D4).
25. **Runbook F7 creado** (`docs/runbook-seguridad-rollback.md`): despliegue, smoke test, rotación de `APP_KEY`, TLS, backup/rollback y respuesta a incidentes (cierra la evidencia del ítem 9 y las acciones R7/R9).

## 4. Revisión de los 6 WARN + 2 informativos de ZAP (2.17.0)

| WARN | Evaluación |
|---|---|
| `Cookie No HttpOnly` | **Falso positivo**: falta en `XSRF-TOKEN` (legible por JS por diseño de Laravel); `laravel_session` sí es `HttpOnly` (verificado con `curl -I`) |
| `X-Content-Type-Options` en estáticos | Assets servidos por el servidor embebido de `artisan serve` (sin middleware); en producción nginx debe añadirlo (R8) |
| `Big Redirect` | 302 a `/login` con cuerpo de error; sin datos sensibles |
| `CSP unsafe-eval` / `unsafe-inline` | **Aceptado** (R2, Livewire 3 + Alpine) |
| `Modern Web Application` / `Session Management` | Informativos |

Desaparecieron respecto a la primera pasada: `X-Powered-By`, `PHP Version with
Known Vulnerabilities` (consecuencia del anterior) y `Sub Resource Integrity`.

## 5. Tests transversales T1–T5

**Pass 1 (editor, 2026-11-24):** T1 ⚠️ (2 correcciones editoriales), T2 ✗ (versiones y documentación), T3 ✗ (9 vs 6 WARN), T4 ✗ (29 desviaciones), T5 ✗ (formato/cifras). Todas las correcciones aplicadas en §3 de este informe.

**Pass 2 (subagentes con contexto fresco, en paralelo):**

| Test | Veredicto pass 2 | Detalle |
|---|---|---|
| **T1** CVEs | ✅ **PASA** | 5/5 CVE y 5/5 GHSA verificados en NVD/GHSA; las divergencias fuente↔fuente (Laravel 13.27.0↔13.29.0, CVE-2026-6104 9.1↔6.3) ya están documentadas; versiones de stack exactas |
| **T2** versiones | ✅ **PASA** | PHP 8.3.36 local/8.3.37 CI, Laravel 13.44.1, Composer 2.9.4 (≥2.10.3), gitleaks 8.30.1 coinciden con php.net, Packagist y GitHub; R7/R9 declaradas con acción y fecha |
| **T3** artefactos | ✅ **PASA** (con salvedades) | SBOM 148/148 sin huérfanos ni versiones distintas, regeneración byte-idéntica; todas las cifras verificadas. Salvedades resueltas: check de SBOM con `scripts/verify-sbom.php` (el `git diff` anterior era vacío sin commit y dependía de metadatos) y referencia a `coherence-audit.md` corregida. La restante (commitear el set F6) se cierra con el commit de esta fase |
| **T4** coherencia | pass 2 ❌ → 12 desviaciones (O-1…O-12) corregidas · pass 3 ❌ → 9 residuos (N-1…N-9) corregidos · pass 4 ❌ → 2 residuos (N-10, N-11) corregidos · **pass 5 ✅ PASA** | 52 hallazgos → 45 corregidos · 7 aceptados con responsable/fecha · 0 abiertas |
| **T5** formato | pass 2 ❌ → 2 correcciones (evidencia del ítem 9, R9 en el cierre) · pass 3 ❌ → 4 (cifras 25→29 y 165→166, enlace del runbook, pipes de tabla) · **pass 4 ✅ PASA** | Formato §6, cifras, enlaces y runbook verificados |

*Nota:* el único cambio posterior a estos veredictos es la actualización de esta propia
tabla y del marcador de estado (N-12); no altera estados de ítems, cifras ni conclusiones.

**Resultado: T1–T5 en verde (5/5) tras los passes de re-ejecución.**

## 6. Coherencia (12 clases — `references/coherence-audit.md` del skill secure-by-default)

- **Pass 1:** 29 desviaciones (D01–D29): **22 corregidas**, 7 aceptadas.
- **Pass 2:** 12 desviaciones adicionales (O-1–O-12): **12 corregidas** (§3.16–24).
- **Pass 3:** 9 residuos documentales (N-1–N-9), todos corregidos: docblock "400" del test (§3.20), cifras 25→29 y 165→166 (ítems 7 y 20 + `SECURITY.md` R4), enlace del runbook (ítem 9), pipes de la tabla del ítem 11, `PLAN.md` CA-8, cabeceras del manual §15.7, §12 del manual, `resolver-incidencias`/`reportes-excel` en §11.2 del manual y test de `guardar()` (§3.17).
- **Pass 4:** 2 residuos (N-10: puntero de recuento en este §6; N-11: permiso de exportación de Auditoría en `manual_usuario.md` §15.1): **2 corregidos**.
- **Total: 52 hallazgos → 45 corregidos · 7 aceptados · 0 abiertas.**

Desviaciones aceptadas (con responsable y fecha, según el gate de coherencia):

| ID | Función / efecto | Responsable | Revisión |
|---|---|---|---|
| D01 | `RateLimiter::for('login')` registrado sin ruta `throttle:` que lo aplique (el login es acción Livewire); usa la misma `LoginThrottle` | Equipo backend | 2027-02-24 |
| D05 | `abort(429)` en login (página de error) frente a `addError()` para credenciales; 429 es deliberado (rate limit) | Equipo backend | 2027-02-24 |
| D06 | Clase `PreventBackHistory` con nombre legacy (el alias `nocache` sí describe el efecto) | Equipo backend | 2027-02-24 |
| D07 | `Auditoria::generarReporteMasivo()` solo abre el modal (la generación está en `ReporteController`) | Equipo backend | 2027-02-24 |
| D23 | `redirect` redundante en `MainDashboard::mount()` (la ruta ya da 403) — se conserva como defensa | Equipo backend | 2027-02-24 |
| D24 | Restos cosméticos: `welcome.blade.php`, slug `dependencias` sin permisos en el seeder, `TELESCOPE_ENABLED` sin paquete, bloque `argon` inactivo | Equipo backend | 2027-02-24 |
| D26 | Parámetros de ruta sin tipar en `ReporteController` (`findOrFail` + allowlist ya validan; tiparlos añadiría 500s) | Equipo backend | 2027-02-24 |

## 7. Cobertura OWASP Top 10:2025

A01 Broken Access Control ✅ · A02 Security Misconfiguration ✅ · A03 Software Supply Chain Failures ✅ ·
A04 Cryptographic Failures ⚠️ (bcrypt ok; transporte sin TLS → R1) · A05 Injection ✅ ·
A06 Insecure Design ✅ · A07 Authentication Failures ✅ · A08 Software or Data Integrity Failures ✅ ·
A09 Security Logging and Alerting Failures ⚠️ (activity_log ok; sin request-id/alertas → R6) ·
A10 Mishandling of Exceptional Conditions ✅.

## 8. Limitaciones declaradas

- No hay entorno de staging: DAST se ejecutó contra `artisan serve` local; el despliegue real y su configuración nginx/PHP no se tocan.
- Larastan fuera del CI por decisión del usuario; SAST se limita a `php -l` y grep dirigido (R4).
- F5 (E2E de navegador/accesibilidad) no se ejecutó: sin staging ni suite Playwright/Dusk; los flujos de error se cubren con tests Livewire.

## 9. Veredicto

**F6 no se declara cerrado al 100%** hasta que el usuario confirme los
riesgos residuales **R1, R2, R4, R6 y R8** (los que causan los 7 ítems ⚠️) y
asuma las acciones **R7** (actualizar PHP local a 8.3.37) y **R9** (Composer
≥2.10.3) con sus fechas de revisión, o hasta que se habiliten TLS/staging/SAST
completo. No hay hallazgos críticos abiertos: los 52 hallazgos de coherencia
(29 del pass 1 + 12 del pass 2 + 9 del pass 3 + 2 del pass 4) quedaron
corregidos o aceptados con motivo, responsable y fecha.

---

```
FASE F6 · gate de seguridad + tests transversales
GATE SEGURIDAD 13/20 ✅ · 7 ⚠️ (R1, R2, R4, R6, R8) · 0 ❌
COHERENCIA 12 clases: 52 hallazgos → 45 corregidos · 7 aceptados con responsable y fecha · 0 abiertas
TESTS 31/31, 108 assertions (docs/seguridad/phpunit.xml) · SCA 0 advisories · gitleaks 0 hallazgos · DAST local 0 FAIL/6 WARN
Artefactos: SECURITY.md · docs/sbom/composer-sbom.json · docs/seguridad/* · docs/runbook-seguridad-rollback.md · scripts/verify-sbom.php
Desviaciones de coherencia corregidas: 45 (pass 1: D02–D04, D08–D22, D25, D27–D29 · pass 2: O-1–O-12 · pass 3: N-1–N-9 · pass 4: N-10–N-11) — detalle en §3 y §6.
Desviaciones aceptadas: D01, D05, D06, D07, D23, D24, D26 (§6) + riesgos residuales R1–R9 propuestos (SECURITY.md §4, pendientes de confirmación) + waivers W1/W2 (SECURITY.md §3).
OWASP Top 10:2025: A01–A10 cubiertos; A04 y A09 parciales con R1/R6.
```

---

## 10. Ampliaciones posteriores al gate (2026-11-25 — F6 sigue abierto)

Decisión del usuario (2026-11-25): **no cerrar F6** hasta habilitar las
condiciones que hoy solo están parcialmente cubiertas. Avances aplicados en
esta tanda (el recuento del gate §1 corresponde a la evaluación del
2026-11-24 y no se reabre hasta la re-verificación final):

| Riesgo | Avance aplicado | Estado |
|---|---|---|
| **R1** | Test `test_secure_cookie_and_hsts_when_tls_is_configured`: con `SESSION_SECURE_COOKIE=true` la cookie de sesión sale con `Secure` + `HttpOnly` y se emite HSTS sobre https (fija el comportamiento que debe existir al habilitar TLS) | Sigue ⚠️: falta TLS real en producción |
| **R4** | SAST one-off: **PHPStan 2.3.7 nivel 1** sobre `app/`, `routes/` y `scripts/` (excluidas las mágicas Eloquent/Livewire) → **0 hallazgos**; se retiró el comando `inspire` del skeleton (único falso positivo, código muerto). Ejecutado fuera del CI (Larastan sigue excluido por decisión del usuario) | Sigue ⚠️: falta DAST autenticado en staging |
| **R6** | Retención automatizada: `activitylog:clean --days=365 --force` programado a diario en `app/Console/Kernel.php` + test `ActivityRetentionTest` (purga registros >12 meses y conserva los recientes) | Avance; quedan alertas/request-id por definir |
| **R8** | Valores recomendados de nginx/php.ini (body size, timeouts, `expose_php`, headers en estáticos, `limit_req` en `/login`) documentados en el runbook §9 | Sigue ⚠️: fuera de alcance (producción) |

Estado de la suite tras las ampliaciones: **31 tests / 108 assertions**,
`pint --test` passed, `composer audit` 0, gitleaks 0 hallazgos,
`scripts/verify-sbom.php` 0, PHPStan 0 hallazgos.

**Pendiente para el cierre de F6:** (1) TLS real en producción (R1),
(2) DAST autenticado en staging con navegador (R4), (3) confirmación
explícita del usuario sobre R1–R9 (§9 y `SECURITY.md` §4). Después de esos
cambios, re-ejecutar T3/T4/T5 porque cambian artefactos y cifras.
