# Runbook de seguridad, despliegue y rollback — sigeinvGDC

- **Versión:** 1.0 (2026-11-24) · **Rama:** `Dev-branch`
- **Alcance:** entorno local/staging. **Producción (172.17.11.31) solo se toca con aprobación explícita del responsable.**
- **Companion:** [`SECURITY.md`](../SECURITY.md) (waivers W1/W2 y riesgos R1–R9).

## 1. Pre-requisitos de despliegue

| Elemento | Valor mínimo | Verificación |
|---|---|---|
| PHP | **8.3.37** (release de seguridad 2026-11-19) | `php -v` |
| Composer | **≥ 2.10.3** (CVE-2026-59944) | `composer --version` |
| Laravel | ≥ 13.27.0 (instalado: 13.44.1) | `composer show laravel/framework` |
| `.env` | `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning` | revisión manual |
| TLS | Activo (ver §4) | `curl -I https://…` |

## 2. Despliegue

```bash
git fetch --all --tags
git checkout <tag-o-commit>            # commit verificado (gpg/ssh signature si aplica)
composer install --no-dev --prefer-dist --no-progress
php scripts/verify-sbom.php            # SBOM ↔ lock 1:1
composer audit --locked                # 0 advisories
cp .env.example .env                   # solo si no existe; luego ajustar valores reales
php artisan key:generate --force        # solo si APP_KEY está vacía (ver §5)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force             # tras backup de BD (§6)
vendor/bin/pint --test
vendor/bin/phpunit                     # 29 tests en verde
```

### Smoke test post-despliegue

```bash
curl -sI https://<host>/login            # 200 + CSP + nosniff + sin X-Powered-By
curl -sI http://<host>/                  # 302 a /login sin sesión
curl -s -o /dev/null -w '%{http_code}\n' https://<host>/admin/auditoria   # 302 sin sesión
```
Y un escritura real: crear una incidencia de prueba desde un usuario con `crear-ticket` y verificarla en `/incidencias/gestion`, luego eliminarla.

> **Ejecutado en local (2026-11-24):** `php artisan serve` + `curl -I http://127.0.0.1:8000/login` → **200** con CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `COOP` y **sin** `X-Powered-By`; `GET /` sin sesión → **302** a `/login`.

## 3. Actualización de dependencias (SCA)

```bash
composer update                         # o paquete concreto: composer update laravel/framework --with-dependencies
composer CycloneDX:make-sbom --output-file=docs/sbom/composer-sbom.json --output-format=JSON --output-reproducible
php scripts/verify-sbom.php
composer audit --locked
vendor/bin/phpunit
git add composer.lock docs/sbom/composer-sbom.json && git commit -m "chore: actualización de dependencias + SBOM"
```
Cualquier advisory nuevo se evalúa: bump → waiver §7 de la skill con compensación y fecha de revisión.

## 4. Habilitación de TLS (cierra R1)

1. Emitir certificado (CA institucional) y montar en nginx con redirect 80 → 443.
2. En `.env`: `SESSION_SECURE_COOKIE=true`, `APP_URL=https://<host>`.
3. Verificar: HSTS (`max-age=31536000; includeSubDomains`) se emite solo sobre TLS (test `SecurityHeadersTest`), cookie `laravel_session` con `Secure`.
4. `php artisan config:cache` y smoke test con `curl -I --insecure https://…`.

## 5. Rotación de `APP_KEY`

1. **Impacto:** invalida todas las cookies/sesiones encriptadas (todos los usuarios se desconectan) y cualquier dato cifrado con la clave anterior (avatares cifrados, etc.). Programar en ventana de mantenimiento.
2. `php artisan key:generate --show > /tmp/nueva-clave` (o generar con `openssl rand -base64 32`).
3. Back `.env` → sustituir `APP_KEY` → `php artisan config:cache`.
4. Verificar login y `Encrypter` sin excepciones; si había cifrado previo, migrar datos antes de rotar.
5. Registrar el evento (fecha, responsable) en el log de cambios.

## 6. Backup y rollback

**Backup antes de migrar:**
```bash
mysqldump --single-transaction -u<user> -p <db> > backup_$(date +%F_%H%M).sql
tar czf storage_backup_$(date +%F_%H%M).tar.gz storage/app/public
```

**Rollback de código:**
```bash
git checkout <commit-anterior>          # p. ej. 82a7086 (estado previo a la migración)
composer install --no-dev
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan migrate:rollback            # solo si hubo migraciones en el release
```
**Rollback de BD:** `mysql < backup_*.sql` (en ventana, con la app parada).

**Rollback verificado en local el 2026-11-24:** worktree en `cfb3542` (estado previo al F6) + `composer install --no-dev` + `config:cache` → *Laravel Framework 13.42.0* y configuración cacheada sin errores; worktree eliminado. `migrate:rollback` **no** se ejecutó (sin ventana de BD de prueba; pendiente con entorno de staging).

## 7. Respuesta a incidentes

1. **Aislar:** bloquear IP/ruta en nginx (`deny`), o `php artisan down --retry=…` si es sistemático.
2. **Preservar:** `storage/logs/laravel.log` + `activity_log` (no purgar; contiene IP/UA).
3. **Notificar:** responsable del proyecto + equipo de seguridad (canal en §6 de `SECURITY.md`); plazo de acuse 5 días hábiles.
4. **Evaluar:** ¿credenciales? → rotar `APP_KEY` (§5) y contraseñas afectadas; ¿permisos? → revisar roles con `php artisan permission:cache-reset` y auditoría de `model_has_roles`.
5. **Corregir:** rama `Dev-branch`, tests primero (TDD), CI en verde antes de merge.
6. **Post-mortem:** sin culpables, con línea de tiempo, causa raíz y acción preventiva con fecha.

## 8. Mantenimiento programado

| Tarea | Frecuencia | Comando/acceso |
|---|---|---|
| `composer audit --locked` + gitleaks + pint + phpunit | cada PR (CI) | `.github/workflows/ci.yml` |
| Actualizar PHP a la última 8.3.x de seguridad | mensual | `php -v` vs php.net |
| Actualizar Composer (≥2.10.3) | mensual | `composer --version` |
| Rotación `APP_KEY` | anual o ante incidente | §5 |
| Revisar waivers W1 (2026-12-13), W2 (2027-02-13), R1–R9 | fechas en `SECURITY.md` | — |
| Backup BD | diario | `mysqldump` (§6) |
