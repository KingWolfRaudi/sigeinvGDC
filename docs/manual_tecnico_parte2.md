# Manual Técnico SIGEINV — Parte 2: Relaciones, Lógica de Negocio y Componentes

## 5. Diagrama de Relaciones entre Modelos

```
users ──────────────────────────────────────────────────────────┐
  │ trabajador_id (BelongsTo)                                    │
  ▼                                                              │ created_by/updated_by
trabajadores ──belongsTo──► departamentos                        │ (RecordSignature en todos los modelos)
  │           ──belongsTo──► dependencias (nullable)
  └───────── (dependencias.departamento_id → departamentos)

computadores ──belongsTo──► marcas
     │         ──belongsTo──► sistemas_operativos
     │         ──belongsTo──► procesadores
     │         ──belongsTo──► gpus (nullable)
     │         ──belongsTo──► departamentos
     │         ──belongsTo──► dependencias (nullable)
     │         ──belongsTo──► trabajadores
     │         ──hasMany───► computador_rams
     │         ──hasMany───► computador_discos
     │         ──belongsToMany──► puertos (via computador_puerto)
     │         ──hasMany───► movimientos_computador
     │         ──morphMany──► incidencias (modelo_type = Computador)
     └──────── (referenciado por dispositivos.computador_id)
               (referenciado por insumos.computador_id)

dispositivos ──belongsTo──► tipo_dispositivos
     │          ──belongsTo──► marcas
     │          ──belongsTo──► departamentos
     │          ──belongsTo──► dependencias (nullable)
     │          ──belongsTo──► trabajadores
     │          ──belongsTo──► computadores (nullable, equipo host)
     │          ──belongsToMany──► puertos (via dispositivo_puerto)
     │          ──hasMany───► movimientos_dispositivos
     │          ──morphMany──► incidencias (modelo_type = Dispositivo)
     └──────── (referenciado por insumos.dispositivo_id)

insumos ──belongsTo──► marcas
    │     ──belongsTo──► categoria_insumos
    │     ──belongsTo──► departamentos (nullable)
    │     ──belongsTo──► trabajadores (nullable)
    │     ──belongsTo──► dispositivos (nullable)
    │     ──belongsTo──► computadores (nullable)
    │     ──hasMany───► movimientos_insumos
    └───────morphMany──► incidencias (modelo_type = Insumo)

incidencias ──belongsTo──► problemas
     │         ──belongsTo──► departamentos
     │         ──belongsTo──► trabajadores (solicitante)
     │         ──belongsTo──► users (user_id = técnico resolutor)
     │         ──morphTo───► [Computador | Dispositivo | Insumo]
     │         ──hasOne────► movimientos_computador (via incidencia_id)
     │         ──hasOne────► movimientos_dispositivos (via incidencia_id)
     └─────────hasOne────► movimientos_insumos (via incidencia_id)

movimientos_computador ──belongsTo──► computadores
     │                   ──belongsTo──► users (solicitante_id)
     │                   ──belongsTo──► users (aprobador_id)
     └──────────────────── belongsTo──► incidencias (nullable)

solicitudes_perfil ──belongsTo──► users

gpus ──belongsToMany──► puertos (via gpu_puerto)
     ──belongsTo──► marcas

procesadores ──belongsTo──► marcas
```

## 6. Relación Polimórfica: Incidencias ↔ Activos

La tabla `incidencias` utiliza una relación polimórfica para asociarse con cualquier tipo de activo tecnológico:

```php
// En la migración:
$table->unsignedBigInteger('modelo_id')->nullable();
$table->string('modelo_type')->nullable();

// Valores de modelo_type:
// 'App\Models\Computador'
// 'App\Models\Dispositivo'
// 'App\Models\Insumo'
```

**Flujo en el código:**
```php
// Definición en Incidencia.php
public function modelo() { return $this->morphTo(); }

// Definición inversa en Computador.php / Dispositivo.php / Insumo.php
public function incidencias() {
    return $this->morphMany(Incidencia::class, 'modelo');
}

// Uso en Livewire (consulta con polimorfismo):
Incidencia::with('modelo')->get();
// $incidencia->modelo → retorna instancia de Computador, Dispositivo o Insumo
```

## 7. Componentes Livewire

Cada componente Livewire combina lógica de controlador y vista en un solo ciclo reactivo. Todos los componentes que renderizan página completa usan el atributo `#[Layout('components.layouts.app')]`.

### 7.1 Listado Completo de Componentes

| Namespace | Clase | Ruta nombrada | Función |
|---|---|---|---|
| `Livewire\Auth` | `Login` | `login` | Autenticación con username o email |
| `Livewire\Dashboard` | `MainDashboard` | `dashboard` | Panel principal con métricas globales |
| `Livewire\Inventario` | `Computadores` | `inventario.computadores` | CRUD completo de computadores |
| `Livewire\Inventario` | `Dispositivos` | `inventario.dispositivos` | CRUD completo de dispositivos |
| `Livewire\Inventario` | `Insumos` | `inventario.insumos` | CRUD y control de stock de insumos |
| `Livewire\Inventario` | `Software` | `inventario.software` | CRUD de software corporativo |
| `Livewire\Incidencias` | `Gestion` | `incidencias.gestion` | Mesa de soporte — atención y resolución de tickets |
| `Livewire\Incidencias` | `CrearTicket` | `incidencias.crear` | Portal de reporte de fallas para usuarios |
| `Livewire\Movimientos` | `PanelComputadores` | `movimientos.computadores` | Workflow de movimientos de PCs |
| `Livewire\Movimientos` | `PanelDispositivos` | `movimientos.dispositivos` | Workflow de movimientos de dispositivos |
| `Livewire\Movimientos` | `PanelInsumos` | `movimientos.insumos` | Workflow de movimientos de insumos |
| `Livewire\Movimientos` | `SolicitudesPerfil` | `movimientos.solicitudes-perfil` | Aprobación de solicitudes de cambio de perfil |
| `Livewire\Admin` | `Usuarios` | `admin.usuarios` | CRUD de usuarios del sistema |
| `Livewire\Admin` | `Roles` | `admin.roles` | Gestión de roles y asignación de permisos |
| `Livewire\Admin` | `ConfiguracionGeneral` | `admin.configuracion` | Parámetros globales del sistema |
| `Livewire\Admin` | `Auditoria` | `admin.auditoria` | Logs del sistema con exportación filtrada |
| `Livewire\Admin` | `AuditoriaTecnicos` | `admin.auditoria-tecnicos` | KPIs y actividad operativa por técnico |
| `Livewire\Admin` | `AuditoriaUsuarios` | `admin.auditoria-usuarios` | Perfil e historial de actividad por usuario |
| `Livewire\Asignaciones` | `Trabajadores` | `asignaciones.trabajadores` | CRUD de trabajadores |
| `Livewire\Asignaciones` | `Departamentos` | `asignaciones.departamentos` | CRUD de departamentos |
| `Livewire\Asignaciones` | `Dependencias` | `asignaciones.dependencias` | CRUD de dependencias (sub-dpto.) |
| `Livewire\Catalogos` | `Marcas` | `catalogos.marcas` | CRUD de marcas/fabricantes |
| `Livewire\Catalogos` | `TiposDispositivo` | `catalogos.tipos-dispositivo` | CRUD de tipos de periféricos |
| `Livewire\Catalogos` | `SistemasOperativos` | `catalogos.sistemas-operativos` | CRUD de sistemas operativos |
| `Livewire\Catalogos` | `Puertos` | `catalogos.puertos` | CRUD de tipos de puertos |
| `Livewire\Catalogos` | `Procesadores` | `catalogos.procesadores` | CRUD de procesadores |
| `Livewire\Catalogos` | `Gpus` | `catalogos.gpus` | CRUD de GPUs |
| `Livewire\Perfil` | `MiPerfil` | `perfil` | Perfil del usuario autenticado |
| `Livewire` | `AsociacionesDashboard` | `asociaciones` | Vista de asociaciones de activo (modal extendido) |

### 7.2 Patrón de Comunicación Modal

Todos los modales en SIGEINV se controlan mediante eventos Livewire:

```php
// Abrir modal desde PHP (Livewire Component):
$this->dispatch('abrir-modal', id: 'modalComputador');

// Cerrar modal desde PHP:
$this->dispatch('cerrar-modal', id: 'modalComputador');

// Escucha en el Layout (app.blade.php) via JS:
Livewire.on('abrir-modal', (event) => {
    let modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById(event.id)
    );
    modal.show();
});
```

### 7.3 Sistema de Notificaciones Toast

```php
// Dispatch desde cualquier componente Livewire:
$this->dispatch('notificar', [
    'icon'  => 'success', // success | error | warning | info
    'title' => 'Operación completada.',
    'text'  => 'El registro fue guardado.',
]);
```

## 8. Flujo de Workflow de Movimientos

```
Usuario edita activo
        │
        ▼
Componente Livewire valida campos + requiere justificacion
        │
        ▼
Se crea MovimientoXxx con:
  estado_workflow = 'borrador'
  payload_anterior = snapshot actual del activo (JSON)
  payload_nuevo = nuevos valores (JSON)
  solicitante_id = Auth::id()
        │
        ▼
Usuario envía a revisión → estado = 'pendiente'
        │
        ├─── Administrador APRUEBA:
        │         estado = 'aprobado'
        │         aprobador_id = Auth::id()
        │         aprobado_at = now()
        │         → Sistema aplica cambios al activo real
        │
        └─── Administrador RECHAZA:
                  estado = 'rechazado'
                  motivo_rechazo = texto del admin
                  → Activo NO se modifica

También existe la vía 'ejecutar-directo' (requiere permiso especial):
  estado_workflow = 'ejecutado_directo'
  → Aplica cambios inmediatamente sin aprobador
```

## 9. Flujo de Trazabilidad Incidencia → Movimiento

```
Incidencia registrada (amerita_movimiento = false)
        │
        ▼
Técnico activa flag 'amerita_movimiento' y guarda
        │
        ▼ (Aparece botón "Registrar Movimiento")
Técnico hace clic → Dispatch de evento con:
  - activo_tipo = modelo_type de la incidencia
  - activo_id   = modelo_id de la incidencia
  - incidencia_id = id de la incidencia
        │
        ▼
Panel de Movimientos recibe parámetros (via query string o session)
  → Pre-selecciona el activo
  → Pre-carga justificacion: "Vinculado a la incidencia #XXXXX"
  → Almacena incidencia_id en el registro de movimiento
        │
        ▼
MovimientoXxx.incidencia_id ← Trazabilidad permanente
```

## 10. Sistema de Auditoría (Spatie Activity Log)

Todos los modelos con el trait `LogsActivity` registran automáticamente en `activity_log`.

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()           // Registra todos los campos
        ->logOnlyDirty()     // Solo si hubo cambio real
        ->dontSubmitEmptyLogs(); // No registra si no cambió nada
}
```

**Estructura del registro de auditoría (`activity_log`):**

| Campo | Descripción |
|---|---|
| `log_name` | Nombre del log (default) |
| `description` | Acción: `created` / `updated` / `deleted` / texto libre |
| `subject_type` | Clase del modelo afectado (ej: `App\Models\Incidencia`) |
| `subject_id` | ID del registro afectado |
| `causer_type` | Clase del causante (`App\Models\User`) |
| `causer_id` | ID del usuario que realizó la acción |
| `properties` | JSON con `old` (valores anteriores) y `attributes` (valores nuevos) |
| `event` | Tipo de evento |
| `created_at` | Timestamp de la acción |

**Eventos de sesión registrados manualmente:**
- `Inicio de sesión exitoso` — con propiedad `ip`
- `Intento fallido de inicio de sesión` — con propiedades `ip` e `identificador_intentado`
- `Cierre de sesión` — con propiedad `ip`

**Formateador de valores en las vistas de auditoría:**
Las vistas de auditoría aplican un formateador automático sobre los valores del JSON `properties`:
- Campos booleanos (`activo`, `solventado`, `disponible`) → se muestran como **Sí / No**
- Campos de relación (`*_id`, `*_by`) → se muestran como **ID X**
- Arrays → se serializan como JSON
- Valores vacíos o nulos → se muestran como **N/A**

## 11. Sistema de Permisos (Spatie Laravel-Permission)

### 11.1 Roles del Sistema

| Rol | Descripción | Permisos base |
|---|---|---|
| `super-admin` | Control total del sistema | Todos los permisos |
| `administrador` | Gestión completa de inventario y personal | Todos los permisos menos `eliminar-*` |
| `coordinador` | Supervisión de equipos, movimientos e incidencias | Reportes, auditoría de técnicos, incidencias completas, movimientos, catálogos/asignaciones/inventario (sin eliminar), especialidades |
| `personal-ti` | Gestión operativa de equipos técnicos | Reportes, incidencias (sin solicitudes de perfil), movimientos, catálogos/asignaciones/inventario (sin eliminar) |
| `resolutor-incidencia` | Especialista en resolución de fallas | `ver-incidencias`, `gestionar-incidencias`, `ver-dashboard` |
| `trabajador` | Usuario estándar | `crear-ticket`, `ver-dashboard` |

### 11.2 Permisos Definidos por Módulo

Los permisos CRUD de entidades se generan automáticamente en el Seeder con el patrón `{accion}-{entidad}`.

**Entidades con CRUD completo** (`ver`, `crear`, `editar`, `eliminar`, `cambiar-estatus`, `ver-estado`):
- `computadores`, `dispositivos`, `insumos`, `software`
- `trabajadores`, `departamentos`
- `marcas`, `tipo-dispositivo`, `sistemas-operativos`, `puertos`, `procesadores`, `gpus`
- `usuarios`, `roles`, `especialidades`

> Los módulos `software` y `roles` no incluyen `cambiar-estatus` ni `ver-estado`.

**Permisos especiales (definidos manualmente en el Seeder):**

**Movimientos:**
- `movimientos-computadores-crear`, `movimientos-computadores-ver`, `movimientos-computadores-enviar`
- `movimientos-computadores-aprobar`, `movimientos-computadores-rechazar`, `movimientos-computadores-ejecutar-directo`
- *(misma estructura para `dispositivos` e `insumos`)*

**Incidencias:**
- `crear-ticket` — Reporte de fallas desde el portal de usuario
- `gestionar-incidencias` — Acceso a la mesa de soporte
- `admin-incidencias` — Configuración avanzada del módulo
- `ver-incidencias` — Histórico de incidencias
- `admin-solicitudes-perfil` — Gestión de cambios de perfil

**Auditoría:**
- `admin-auditoria` — Acceso total a logs del sistema
- `ver-auditoria-tecnicos` — Vista de KPIs y actividad de técnicos
- `ver-auditoria-usuarios` — Vista de auditoría individual por usuario

**Reportes:**
- `reportes-excel` — Exportación a Excel y PDF (compartido)
- `reportes-pdf` — Generación de fichas PDF de activos
- `reportes-masivos-filtros` — Generador de reportes multi-módulo (Generador Pro)

**Dashboard:**
- `ver-dashboard` — Acceso al panel principal. Todos los roles lo tienen. Si un usuario autenticado no tiene este permiso (sin rol), es redirigido automáticamente a su Perfil.
- `ver-panel-soporte` — Muestra la sección de Mesa de Ayuda (HelpDesk) dentro del Dashboard. Asignado exclusivamente a Administrador, Coordinador y Personal TI.

## 12. Exportaciones Excel

Todas las exportaciones están en `app/Exports/` e implementan interfaces de `Maatwebsite/Excel`. Son invocadas exclusivamente desde `ReporteController`.

| Clase | Tabla/Dato exportado | Acepta Filtros |
|---|---|---|
| `ComputadoresExport` | Inventario de computadores con relaciones | ✅ search, estado, departamento_id |
| `DispositivosExport` | Inventario de dispositivos con relaciones | ✅ search, estado, departamento_id |
| `InsumosExport` | Almacén de insumos con relaciones | ✅ search, estado, departamento_id |
| `SoftwareExport` | Catálogo de software | ✅ search, estado |
| `IncidenciasExport` | Historial de incidencias | ✅ search, estado, departamento_id |
| `MovimientosExport` | Movimientos (computadores/dispositivos/insumos) | ✅ search, tipo_operacion, estado_workflow |
| `UsersExport` | Listado de usuarios del sistema | ✅ search, estado |
| `LogsExport` | Auditoría de logs del sistema filtrada | ✅ searchUser, searchModule, dateFrom, dateTo |
| `CatalogosExport` | Catálogos genéricos (Marcas, Tipos, Dptos, etc.) | ✅ search, estado |
| `SolicitudesPerfilExport` | Solicitudes de cambio de perfil | ✅ |
| `MassiveReportExport` | Reporte multi-hoja con múltiples módulos | ✅ (por módulo) |

**Estándares de columnas:**
- Nunca exponer IDs de base de datos en columnas descriptivas.
- Siempre mapear relaciones (ej. `$model->departamento->nombre`).
- Incluir: `Creado Por`, `Modificado Por`, `Fecha Registro`, `Última Modificación`.
- En movimientos: columna `Folio Incidencia` si `incidencia_id` no es null.
- Incluir título y subtítulo de filtros aplicados en las primeras filas (vía `AfterSheet` event).

## 13. Reportes PDF

Todos los reportes PDF son generados por **un único controlador centralizado**: `App\Http\Controllers\ReporteController`, usando `Barryvdh\DomPDF`. Todas las rutas están bajo el prefijo `/reportes` con nombre `reportes.*`.

### 13.1 Fichas Individuales (GET con `{id}`)

| Ruta nombrada | Método | Vista Blade | Descripción |
|---|---|---|---|
| `reportes.computador.ficha` | `computadorFicha($id)` | `reports/ficha-computador` | Ficha técnica completa del PC |
| `reportes.dispositivo.ficha` | `dispositivoFicha($id)` | `reports/ficha-dispositivo` | Ficha técnica del periférico |
| `reportes.insumo.ficha` | `insumoFicha($id)` | `reports/ficha-insumo` | Ficha del insumo |
| `reportes.gpu.ficha` | `gpuFicha($id)` | `reports/ficha-gpu` | Ficha técnica de GPU |
| `reportes.incidencia.ficha` | `incidenciaFicha($id)` | `reports/ficha-incidencia` | Reporte de ticket |

### 13.2 Exportaciones Excel (GET con filtros por query string)

| Ruta nombrada | Método | Descripción |
|---|---|---|
| `reportes.inventario.computadores.excel` | `computadoresExcel` | Inventario de PCs filtrado |
| `reportes.inventario.dispositivos.excel` | `dispositivosExcel` | Inventario de dispositivos filtrado |
| `reportes.inventario.insumos.excel` | `insumosExcel` | Almacén de insumos filtrado |
| `reportes.inventario.software.excel` | `softwareExcel` | Catálogo de software filtrado |
| `reportes.catalogo.excel` | `catalogoExcel($tipo)` | Catálogos genéricos (marcas, tipos, etc.) |
| `reportes.incidencias.excel` | `incidenciasExcel` | Historial de incidencias |
| `reportes.movimientos.excel` | `movimientosExcel($segmento)` | Movimientos por segmento |
| `reportes.usuarios.excel` | `usuariosExcel` | Listado de usuarios |
| `reportes.logs.excel` | `logsExcel` | Auditoría de logs filtrada a Excel |

### 13.3 PDFs de Auditoría

| Ruta nombrada | Método | Vista Blade | Descripción |
|---|---|---|---|
| `reportes.logs.pdf` | `logsPdf` | `reports/logs-pdf` | PDF de auditoría general filtrada (máx. 500 registros, landscape) |
| `reportes.auditoria-usuario.pdf` | `auditoriaUsuarioPdf($id)` | `reports/auditoria-usuario-pdf` | PDF de auditoría individual de un usuario (máx. 200 registros, portrait) |

### 13.4 Reporte Masivo

| Ruta nombrada | Método | Descripción |
|---|---|---|
| `reportes.masivo.excel` | `reporteMasivo` (POST) | Excel multi-hoja con módulos seleccionados |

> Requiere permiso `reportes-masivos-filtros`. Genera un archivo con una hoja por cada módulo seleccionado.

## 14. Configuraciones del Sistema

La tabla `configuraciones` almacena pares clave-valor para parámetros operativos editables desde la UI (`ConfiguracionGeneral`):

| Clave | Tipo | Descripción |
|---|---|---|
| `incidencias_cierre_irreversible` | boolean | Si true, las incidencias cerradas no pueden reabrirse |
| `incidencias_activo_obligatorio` | boolean | Si true, crear ticket sin activo vinculado lanza validación |
| `dashboard_tecnico_ver_global` | boolean | Si true, técnicos ven todos los tickets pendientes en el dashboard; si false, solo los de su especialidad |

**Lectura en componentes:**
```php
$config = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
$this->cierre_irreversible = $config ? (bool)$config->valor : false;
```

## 15. Guía de Despliegue y Mantenimiento

### 15.1 Requisitos del Servidor

- PHP 8.1+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `xml`, `json`, `gd` (para PDF)
- Composer 2.x
- Node.js 18+ y NPM (para compilar assets)
- MariaDB 10.6+ / MySQL 8+
- Servidor Web: Apache/Nginx con `mod_rewrite` habilitado

### 15.2 Instalación

```bash
# 1. Clonar repositorio
git clone <url-repositorio> sigeinvGDC
cd sigeinvGDC

# 2. Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# 3. Instalar dependencias JS y compilar
npm install && npm run build

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=sigeinvGDC
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Crear enlace de almacenamiento (avatares)
php artisan storage:link

# 8. Optimizar para producción
php artisan optimize
```

### 15.3 Orden de Seeders de Producción

```
DatabaseSeeder (seeder maestro de producción)
  └► RolesAndPermissionsSeeder  → Crea permisos, roles, usuario sistema y superadmin
  └► CatalogosSeeder            → Datos maestros: marcas, tipos, SO, dptos, procesadores, GPUs
  └► IncidenciasSeeder          → Especialidades técnicas, tipos de problemas y configuraciones
```

> **Importante:** `RolesAndPermissionsSeeder` es idempotente (`updateOrCreate` y `firstOrCreate`). Se puede re-ejecutar sin riesgo de duplicar datos. Siempre limpiar la caché de permisos tras correrlo: `php artisan permission:cache-reset`.

> Los seeders `InventarioSeeder`, `SoftwareSeeder` y `DemoTicketsSeeder` existen en el repositorio pero **no se incluyen en el `DatabaseSeeder` de producción**. Son exclusivamente para entornos de desarrollo.

### 15.4 Variables de Entorno Personalizadas

| Variable | Descripción | Ejemplo |
|---|---|---|
| `ORG_NOMBRE` | Nombre del sistema en la UI | `"SIGEINV - Gerencia de TI"` |
| `ORG_DEPENDENCIA` | Nombre de la dependencia | `"Dirección General de Tecnología"` |
| `DOMINIO_ORGANIZACION` | Dominio para correos institucionales | `"@institucion.gob.ve"` |

### 15.5 Comandos de Mantenimiento Frecuentes

```bash
# Limpiar todas las cachés
php artisan optimize:clear

# Regenerar cachés de producción
php artisan optimize

# Resetear caché de permisos Spatie
php artisan permission:cache-reset

# Revertir y re-ejecutar migraciones (DESTRUCTIVO — solo desarrollo)
php artisan migrate:fresh --seed

# Ver rutas registradas
php artisan route:list

# Verificar configuración de entorno
php artisan config:show
```

### 15.6 Ruta de Emergencia: Sincronización de Permisos

Existe una ruta temporal en `web.php` para sincronizar permisos nuevos sin necesidad de acceso a consola:

```
GET /seed-permissions
```

Esta ruta crea los permisos `ver-auditoria-tecnicos` y `ver-auditoria-usuarios` y los asigna a los roles `super-admin` y `administrador`. Debe protegerse o eliminarse en producción.

### 15.7 Seguridad y Backups

- **Base de datos:** Realizar backup diario de MariaDB con `mysqldump`.
- **Archivos:** Hacer backup del directorio `storage/app/public/` (avatares).
- **Variables sensibles:** Nunca versionar el archivo `.env`.
- **`APP_DEBUG`:** Asegurarse de que en producción esté en `false`. Tenerlo en `true` expone trazas del stack, credenciales y variables de entorno en pantalla cuando ocurre un error.
- **`APP_ENV`:** Debe ser `production` en el servidor final.
- **Permisos de carpetas:** `storage/` y `bootstrap/cache/` deben tener permisos `775`.
- **Auditoría de acceso:** Todo inicio/cierre de sesión queda registrado en `activity_log` con la IP del cliente.
- **Subida de archivos:** El módulo de avatar usa `extension()` (inferido por MIME) en lugar de `getClientOriginalExtension()`, para prevenir suplantación de extensiones.
- **Ruta `/home`:** La constante `HOME` del `RouteServiceProvider` apunta a `/`. Los usuarios autenticados sin rol son redirigidos automáticamente a `/perfil` por la lógica del `MainDashboard`.

---
*Manual Técnico SIGEINV — Versión 2.0 (Actualizado: Mayo 2026)*
