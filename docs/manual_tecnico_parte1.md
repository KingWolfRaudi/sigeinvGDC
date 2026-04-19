# Manual Técnico SIGEINV — Parte 1: Arquitectura y Base de Datos

## 1. Stack Tecnológico

| Componente | Tecnología |
|---|---|
| Lenguaje | PHP 8.3+ |
| Framework | Laravel 10/12 |
| Frontend | Livewire 3 + Bootstrap 5.3 |
| Base de Datos | MariaDB |
| Auth/Permisos | Spatie Laravel-Permission |
| Auditoría | Spatie Laravel-Activitylog |
| Exportaciones | Maatwebsite/Laravel-Excel |

## 2. Estructura de Directorios

```
app/
├── Exports/          # Clases de exportación Excel
├── Http/Controllers/ # Controladores PDF (reportes)
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
├── Models/           # Modelos Eloquent (25 modelos)
└── Traits/
    └── RecordSignature.php  # Auditoría created_by/updated_by
database/
├── migrations/       # 35 migraciones
└── seeders/
resources/views/
├── components/layouts/app.blade.php  # Layout principal
├── livewire/         # Vistas de componentes
└── reports/          # Plantillas de reportes PDF
```

## 3. Trait RecordSignature

Todos los modelos operativos implementan el trait `App\Traits\RecordSignature`, el cual gestiona automáticamente los campos de auditoría:

- `created_by` → FK a `users.id` (quién creó el registro)
- `updated_by` → FK a `users.id` (quién modificó el registro por última vez)

Modelos que lo implementan: `User`, `Computador`, `Dispositivo`, `Insumo`, `Software`, `Incidencia`, `MovimientoComputador`, `MovimientoDispositivo`, `MovimientoInsumo`, `Trabajador`, `SolicitudPerfil`, `Marca`, `Departamento`, y todos los catálogos.

## 4. Esquema Completo de Base de Datos

### 4.1 Tabla: `users`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| name | varchar(255) | NO | — | Nombre completo |
| email | varchar(255) | SI | UNIQUE | Correo electrónico |
| username | varchar(255) | SI | UNIQUE | Nombre de usuario |
| email_verified_at | timestamp | SI | — | Fecha verificación |
| password | varchar(255) | NO | — | Hash de contraseña |
| avatar | varchar(255) | SI | — | Ruta del avatar |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| trabajador_id | bigint UNSIGNED | SI | FK | Vínculo con trabajador |
| disponible_asignacion | tinyint(1) | NO | — | Disponible para tickets |
| especialidad_id | bigint UNSIGNED | SI | FK→especialidades_tecnicas | Especialidad técnica |
| remember_token | varchar(100) | SI | — | Token de sesión |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | Fecha creación |
| updated_at | timestamp | SI | — | Fecha actualización |
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

### 4.4 Tabla: `computadores`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | Clave primaria |
| bien_nacional | varchar(255) | NO | UNIQUE | Número de inventario |
| serial | varchar(255) | NO | UNIQUE | Serial del equipo |
| nombre_equipo | varchar(15) | NO | — | Nombre del host (máx 15 chars) |
| marca_id | bigint UNSIGNED | NO | FK→marcas (RESTRICT) | Fabricante |
| tipo_computador | varchar(255) | NO | — | Desktop/Laptop/Servidor |
| sistema_operativo_id | bigint UNSIGNED | NO | FK→sistemas_operativos | SO instalado |
| procesador_id | bigint UNSIGNED | NO | FK→procesadores | CPU |
| gpu_id | bigint UNSIGNED | SI | FK→gpus | GPU dedicada (nullable) |
| departamento_id | bigint UNSIGNED | SI | FK→departamentos (RESTRICT) | Ubicación |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores (RESTRICT) | Responsable |
| tipo_ram | enum | NO | — | DDR2/DDR3/DDR4/DDR5/DDR6 |
| mac | varchar(255) | SI | UNIQUE | Dirección MAC |
| ip | varchar(255) | SI | — | Dirección IP |
| tipo_conexion | enum | SI | — | Ethernet/Wi-Fi/Ambas |
| unidad_dvd | tinyint(1) | NO | — | Tiene DVD (default: 1) |
| fuente_poder | tinyint(1) | NO | — | Fuente interna (default: 1) |
| estado_fisico | enum | NO | — | operativo/danado/indeterminado/en_reparacion/baja |
| observaciones | text | SI | — | Notas adicionales |
| activo | tinyint(1) | NO | — | Estado operativo (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.5 Tabla: `computador_rams` (HasMany de computadores)

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores |
| capacidad | varchar(255) | NO | Ej: "8GB", "16GB" |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |

### 4.6 Tabla: `computador_discos` (HasMany de computadores)

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores |
| capacidad | varchar(255) | NO | Ej: "500GB", "1TB" |
| tipo | varchar(255) | NO | HDD/SSD/NVME/M.2 |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |

### 4.7 Tabla: `computador_puerto` (Pivot BelongsToMany)

| Columna | Tipo | Descripción |
|---|---|---|
| computador_id | bigint UNSIGNED | FK→computadores |
| puerto_id | bigint UNSIGNED | FK→puertos |

### 4.8 Tabla: `dispositivos`

| Columna | Tipo | Nulo | Índice | Descripción |
|---|---|---|---|---|
| id | bigint UNSIGNED | NO | PK | — |
| bien_nacional | varchar(255) | NO | UNIQUE | Número de inventario |
| serial | varchar(255) | NO | UNIQUE | Serial del equipo |
| tipo_dispositivo_id | bigint UNSIGNED | NO | FK→tipo_dispositivos (RESTRICT) | Tipo |
| marca_id | bigint UNSIGNED | NO | FK→marcas (RESTRICT) | Fabricante |
| nombre | varchar(255) | NO | — | Modelo del dispositivo |
| ip | varchar(255) | SI | — | Dirección IP |
| estado | enum | NO | — | operativo/dañado/indeterminado/en_reparacion/baja |
| departamento_id | bigint UNSIGNED | NO | FK→departamentos (RESTRICT) | Ubicación |
| trabajador_id | bigint UNSIGNED | SI | FK→trabajadores (RESTRICT) | Responsable |
| computador_id | bigint UNSIGNED | SI | FK→computadores (RESTRICT) | Equipo host vinculado |
| notas | text | SI | — | Notas adicionales |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.9 Tabla: `insumos`

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
| unidad_medida | enum | NO | — | unidad/metros/litros/cajas/pares |
| medida_actual | decimal(8,2) | NO | — | Stock actual (default: 1.00) |
| medida_minima | decimal(8,2) | NO | — | Stock mínimo (default: 1.00) |
| reutilizable | tinyint(1) | NO | — | Requiere devolución (default: 0) |
| instalable_en_equipo | tinyint(1) | NO | — | Se instala en equipo (default: 0) |
| estado_fisico | enum | NO | — | operativo/danado/indeterminado/en_reparacion/baja |
| activo | tinyint(1) | NO | — | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.10 Tabla: `software`

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| nombre_programa | varchar(100) | NO | Nombre del software |
| arquitectura_programa | enum | SI | 32bits/64bits/Universal |
| tipo_licencia | enum | NO | Libre/Privativo |
| serial | varchar(50) | SI | Clave de activación |
| descripcion_programa | varchar(250) | SI | Descripción |
| activo | tinyint(1) | NO | Estado (default: 1) |
| created_by | bigint UNSIGNED | SI | FK→users (Auditoría) |
| updated_by | bigint UNSIGNED | SI | FK→users (Auditoría) |
| created_at | timestamp | SI | — |
| updated_at | timestamp | SI | — |
| deleted_at | timestamp | SI | SoftDelete |

### 4.11 Tabla: `incidencias`

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
| nota_resolucion | varchar(500) | SI | — | Diagnóstico/resolución |
| amerita_movimiento | tinyint(1) | NO | — | Requiere movimiento de inventario |
| solventado | tinyint(1) | NO | — | Fue resuelto (default: 0) |
| cerrado | tinyint(1) | NO | — | Caso cerrado definitivamente |
| created_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| updated_by | bigint UNSIGNED | SI | FK→users | Auditoría |
| created_at | timestamp | SI | — | — |
| updated_at | timestamp | SI | — | — |
| deleted_at | timestamp | SI | — | SoftDelete |

### 4.12 Tabla: `movimientos_computador`

| Columna | Tipo | Nulo | Descripción |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK |
| computador_id | bigint UNSIGNED | NO | FK→computadores (RESTRICT) |
| tipo_operacion | enum | NO | cambio_departamento / reasignacion_trabajador / cambio_estado / actualizacion_datos / baja / toggle_activo |
| payload_anterior | json | SI | Snapshot del estado ANTES del cambio |
| payload_nuevo | json | NO | Datos propuestos |
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

### 4.13 Tabla: `solicitudes_perfil`

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

### 4.14 Catálogos y Tablas de Soporte

| Tabla | Columnas principales | Uso |
|---|---|---|
| `marcas` | nombre, activo | Fabricantes de equipos |
| `tipo_dispositivos` | nombre, activo | Clasificación de periféricos |
| `sistemas_operativos` | nombre, activo | SO para computadores |
| `puertos` | nombre, activo | Tipos de puertos |
| `procesadores` | marca_id, modelo, socket, activo | CPUs para computadores |
| `gpus` | marca_id, modelo, activo | GPUs para computadores |
| `gpu_puerto` (pivot) | gpu_id, puerto_id | Puertos de GPUs |
| `categoria_insumos` | nombre, activo | Categorías de insumos |
| `problemas` | nombre, activo | Tipos de problemas en incidencias |
| `configuraciones` | clave, valor | Parámetros del sistema |
| `especialidades_tecnicas` | nombre, descripcion | Especialidades de técnicos |
| `dispositivo_puerto` (pivot) | dispositivo_id, puerto_id | Puertos de dispositivos |

### 4.15 Tablas Spatie (Permisos y Auditoría)

| Tabla | Descripción |
|---|---|
| `permissions` | Permisos individuales del sistema |
| `roles` | Roles de usuario |
| `model_has_permissions` | Permisos directos sobre modelos |
| `model_has_roles` | Roles asignados a usuarios |
| `role_has_permissions` | Permisos asignados a roles |
| `activity_log` | Log de auditoría completo |
