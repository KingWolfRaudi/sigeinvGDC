# Manual Técnico SIGEINV — Parte 2: Relaciones, Lógica de Negocio y Componentes

## 5. Diagrama de Relaciones entre Modelos

```
users ──────────────────────────────────────────────────────────┐
  │ trabajador_id (BelongsTo)                                    │
  ▼                                                              │ created_by/updated_by
trabajadores                                                     │ (RecordSignature en todos los modelos)
  │ departamento_id (BelongsTo)
  ▼
departamentos

computadores ──belongsTo──► marcas
     │         ──belongsTo──► sistemas_operativos
     │         ──belongsTo──► procesadores
     │         ──belongsTo──► gpus (nullable)
     │         ──belongsTo──► departamentos
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
     │         ──morphTo───► [Computador | Dispositivo | Insumo] (modelo_id/modelo_type)
     │         ──hasOne────► movimientos_computador (via incidencia_id)
     │         ──hasOne────► movimientos_dispositivos (via incidencia_id)
     └─────────hasOne────► movimientos_insumos (via incidencia_id)

movimientos_computador ──belongsTo──► computadores
     │                   ──belongsTo──► users (solicitante_id)
     │                   ──belongsTo──► users (aprobador_id)
     └─────────────────── ──belongsTo──► incidencias (nullable)

software ──(sin relaciones FK directas, auditoría via created_by/updated_by)

solicitudes_perfil ──belongsTo──► users
```

## 6. Relación Polimórfica: Incidencias ↔ Activos

La tabla `incidencias` utiliza una relación polimórfica para asociarse con cualquier tipo de activo tecnológico:

```php
// En incidencias:
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

Cada componente Livewire combina lógica de controlador y vista en un solo ciclo reactivo.

### 7.1 Listado de Componentes

| Namespace | Clase | Vista | Función |
|---|---|---|---|
| `Livewire\Auth` | `Login` | `auth/login` | Autenticación |
| `Livewire\Dashboard` | `MainDashboard` | `dashboard/main-dashboard` | Panel principal con métricas |
| `Livewire\Inventario` | `Computadores` | `inventario/computadores` | CRUD Computadores |
| `Livewire\Inventario` | `Dispositivos` | `inventario/dispositivos` | CRUD Dispositivos |
| `Livewire\Inventario` | `Insumos` | `inventario/insumos` | CRUD Insumos |
| `Livewire\Inventario` | `Software` | `inventario/software` | CRUD Software |
| `Livewire\Incidencias` | `Gestion` | `incidencias/gestion` | Gestión técnica de tickets |
| `Livewire\Incidencias` | `CrearTicket` | `incidencias/crear-ticket` | Reporte de fallas (usuario) |
| `Livewire\Movimientos` | `PanelComputadores` | `movimientos/panel-computadores` | Flujo movimientos PC |
| `Livewire\Movimientos` | `PanelDispositivos` | `movimientos/panel-dispositivos` | Flujo movimientos Dispositivos |
| `Livewire\Movimientos` | `PanelInsumos` | `movimientos/panel-insumos` | Flujo movimientos Insumos |
| `Livewire\Movimientos` | `SolicitudesPerfil` | `movimientos/solicitudes-perfil` | Aprobación cambios de perfil |
| `Livewire\Admin` | `Usuarios` | `admin/usuarios` | Gestión de usuarios |
| `Livewire\Admin` | `Roles` | `admin/roles` | Gestión de roles/permisos |
| `Livewire\Admin` | `ConfiguracionGeneral` | `admin/configuracion-general` | Parámetros del sistema |
| `Livewire\Admin` | `Auditoria` | `admin/auditoria` | Logs del sistema |
| `Livewire\Asignaciones` | `Trabajadores` | `asignaciones/trabajadores` | CRUD Trabajadores |
| `Livewire\Asignaciones` | `Departamentos` | `asignaciones/departamentos` | CRUD Departamentos |
| `Livewire\Perfil` | `MiPerfil` | `perfil/mi-perfil` | Perfil del usuario autenticado |
| `Livewire` | `AsociacionesDashboard` | `asociaciones-dashboard` | Vista de asociaciones de activo |

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
$this->dispatch('mostrar-toast', [
    'mensaje' => 'Operación completada.',
    'tipo'    => 'success', // success | error | warning | info
]);

// Alias soportado (retrocompatibilidad):
$this->dispatch('toast', [...]);
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

Todos los modelos con `LogsActivity` registran en la tabla `activity_log`:

```php
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()          // Registra todos los campos
        ->logOnlyDirty()    // Solo si hubo cambio real
        ->dontSubmitEmptyLogs(); // No registra si no cambió nada
}
```

**Estructura del registro de auditoría:**

| Campo | Descripción |
|---|---|
| `log_name` | Nombre del log (default) |
| `description` | Acción: created / updated / deleted |
| `subject_type` | Clase del modelo afectado |
| `subject_id` | ID del registro afectado |
| `causer_type` | Clase del causante (User) |
| `causer_id` | ID del usuario que realizó la acción |
| `properties` | JSON con `old` (antes) y `attributes` (después) |
| `event` | Tipo de evento |
| `created_at` | Timestamp de la acción |

## 11. Sistema de Permisos (Spatie Laravel-Permission)

### 11.1 Permisos Definidos por Módulo

**Inventario - Computadores:**
- `ver-computadores`, `crear-computadores`, `editar-computadores`, `eliminar-computadores`
- `ver-estado-computadores`, `cambiar-estatus-computadores`

**Inventario - Dispositivos:**
- `ver-dispositivos`, `crear-dispositivos`, `editar-dispositivos`, `eliminar-dispositivos`
- `ver-estado-dispositivos`, `cambiar-estatus-dispositivos`

**Inventario - Insumos:**
- `ver-insumos`, `crear-insumos`, `editar-insumos`, `eliminar-insumos`
- `ver-estado-insumos`, `cambiar-estatus-insumos`

**Inventario - Software:**
- `ver-software`, `crear-software`, `editar-software`, `eliminar-software`

**Incidencias:**
- `ver-incidencias`, `crear-ticket`, `admin-incidencias`

**Movimientos:**
- `movimientos-computadores-ver`, `movimientos-computadores-crear`, `movimientos-computadores-aprobar`
- `movimientos-dispositivos-ver`, `movimientos-dispositivos-crear`, `movimientos-dispositivos-aprobar`
- `movimientos-insumos-ver`, `movimientos-insumos-crear`, `movimientos-insumos-aprobar`
- `admin-solicitudes-perfil`

**Catálogos:**
- `ver-marcas`, `ver-tipos-dispositivo`, `ver-sistemas-operativos`
- `ver-puertos`, `ver-procesadores`, `ver-gpus`

**Asignaciones:**
- `ver-trabajadores`, `ver-departamentos`

**Administración:**
- `ver-roles`, `crear-roles`, `editar-roles`, `eliminar-roles`
- `ver-usuarios`, `crear-usuarios`, `editar-usuarios`, `cambiar-estatus-usuarios`, `eliminar-usuarios`
- `admin-auditoria`

**Reportes:**
- `reportes-excel`, `reportes-pdf`

## 12. Exportaciones Excel

Ubicadas en `app/Exports/`. Implementan la interfaz `FromQuery` de Maatwebsite/Excel.

| Clase | Tabla exportada |
|---|---|
| `ComputadoresExport` | Inventario de computadores con relaciones |
| `DispositivosExport` | Inventario de dispositivos con relaciones |
| `InsumosExport` | Almacén de insumos con relaciones |
| `MovimientosExport` | Movimientos con Folio de Incidencia vinculada |

**Estándar de columnas en exportaciones:**
- Nunca exponer IDs de base de datos en columnas descriptivas.
- Siempre mapear relaciones (ej. `$model->departamento->nombre`).
- Incluir: `Creado Por`, `Modificado Por`, `Fecha Registro`, `Última Modificación`.
- En movimientos: columna `Folio Incidencia` si `incidencia_id` no es null.

## 13. Reportes PDF

Controladores en `app/Http/Controllers/` con rutas nombradas:

| Ruta | Controlador | Descripción |
|---|---|---|
| `reportes.computador.ficha` | `ReporteComputadorController` | Ficha técnica de computador |
| `reportes.dispositivo.ficha` | `ReporteDispositivoController` | Ficha técnica de dispositivo |
| `reportes.incidencia.ficha` | `ReporteIncidenciaController` | Ficha de incidencia |

Vistas en `resources/views/reports/`.

## 14. Configuraciones del Sistema

La tabla `configuraciones` almacena pares clave-valor para parámetros operativos:

| Clave | Tipo | Descripción |
|---|---|---|
| `incidencias_cierre_irreversible` | boolean | Si true, las incidencias cerradas no pueden reabrirse |
| `incidencias_activo_obligatorio` | boolean | Si true, crear ticket sin activo lanza validación |

**Lectura en componentes:**
```php
$config = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
$this->cierre_irreversible = $config ? (bool)$config->valor : false;
```

## 15. Guía de Despliegue y Mantenimiento

### 15.1 Requisitos del Servidor
- PHP 8.3+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `xml`, `json`
- Composer 2.x
- Node.js 18+ y NPM (para compilar assets)
- MariaDB 10.6+ / MySQL 8+
- Servidor Web: Apache/Nginx con mod_rewrite habilitado

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

### 15.3 Orden de Seeders

```
RolesSeeder → Crea roles y permisos base
ConfiguracionSeeder → Carga valores de configuracion por defecto
MarcasSeeder → Datos maestros de catálogos
```

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

# Revertir y re-ejecutar migraciones (DESTRUCTIVO)
php artisan migrate:fresh --seed

# Ver rutas registradas
php artisan route:list

# Verificar configuración
php artisan config:show
```

### 15.6 Seguridad y Backups

- **Base de datos:** Realizar backup diario de MariaDB con `mysqldump`.
- **Archivos:** Hacer backup del directorio `storage/app/public/` (avatares).
- **Variables sensibles:** Nunca versionar el archivo `.env`.
- **Permisos de carpetas:** `storage/` y `bootstrap/cache/` deben tener permisos `775`.

---
*Manual Técnico SIGEINV — Versión 1.0*
