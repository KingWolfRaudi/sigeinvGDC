# Matriz de permisos — recurso × rol (F1)

- **Fecha:** 2026-11-13 · **Base:** `RolesAndPermissionsSeeder.php` + `routes/web.php` + checks internos de componentes + `@can` de la UI.
- **Leyenda:** ✅ correcto · ⚠️ hueco (sin authz en servidor) · 🔶 incoherencia (UI ≠ servidor).

## 1. Roles

| Rol | Alcance |
|---|---|
| `super-admin` | Todos los permisos |
| `administrador` | Todo salvo `eliminar-*` |
| `coordinador` | Catálogos+asignaciones+inventario (sin eliminar), movimientos, reportes, ticket/gestión/admin incidencias, `admin-solicitudes-perfil`, `ver-auditoria-tecnicos` |
| `personal-ti` | Catálogos+asignaciones+inventario (sin eliminar), movimientos, reportes, ticket/gestión/ver incidencias (sin `admin-incidencias` ni `resolver-incidencias`) |
| `resolutor-incidencia` | `ver-incidencias`, `gestionar-incidencias`, `resolver-incidencias`, `ver-dashboard` |
| `trabajador` | `crear-ticket`, `ver-dashboard` |

## 2. Rutas web × authz en servidor

> **Nota:** las marcas ⚠️/🔶 de esta tabla describen el estado en **F1**
> (2026-11-13). La resolución aplicada en F4 está en **§6** al final del documento.

| Ruta | Permiso esperado (UI) | Servidor | Estado |
|---|---|---|---|
| `GET /` (MainDashboard) | `ver-dashboard` | componente: `redirect()->route('perfil')` si `Gate::denies` (defensa); hoy la ruta exige `->can('ver-dashboard')` → 403 | ✅ (cerrado en F4) |
| `GET /login` | invitado | middleware `guest` | ✅ |
| `POST /logout`, `GET /force-logout-tab` | autenticado | middleware `auth` | ✅ |
| `GET /admin/roles` | `ver-roles` | `->can('ver-roles')` + componente | ✅ |
| `GET /admin/usuarios` | `ver-usuarios` | `->can('ver-usuarios')` + componente | ✅ |
| `GET /admin/configuracion` | `admin-incidencias` ∨ `admin-solicitudes-perfil` ∨ `admin-configuracion-global` | componente: 3× `Gate::denies→403`; **ruta sin `->can()`** | ⚠️ ruta sin defensa en profundidad · 🔶 menú no muestra el enlace a quien solo tiene `admin-configuracion-global` |
| `GET /admin/auditoria` | `admin-auditoria` (menú) | componente: `authorize('reportes-masivos-filtros')` | 🔶 **UI dice `admin-auditoria`, servidor exige `reportes-masivos-filtros`** (coordinador/personal-ti ven la ruta accesible aunque el menú se los oculta) |
| `GET /admin/auditoria-tecnicos` | `ver-auditoria-tecnicos` (menú) | **sin check en ruta ni componente** | ⚠️ **cualquier autenticado accede** |
| `GET /admin/auditoria-usuarios` | `ver-auditoria-usuarios` (menú) | **sin check en ruta ni componente** | ⚠️ **cualquier autenticado accede** |
| `GET /reportes/{computador,dispositivo,insumo,gpu,incidencia}/{id}/ficha` (PDF) | `reportes-pdf` (existe en seeder) | **5 métodos del controlador sin `authorize`** | ⚠️ **cualquier autenticado genera cualquier ficha (authz ausente)** |
| `GET /reportes/**/*.excel` (8 rutas) | `reportes-excel` | `$this->authorize('reportes-excel')` en controlador | ✅ |
| `GET /reportes/catalogo/{tipo}/excel` | `reportes-excel` | `authorize` ✅ | ✅ |
| `GET /reportes/logs/{excel,pdf}` | `admin-auditoria` (contiene PII/IP) | `authorize('reportes-excel')` | 🔶 permiso demasiado bajo para datos de auditoría |
| `GET /reportes/auditoria-usuario/{id}/pdf` | `ver-auditoria-usuarios` | `authorize('ver-auditoria-usuarios')` | ✅ |
| `POST /reportes/masivo/excel` | `reportes-masivos-filtros` | `authorize('reportes-masivos-filtros')` | ✅ |
| `GET /catalogos/*` (6) | `ver-*` | `->can('ver-*')` + componente | ✅ |
| `GET /asignaciones/departamentos` | `ver-departamentos` | `->can` + componente | ✅ |
| `GET /asignaciones/dependencias` | `ver-departamentos` | `->can('ver-departamentos')` | ⚠️ sin entidad `dependencias` en seeder (se reusa `departamentos`; ver §4) |
| `GET /asignaciones/trabajadores` | `ver-trabajadores` | `->can` + componente | ✅ |
| `GET /inventario/*` (4) | `ver-*` | `->can('ver-*')` + componente | ✅ |
| `GET /incidencias/reportar` | `crear-ticket` | `->can('crear-ticket')` | ✅ |
| `GET /incidencias/gestion` | `ver-incidencias` | `->can('ver-incidencias')` | ✅ |
| `GET /movimientos/*` (4) | `movimientos-*-ver` / `admin-solicitudes-perfil` | `->can(...)` | ✅ |
| `GET /asociaciones/{tipo}/{id}` | `ver-dashboard` | componente `Gate::denies→403` | ✅ |
| `GET /perfil` | autenticado (perfil propio) | `auth` | ✅ |
| `GET /seed-permissions` | — | **sin ninguna authz; muta roles vía GET** | ⚠️ **debe eliminarse o protegerse (super-admin)** |
| `GET /api/user` | `auth:sanctum` | ✅ | ✅ |

## 3. Acciones CRUD internas (componentes Livewire)

| Componente | Checks internos | Estado |
|---|---|---|
| `Admin\Roles` | `crear/editar/eliminar-roles` | ✅ |
| `Admin\Usuarios` | `ver/crear/editar/cambiar-estatus/eliminar-usuarios` + bloqueo superusuario | ✅ |
| `Admin\ConfiguracionGeneral` | 3 permisos con `Gate::denies→403` | ✅ |
| `Admin\Auditoria` | `reportes-masivos-filtros` | 🔶 vs menú `admin-auditoria` |
| `Admin\AuditoriaTecnicos` | **ninguno** | ⚠️ |
| `Admin\AuditoriaUsuarios` | **ninguno** | ⚠️ |
| `Asignaciones\*`, `Catalogos\*`, `Inventario\*`, `Movimientos\*`, `Incidencias\*` | CRUD por permiso | ✅ (muestreo verificado; ver §4 cierre) |
| `Dashboard` (componente legado sin ruta) | ninguno | **eliminado en F6** (sin rutas ni referencias; su vista arrastraba un chart.js sin SRI) |
| `Perfil\MiPerfil` | ninguno (perfil propio) | ✅ |

## 4. Decisiones de diseño de la matriz (F1)

1. **PDFs de ficha** → permiso `reportes-pdf` (ya existe en el seeder; hoy nadie lo exige). Añadir `$this->authorize('reportes-pdf')` a los 5 métodos + `->can('reportes-pdf')` en las 5 rutas.
2. **Auditorías técnicos/usuarios** → `->can()` en ruta **y** `abort_if(Gate::denies(...))` en `mount()` de ambos componentes (defensa en profundidad).
3. **`/admin/auditoria`** → unificar: el servidor exige `admin-auditoria` (coherente con menú y con el hecho de que muestra logs con IP/PII). *Impacto:* coordinador/personal-ti perdían acceso vía URL directa (hoy accidental), pero su menú nunca lo mostró → sin pérdida funcional real.
4. **`/admin/configuracion`** → ability compuesta `admin.configuracion` definida en `AppServiceProvider` (`admin-incidencias` ∨ `admin-solicitudes-perfil` ∨ `admin-configuracion-global`), usada con `->can()` en ruta y en el menú (corrige la 🔶; `Route::canAny` no existe en Laravel 13).
5. **`/reportes/logs/*`** → subir de `reportes-excel` a `admin-auditoria` (exporta logs con PII/IP; solo super-admin/administrador).
6. **`/seed-permissions`** → **eliminar** (endpoint de mutación accesible; el seeder cubre su función).
7. **Dependencias** → crear entidad `dependencias` en el seeder (`ver-dependencias` etc.) y migrar ruta+UI a `ver-dependencias`; *o* mantener `ver-departamentos` por pragmatismo. **Decisión: mantener `ver-departamentos` en este ciclo** (no ampliar alcance), anotado como deuda.
8. **`GET /`** → añadir `->can('ver-dashboard')` en ruta (hoy solo en componente).

## 5. Tests de authz negativos exigidos (CA-9)

| Test | Escenario | CWE |
|---|---|---|
| T-AUTH-1 | Sin sesión → `GET /` 302 a `/login` | CWE-306 |
| T-AUTH-2 | Rol `trabajador` → `GET /admin/auditoria-tecnicos` 403 | CWE-862 |
| T-AUTH-3 | Rol `trabajador` → `GET /admin/usuarios` 403 | CWE-862 |
| T-AUTH-4 | Rol `trabajador` → `GET /reportes/computador/1/ficha` 403 | CWE-862 |
| T-AUTH-5 | Rol `personal-ti` (sin `admin-auditoria`) → `GET /admin/auditoria` 403 | CWE-862 |
| T-AUTH-6 | Rol `trabajador` → `POST /reportes/masivo/excel` 403 | CWE-862 |
| T-AUTH-7 | Autenticado sin permiso → `GET /seed-permissions` (ruta eliminada → 404) | CWE-862 |
| T-AUTH-8 | Login: N intentos fallidos → 429 (rate limit) | CWE-307 |

## 6. Resolución tras F4 (2026-11-24)

Todas las filas ⚠️/🔶 de §2 y §3 quedaron cerradas en el commit de F4
(excepto la deuda `dependencias` → `ver-departamentos`, §4.7). En F6 se
cerraron además las desviaciones UI↔servidor detectadas en la auditoría de
coherencia:

| Hallazgo F1 | Resolución aplicada | Evidencia |
|---|---|---|
| `/` sin `->can()` | `->can('ver-dashboard')` en ruta + check en componente | `routes/web.php`, `AuthorizationTest` |
| `/admin/configuracion` sin `->can()` y menú incoherente | ability compuesta `admin.configuracion` en ruta y menú | `AppServiceProvider`, `app.blade.php` |
| `/admin/auditoria` UI ≠ servidor | ruta y componente exigen `admin-auditoria` | `routes/web.php`, `Auditoria::mount()` |
| Auditorías técnicos/usuarios sin check | `->can()` + `abort_if(Gate::denies())` en `mount()` | componentes + tests T-AUTH-2 |
| 5 fichas PDF sin `authorize` | `->can('reportes-pdf')` + `$this->authorize('reportes-pdf')` | `routes/web.php`, `ReporteController` |
| Logs Excel/PDF con permiso bajo | subidos a `admin-auditoria` en ruta y controlador | `ReporteController`, `routes/web.php` |
| Reporte masivo | `->can('reportes-masivos-filtros')` + `authorize()` | `routes/web.php`, `ReporteController` |
| `/seed-permissions` | **eliminada** (404 verificado por T-AUTH-7) | `routes/web.php` |
| `dependencias` sin permiso propio | **aceptado**: reutiliza `ver-departamentos` (§4.7, deuda) | — |
| `/perfil` | autenticado (perfil propio); sin `->can()` por diseño | `routes/web.php` |
| Botón "Exportar" en gestión visible sin `reportes-excel` | `@can('reportes-excel')` + tests UI (oculta/visible) | `gestion.blade.php`, `AuthorizationTest` |
| Botón Excel en usuarios visible sin `reportes-excel` | `@can('reportes-excel')` | `usuarios.blade.php` |
| Botones de logs en `/admin/auditoria` gateados con `reportes-excel` | `@can('admin-auditoria')` (coherente con ruta y controlador) | `auditoria.blade.php` |
| `MainDashboard::$esResolutor` nunca se activaba (resolutor veía todo) | flag recalculado en `mount()` | `MainDashboard.php` |
| Componente `Dashboard` legado sin ruta | eliminado | — |

