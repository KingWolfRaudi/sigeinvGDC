# Manual Técnico SIGEINV — Parte 1: Arquitectura y Base de Datos

## 1. Stack Tecnológico

| Componente | Tecnología |
|---|---|
| Lenguaje | PHP 8.1+ |
| Framework | Laravel 10 |
| Frontend | Livewire 3 + Bootstrap 5.3 |
| Base de Datos | MariaDB 10.6+ / MySQL 8+ |
| Auth/Permisos | Spatie Laravel-Permission |
| Auditoría | Spatie Laravel-Activitylog |
| Exportaciones Excel | Maatwebsite/Laravel-Excel |
| Exportaciones PDF | Barryvdh/DomPDF |

## 2. Estructura de Directorios

```
app/
├── Exports/          # Clases de exportación Excel (11 clases)
├── Http/Controllers/ # Controladores PDF/Excel (ReporteController)
├── Livewire/         # Componentes Livewire (controladores de UI)
│   ├── Admin/
│   ├── Asignaciones/
│   ├── Auth/
│   ├── Catalogos/
│   ├── Dashboard/
│   ├── Incidencias/
│   ├── Inventario/
│   ├── Movimientos/
│   └── Perfil/
├── Models/           # Modelos Eloquent (26 modelos)
└── Traits/
    └── RecordSignature.php  # Auditoría created_by/updated_by
database/
├── migrations/
└── seeders/
    ├── RolesAndPermissionsSeeder.php  # Permisos, roles y superadmin (seeder maestro)
    ├── CatalogosSeeder.php            # Datos base de catálogos (marcas, tipos, SO, etc.)
    ├── IncidenciasSeeder.php          # Especialidades, tipos de problemas y configuraciones
    ├── DatabaseSeeder.php             # Orquestador (solo los 3 seeders de producción)
    ├── InventarioSeeder.php           # Datos de ejemplo (solo desarrollo)
    ├── SoftwareSeeder.php             # Software de ejemplo (solo desarrollo)
    └── DemoTicketsSeeder.php          # Tickets de ejemplo (solo desarrollo)
resources/views/
├── components/layouts/app.blade.php  # Layout principal con sidebar reactivo
├── livewire/         # Vistas de componentes Livewire
└── reports/          # Plantillas Blade para reportes PDF
routes/
└── web.php           # Todas las rutas del sistema
```

## 3. Trait RecordSignature

Todos los modelos operativos implementan el trait `App\Traits\RecordSignature`, el cual gestiona automáticamente los campos de auditoría propios del sistema:

- `created_by` → FK a `users.id` (quién creó el registro)
- `updated_by` → FK a `users.id` (quién modificó el registro por última vez)

Modelos que lo implementan: `User`, `Computador`, `Dispositivo`, `Insumo`, `Software`, `Incidencia`, `MovimientoComputador`, `MovimientoDispositivo`, `MovimientoInsumo`, `Trabajador`, `SolicitudPerfil`, `Marca`, `Departamento`, `Dependencia`, y todos los catálogos.

> **Nota:** Este trait es complementario a `Spatie Laravel-Activitylog`. El trait registra el *quién* en columnas de la tabla del propio modelo, mientras que Activitylog registra el *qué* en la tabla `activity_log` como historial detallado de cambios.

## 4. Esquema Completo de Base de Datos

### 4.1 Tabla: `users`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| name | varchar(255) | NO | — | Nombre completo |
| email | varchar(255) | SI | UNIQUE | Correo electrónico |
| username | varchar(255) | SI | UNIQUE | Nombre de usuario (para login) |
| email_verified_at | timestamp | SI | — | Fecha verificación |
| password | varchar(255) | NO | — | Hash de contraseña (bcrypt) |
| avatar | varchar(255) | SI | — | Ruta del avatar en storage |
| activo | tinyint(1) | NO | — | Estado del usuario (default: 1) |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores | Vínculo con ficha de trabajador |
| disponible_asignacion | tinyint(1) | NO | — | Disponible para asignación de tickets |
| especialidad_id | bigint UNSIGNED | SI | FK→especialidades_tecnicas | Especialidad técnica del técnico |
| remember_token | varchar(100) | SI | — | Token de sesión persistente |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría: quién creó el registro |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría: quién modificó el registro |
| created_at | timestamp | SI | — | Fecha de creación |
| updated_at | timestamp | SI | — | Fecha de última modificación |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.2 Tabla: `trabajadores`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| nombres | varchar(255) | NO | — | Nombres del trabajador |
| apellidos | varchar(255) | NO | — | Apellidos del trabajador |
| cedula | varchar(255) | SI | UNIQUE | Cédula de identidad |
| cargo | varchar(255) | SI | — | Cargo o posición |
| departamento_id | bigint UNSIGNED | NO | FK→departamentos | Departamento asignado |
| dependencia_id | bigint UNSIGNED | SI | FK→dependencias | Dependencia (subdivisión del dpto.) |
| user_id | bigint UNSIGNED | SI | FK→users | Cuenta de sistema vinculada |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.3 Tabla: `departamentos`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| nombre | varchar(255) | NO | — | Nombre del departamento |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.4 Tabla: `dependencias`

Subdivisionamiento organizativo dentro de un departamento.

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| nombre | varchar(255) | NO | — | Nombre de la dependencia |
| departamento_id | bigint UNSIGNED | NO | FK→departamentos | Departamento padre |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.5 Tabla: `computadores`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| bien_nacional | varchar(255) | NO | UNIQUE | Número de inventario institucional |
| serial | varchar(255) | NO | UNIQUE | Serial del equipo |
| nombre_equipo | varchar(15) | NO | — | Nombre del host en red (máx 15 chars) |
| marca_id | bigint UNSIGNED | NO | FK→marcas (RESTRICT) | Fabricante |
| tipo_computador | varchar(255) | NO | — | Desktop / Laptop / Servidor |
| sistema_operativo_id | bigint UNSIGNED | NO | FK→sistemas_operativos | SO instalado |
| procesador_id | bigint UNSIGNED | NO | FK→procesadores | CPU |
| gpu_id | bigint UNSIGNED | SI | FK→gpus | GPU dedicada (nullable) |
| departamento_id | bigint UNSIGNED | SI | FK→departamentos (RESTRICT) | Ubicación del equipo |
| dependencia_id | bigint UNSIGNED | SI | FK→dependencias | Subdivisión del departamento |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores (RESTRICT) | Responsable/Custodio |
| tipo_ram | enum | NO | — | DDR2 / DDR3 / DDR4 / DDR5 / DDR6 |
| mac | varchar(255) | SI | UNIQUE | Dirección MAC de red |
| ip | varchar(255) | SI | — | Dirección IP |
| tipo_conexion | enum | SI | — | Ethernet / Wi-Fi / Ambas |
| unidad_dvd | tinyint(1) | NO | — | Tiene unidad DVD (default: 1) |
| fuente_poder | tinyint(1) | NO | — | Fuente de poder interna (default: 1) |
| estado_fisico | enum | NO | — | operativo / danado / indeterminado / en_reparacion / baja |
| observaciones | text | SI | — | Notas adicionales |
| activo | tinyint(1) | NO | — | Estado operativo (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.6 Tabla: `computador_rams` (HasMany de computadores)

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores |
| capacidad | varchar(255) | NO | Ej: "8GB", "16GB" |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |

### 4.7 Tabla: `computador_discos` (HasMany de computadores)

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores |
| capacidad | varchar(255) | NO | Ej: "500GB", "1TB" |
| tipo | varchar(255) | NO | HDD / SSD / NVME / M.2 |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |

### 4.8 Tabla: `computador_puerto` (Pivot BelongsToMany)

| Columna | Tipo | Descripción |
|---|---|---|
| computador_id | bigint UNSIGNED | FK→computadores |
| puerto_id | bigint UNSIGNED | FK→puertos |

### 4.9 Tabla: `dispositivos`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | — |
| bien_nacional | varchar(255) | NO | UNIQUE | Número de inventario |
| serial | varchar(255) | NO | UNIQUE | Serial del equipo |
| tipo_dispositivo_id | bigint UNSIGNED | NO | FK→tipo_dispositivos (RESTRICT) | Tipo de periférico |
| marca_id | bigint UNSIGNED | NO | FK→marcas (RESTRICT) | Fabricante |
| nombre | varchar(255) | NO | — | Modelo/nombre del dispositivo |
| ip | varchar(255) | SI | — | Dirección IP (si aplica) |
| estado | enum | NO | — | operativo / dañado / indeterminado / en_reparacion / baja |
| departamento_id | bigint UNSIGNED | NO | FK→departamentos (RESTRICT) | Ubicación |
| dependencia_id | bigint UNSIGNED | SI | FK→dependencias | Subdivisión del departamento |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores (RESTRICT) | Responsable |
| computador_id | bigint UNSIGNED | SI | FK→computadores (RESTRICT) | Equipo host vinculado (opcional) |
| notas | text | SI | — | Notas adicionales |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.10 Tabla: `dispositivo_puerto` (Pivot BelongsToMany)

| Columna | Tipo | Descripción |
|---|---|---|
| dispositivo_id | bigint UNSIGNED | FK→dispositivos |
| puerto_id | bigint UNSIGNED | FK→puertos |

### 4.11 Tabla: `insumos`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | — |
| bien_nacional | varchar(255) | SI | UNIQUE | Número de inventario |
| serial | varchar(255) | SI | UNIQUE | Serial (si aplica) |
| nombre | varchar(255) | NO | — | Nombre del insumo |
| descripcion | text | SI | — | Descripción detallada |
| marca_id | bigint UNSIGNED | NO | FK→marcas (RESTRICT) | Fabricante |
| categoria_insumo_id | bigint UNSIGNED | NO | FK→categoria_insumos (RESTRICT) | Categoría |
| departamento_id | bigint UNSIGNED | SI | FK→departamentos (SET NULL) | Ubicación |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores (SET NULL) | Responsable |
| dispositivo_id | bigint UNSIGNED | SI | FK→dispositivos (SET NULL) | Dispositivo asociado |
| computador_id | bigint UNSIGNED | SI | FK→computadores (SET NULL) | Computador asociado |
| unidad_medida | enum | NO | — | unidad / metros / litros / cajas / pares |
| medida_actual | decimal(8,2) | NO | — | Stock actual (default: 1.00) |
| medida_minima | decimal(8,2) | NO | — | Stock mínimo (default: 1.00) |
| reutilizable | tinyint(1) | NO | — | Requiere devolución (default: 0) |
| instalable_en_equipo | tinyint(1) | NO | — | Se instala en un equipo (default: 0) |
| estado_fisico | enum | NO | — | operativo / danado / indeterminado / en_reparacion / baja |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.12 Tabla: `software`

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| nombre_programa | varchar(100) | NO | Nombre del software |
| arquitectura_programa | enum | SI | 32bits / 64bits / Universal |
| tipo_licencia | enum | NO | Libre / Privativo |
| serial | varchar(50) | SI | Clave de activación |
| descripcion_programa | varchar(250) | SI | Descripción |
| activo | tinyint(1) | NO | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users (Auditoría) |
| updated_by | bigint UNSIGNED | SI | FK→users (Auditoría) |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |
| deleted_at | timestamp | SI | SoftDelete |

### 4.13 Tabla: `incidencias`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | — |
| problema_id | bigint UNSIGNED | NO | FK→problemas | Categoría del problema |
| departamento_id | bigint UNSIGNED | NO | FK→departamentos | Dpto. solicitante |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores | Trabajador solicitante |
| user_id | bigint UNSIGNED | SI | FK→users | Técnico resolutor asignado |
| modelo_id | bigint UNSIGNED | SI | — | ID del activo (polimórfico) |
| modelo_type | varchar(255) | SI | — | Clase del activo (polimórfico) |
| descripcion | text | NO | — | Descripción de la falla |
| nota_resolucion | varchar(500) | SI | — | Diagnóstico/nota de resolución |
| amerita_movimiento | tinyint(1) | NO | — | Requiere movimiento de inventario |
| solventado | tinyint(1) | NO | — | Fue resuelto (default: 0) |
| cerrado | tinyint(1) | NO | — | Caso cerrado definitivamente |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.14 Tabla: `movimientos_computador`

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores (RESTRICT) |
| tipo_operacion | enum | NO | cambio_departamento / reasignacion_trabajador / cambio_estado / actualizacion_datos / baja / toggle_activo |
| payload_anterior | json | SI | Snapshot del estado ANTES del cambio |
| payload_nuevo | json | NO | Datos propuestos del cambio |
| estado_workflow | enum | NO | borrador / pendiente / aprobado / rechazado / ejecutado_directo |
| justificacion | text | NO | Justificación obligatoria del cambio |
| motivo_rechazo | text | SI | Razón del rechazo (si aplica) |
| incidencia_id | bigint UNSIGNED | SI | FK→incidencias (SET NULL) — Trazabilidad |
| solicitante_id | bigint UNSIGNED | NO | FK→users (RESTRICT) |
| aprobador_id | bigint UNSIGNED | SI | FK→users (SET NULL) |
| aprobado_at | timestamp | SI | Fecha/hora de aprobación |
| created_by | bigint UNSIGNED | SI | FK→users |
| updated_by | bigint UNSIGNED | SI | FK→users |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |
| deleted_at | timestamp | SI | SoftDelete |

> Las tablas `movimientos_dispositivos` y `movimientos_insumos` siguen la misma estructura, reemplazando `computador_id` por `dispositivo_id` e `insumo_id` respectivamente.

### 4.15 Tabla: `solicitudes_perfil`

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| user_id | bigint UNSIGNED | NO | FK→users (CASCADE) |
| tipo | enum | NO | nombre / username / email / password |
| valor_nuevo | varchar(255) | NO | Nuevo valor solicitado |
| estado | varchar(20) | NO | pendiente / aprobado / rechazado (default: pendiente) |
| motivo_rechazo | text | SI | Razón del rechazo |
| revisado_por | bigint UNSIGNED | SI | FK→users — Admin que revisó |
| created_by | bigint UNSIGNED | SI | FK→users |
| updated_by | bigint UNSIGNED | SI | FK→users |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |

### 4.16 Catálogos y Tablas de Soporte

| Tabla | Columnas principales | Uso |
|---|---|---|
| `marcas` | nombre, activo | Fabricantes de equipos y periféricos |
| `tipo_dispositivos` | nombre, activo | Clasificación de periféricos |
| `sistemas_operativos` | nombre, activo | SO instalados en computadores |
| `puertos` | nombre, activo | Tipos de puertos de conectividad |
| `procesadores` | marca_id, modelo, socket, activo | CPUs para computadores |
| `gpus` | marca_id, modelo, activo | GPUs dedicadas para computadores |
| `gpu_puerto` (pivot) | gpu_id, puerto_id | Puertos disponibles de cada GPU |
| `categoria_insumos` | nombre, activo | Categorías de insumos |
| `problemas` | nombre, especialidad_id, activo | Tipos de problemas para incidencias |
| `especialidades_tecnicas` | nombre, descripcion | Especialidades de técnicos resolutores |
| `configuraciones` | clave, valor | Parámetros de configuración del sistema |
| `dispositivo_puerto` (pivot) | dispositivo_id, puerto_id | Puertos de dispositivos periféricos |

### 4.17 Tablas Spatie (Permisos y Auditoría)

| Tabla | Descripción |
|---|---|
| `permissions` | Permisos individuales del sistema |
| `roles` | Roles de usuario |
| `model_has_permissions` | Permisos directos sobre modelos |
| `model_has_roles` | Roles asignados a usuarios |
| `role_has_permissions` | Permisos asignados a roles |
| `activity_log` | Log de auditoría completo de todas las acciones |

---
*Manual Técnico SIGEINV — Versión 2.0 (Actualizado: Mayo 2026)*
