# Informe de Auditoría: Módulo de Incidencias

## 1. Alcance y Archivos Involucrados

### Núcleo del Módulo
| Archivo | Tipo | Descripción |
|---|---|---|
| `app/Models/Incidencia.php` | Modelo | Modelo principal de la entidad |
| `app/Models/Problema.php` | Modelo | Catálogo de tipos de problema |
| `app/Livewire/Incidencias/Gestion.php` | Componente | Lógica de gestión (única vista) |
| `resources/views/livewire/incidencias/gestion.blade.php` | Vista | Interfaz completa del módulo |

### Soporte y Configuración
| Archivo | Rol |
|---|---|
| `app/Livewire/Admin/ConfiguracionGeneral.php` | Panel de admin con configuración de incidencias |
| `app/Exports/IncidenciasExport.php` | Exportación Excel |
| `app/Http/Controllers/ReporteController.php` | Controlador del Excel |
| `database/migrations/2026_04_03_100036_create_incidencias_table.php` | Migración |
| `database/seeders/IncidenciasSeeder.php` | Datos iniciales |
| `database/seeders/RolesAndPermissionsSeeder.php` | Permisos |
| `routes/web.php` | Rutas |

### Integración con otros módulos
| Archivo | Integración |
|---|---|
| `resources/views/livewire/asociaciones-dashboard.blade.php` | Pestaña de Incidencias en Asociaciones |
| `app/Livewire/Dashboard.php` | KPI de incidencias abiertas |

---

## 2. Estructura de Base de Datos

### Tabla `incidencias`
| Columna | Tipo | Notas |
|---|---|---|
| `id` | PK | Auto-incremental |
| `problema_id` | FK | → `problemas` (NOT NULL) |
| `departamento_id` | FK | → `departamentos` (NOT NULL) |
| `trabajador_id` | FK nullable | → `trabajadores` (Solicitante) |
| `user_id` | FK | → `users` (Técnico Resolutor, NOT NULL) |
| `modelo_id` | INT nullable | Polimórfico: ID del activo |
| `modelo_type` | STRING nullable | Polimórfico: clase del activo |
| `descripcion` | TEXT | Detalle de la falla |
| `notas` | TEXT nullable | Notas de resolución |
| `solventado` | BOOLEAN | Default: false |
| `cerrado` | BOOLEAN | Default: false |
| `created_by` | FK nullable | → `users` (Auditoría) |
| `updated_by` | FK nullable | → `users` (Auditoría) |
| `timestamps` + `deleted_at` | | Soft Deletes |

### Tabla `problemas`
| Columna | Tipo | Notas |
|---|---|---|
| `id`, `nombre`, `activo` | Base | Catálogo simple |
| `created_by`, `updated_by` | FK | Auditoría |
| `timestamps` + `deleted_at` | | Soft Deletes |

---

## 3. Funcionalidades Actuales

### ✅ Implementadas y Funcionales
1.  **Listado con Filtros**: Búsqueda por descripción o nombre del trabajador, además de filtros por departamento y estado (Abierto / Solventado / Cerrado).
2.  **Creación de Incidencia**: Formulario modal en 4 secciones:
    - *Ubicación y Solicitante* (Departamento + Trabajador en cascada)
    - *Activo relacionado* (Polimórfico: Computador, Dispositivo, Insumo)
    - *Información del Caso* (Tipo de Problema + Técnico Asignado + Descripción)
    - *Control y Seguimiento* (Toggle solventado + toggle cerrar)
3.  **Edición**: Carga el formulario con los datos existentes. Protege incidencias cerradas (solo `admin-incidencias` puede editarlas).
4.  **Cierre Controlado**: El botón de cierre está deshabilitado si la incidencia no está marcada como solventada primero.
5.  **Exportación Excel**: Dropdown con "Vista Actual" y "Todo el Historial", protegido por `@can('reportes-excel')`.
6.  **Modo Embebido**: El componente acepta `presetFiltro` y `ocultarTitulos` para mostrarse dentro del dashboard de Asociaciones filtrado automáticamente por trabajador o activo.
7.  **Configuración de Sistema**:
    - Roles que actúan como Técnicos Resolutores (configurable)
    - Toggle: Activo relacionado obligatorio
    - Toggle: Cierre irreversible
8.  **Catálogo de Problemas**: CRUD completo en el panel de Configuración.
9.  **Ordenamiento de tabla**: Por fecha de creación.
10. **Dashboard KPI**: Conteo de incidencias abiertas en el panel de inicio.

---

## 4. Permisos Definidos
| Permiso | Uso esperado |
|---|---|
| `ver-incidencias` | Acceso a la vista de gestión |
| `crear-incidencias` | Botón "Nueva Incidencia" |
| `editar-incidencias` | *(Definido en seeder, sin uso explícito en código)* |
| `cerrar-incidencias` | *(Definido en seeder, sin uso explícito en código)* |
| `ver-estado-incidencias` | *(Definido en seeder, sin uso explícito en código)* |
| `eliminar-incidencias` | *(Definido en seeder, sin uso explícito en código)* |
| `admin-incidencias` | Editar incidencias cerradas y gestionar configuración |

---

## 5. ⚠️ Errores, Inconsistencias y Deudas Técnicas Detectadas

### 🔴 Críticos / Bugs Confirmados

1.  **Clase fantasma importada en `routes/web.php`** (Línea 8):
    ```php
    use App\Livewire\Admin\IncidenciasConfig;
    ```
    Esta clase **no existe** en el filesystem (`app/Livewire/Admin/IncidenciasConfig.php`). El import es un vestigio de una refactorización anterior. Aunque no rompe el sistema actualmente (no hay Route que la use), puede causar errores si se ejecuta `php artisan route:list --verbose` o en futuros despliegues.

2.  **Falta validación de `departamento_id` al filtrar activos de Insumos** (Gestion.php L.112):
    ```php
    $query = Insumo::query(); // Ajustar si Insumo tuviera depto_id
    ```
    El comentario indica que esto **no está terminado**. Actualmente si se selecciona "Insumo" como tipo de activo, el sistema devuelve **todos** los insumos del sistema, ignorando el departamento seleccionado. El modelo `Insumo` sí tiene `departamento_id`, por lo que el filtro debería aplicarse.

3.  **El permiso `crear-incidencias` usa un comentario informal en la vista** (gestion.blade.php L.56):
    ```blade
    @can('crear-incidencias') {{-- Assuming this permission exists based on context --}}
    ```
    El comentario `"Assuming this permission exists"` indica incertidumbre durante el desarrollo. El permiso sí existe en el seeder, pero la duda no fue aclarada formalmente.

### 🟡 Inconsistencias / Deudas de Diseño

4.  **Permisos declarados pero sin implementación en código**:
    Los permisos `editar-incidencias`, `cerrar-incidencias`, `ver-estado-incidencias` y `eliminar-incidencias` están definidos en `RolesAndPermissionsSeeder` pero **ninguno se usa con `@can` en la vista ni con `abort_if(Gate::denies())` en el componente**. La lógica de quién puede editar depende completamente de `admin-incidencias`, dejando los demás permisos como decoración sin efecto real.

5.  **No hay funcionalidad de Eliminar (Soft Delete)**:
    Aunque el modelo usa `SoftDeletes` y existe el permiso `eliminar-incidencias`, la vista **no tiene ningún botón de eliminar** y el componente tampoco tiene un método `eliminar()`.

6.  **Sin paginación de filtros avanzados (Filtro de Técnico)**:
    El listado no permite filtrar por técnico asignado, siendo este uno de los campos más importantes para el seguimiento del flujo de trabajo.

7.  **Ruta del Excel mal nombrada** (`routes/web.php` L.55):
    La ruta está registrada como `reportes.incidencias.excel`, pero en la vista (`gestion.blade.php` L.52) se llama con `route('reportes.incidencias.excel', ...)`. Esto **sí coincide**, pero la ruta no está en el prefijo `reportes.` que agrupa a las demás. Está dentro del grupo `incidencias.`:
    ```php
    // Línea 55 - está dentro del grupo reportes. (correcto)
    Route::get('/incidencias/excel', ...)->name('incidencias.excel');
    ```

8.  **El campo `prioridad` en el controlador no existe en la BD** (ReporteController.php L.168):
    ```php
    $filters = $request->only(['search', 'estado', 'prioridad']);
    ```
    Se extrae un filtro `prioridad` del request, pero la tabla `incidencias` **no tiene columna `prioridad`**. El `IncidenciasExport` tampoco implementa ese filtro. Es código muerto.

9.  **El modelo `Incidencia` no aplica `LogsActivity` por relaciones**: Actualmente solo logea los atributos simples de la tabla. Los cambios de estado (solventado/cerrado) quedan registrados en el log de Spatie, pero **no hay un sistema de comentarios/historial propio** del ticket (a diferencia de lo que exporta `IncidenciasExport`, que intenta leer `$item->activities`).

10. **Relación `tecnico` puede fallar si el registro es nulo**: En la vista (L.100):
    ```blade
    <td>{{ $inc->tecnico->name }}</td>
    ```
    Sin el operador `??`, si por alguna razón `user_id` es null o el usuario fue eliminado permanentemente, esto lanzaría un error `Attempt to read property "name" on null`.

---

## 6. Funcionalidades Ausentes (No Implementadas)

- ❌ Sin vista de "Detalle" individual de una incidencia (solo modal de edición).
- ❌ Sin sistema de comentarios/historial de seguimiento propio (solo actividad de Spatie).
- ❌ Sin filtro por Técnico Asignado en el listado.
- ❌ Sin eliminar registros (Botón de Soft Delete).
- ❌ Sin notificaciones al técnico al ser asignado a una incidencia.
- ❌ Sin campo de Prioridad en la tabla (pero sí esperado en el controlador).
- ❌ Sin reporte PDF individual de incidencia (solo exportación Excel masiva).
- ❌ El módulo no aparece en el Reporte Masivo (`MassiveReportExport`).
