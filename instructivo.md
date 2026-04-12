# Instructivo Técnico y Operacional: SigeinvGDC (V4.1)

Este documento constituye la fuente de verdad absoluta para el desarrollo y mantenimiento del **Sistema de Gestión de Inventario Tecnológico (SigeinvGDC)**. Define las reglas arquitectónicas, los flujos de negocio y los estándares técnicos que deben seguirse rigurosamente.

---

## 1. Stack Tecnológico y Arquitectura Core
El sistema está construido sobre una arquitectura moderna y escalable:
- **Lenguaje:** PHP 8.3+
- **Framework:** Laravel 10/12 (Estructura de directorios organizada por módulos).
- **Frontend:** Livewire 3 (Interacción en tiempo real sin recarga) + Bootstrap 5.
- **Iconografía:** Bootstrap Icons.
- **Base de Datos:** MariaDB (Relacional estricta con integridad referencial avanzada).

---

## 2. Reglas de Oro del Desarrollo (Inviolables)

### 2.1. Gestión de Datos y Modelos
- **SoftDeletes Obligatorio:** NUNCA se realiza un borrado físico (`Hard Delete`). Todas las tablas deben usar `$table->softDeletes()` y los modelos el trait `use SoftDeletes;`. Esto incluye a la tabla nativa `users`.
- **Casteos Booleanos:** Cada modelo con columna `activo` debe incluir `protected $casts = ['activo' => 'boolean'];`.
- **Integridad Referencial:**
    - Relaciones maestras (Marcas, Tipos, etc.): `onDelete('restrict')` para evitar orfandad de datos.
    - Tablas pivote (Muchos a Muchos): `cascadeOnDelete()`.
- **Campos Únicos Opcionales:** Deben definirse como `$table->string('campo')->nullable()->unique();` para permitir valores nulos sin colisiones de unicidad.

### 2.2. Estándares Livewire 3
- **Data Scoping:** Separar el permiso `ver-estado-modulo` (ver registros inactivos y el filtro) del permiso `cambiar-estatus-modulo` (ejecutar el switch). Por defecto, los usuarios sin permiso de estado solo ven `activo = true`.
- **Deep Search:** Las búsquedas en el `render()` deben usar grupos de condiciones `where(function($q) { ... })` para no romper los alcances globales (scoping).
- **Toasts unificados:** Toda respuesta de éxito o error debe enviarse vía `$this->dispatch('mostrar-toast', mensaje: '...', tipo: 'success|danger|warning|info')`.

---

## 3. Módulos y Lógica de Negocio Detallada

### 3.1. Inventario Tecnológico
El inventario se divide en tres grandes categorías con lógica diferenciada:
- **Computadores:** Módulo complejo que gestiona hardware dinámico (RAMs y Discos) a través de modelos relacionados (`ComputadorRam`, `ComputadorDisco`).
    - *Accesors Inteligentes:* El modelo calcula automáticamente el total de RAM y Almacenamiento limpiando sufijos como "GB" para operaciones matemáticas.
- **Dispositivos:** Equipos periféricos o de red (Routers, Switches, Impresoras).
- **Insumos/Herramientas:** Gestión de consumibles con control de stock y categorías.
    - *Lógica de Medida:* El sistema valida el stock según la unidad. Unidades como "Metros" o "Litros" permiten decimales (Double), mientras que "Piezas", "Unidades" o "Cajas" se fuerzan estrictamente a **Enteros** en backend y frontend.

### 3.2. Gestión de Movimientos (Ciclo de Vida)
El sistema rastrea cada cambio de custodia de un activo mediante un flujo estandarizado:
- **Tipos de Movimiento:** Asignación, Préstamo, Devolución, Reparación, Baja, Actualización.
- **Generador Estándar (Flujo Multietapa):** Implementado en los paneles de cada segmento para centralizar la creación.
    - **Etapa 1: Filtrado y Selección:** Buscador reactivo 2x2 (Bien Nacional, Serial, Departamento, Trabajador) con tabla de pre-selección.
    - **Etapa 2: Configuración de Cambios (Diff Engine):**
        - El sistema compara el estado actual contra el propuesto en el formulario (usando `Partial _form_fields`).
        - Solo se almacenan en la columna `cambios` (JSON) los atributos que efectivamente fueron modificados.
    - **Etapa 3: Justificación y Borrador:** Todo movimiento se guarda inicialmente como un **Borrador**, permitiendo correcciones antes de ser enviado a revisión.
- **Trazabilidad:** Cada activo mantiene un historial (`HasMany`) de todos sus movimientos pasados.

### 3.3. Panel de Soporte (Incidencias)
Sistema de ticketera interno para reporte de fallas.
- **Catálogo de Problemas:** Definido por el administrador para estandarizar reportes.
- **Roles Técnicos:** El sistema permite configurar qué roles (ej. `personal-ti`) actúan como "Agentes de Soporte".
- **Cierre Irreversible:** Opción de configuración para evitar la reapertura de casos finalizados.

---

## 4. Perfil de Usuario y Seguridad

### 4.1. Inmutabilidad del SuperAdmin
- El usuario con rol `super-admin` (o ID 1) es **INMUTABLE**.
- No puede ser modificado por otros usuarios ni por sí mismo desde el panel de perfil.
- El sistema bloquea en backend y oculta en frontend cualquier control de edición para esta cuenta.

### 4.2. Workflow de Solicitudes de Cambio
Los usuarios estándar no pueden cambiar su información sensible directamente. Deben enviar una solicitud:
- **Campos Sujetos a Aprobación:** Nombre, Username, Email, Password.
- **Regla de los 180 Días:** No se puede solicitar un cambio del mismo tipo si existe una solicitud aprobada hace menos de 180 días. Esta lógica reside en `SolicitudPerfil::canRequest()`.
- **Gestión de Avatares:** Las fotos se almacenan en `storage/app/public/avatars` y se renombran usando el slug del nombre del usuario para consistencia.

---

## 5. Auditoría del Sistema y Reportes

### 5.1. Centro de Auditoría (Activity Logs)
- **Activación Masiva:** Implementado vía `spatie/laravel-activitylog` en todos los modelos críticos (`User`, `Computador`, `Dispositivo`, `Insumo`, `Incidencia`, `SolicitudPerfil`).
- **Trazabilidad Forense:** El sistema captura automáticamente el estado **Anterior** y el **Nuevo** de cada atributo modificado.
- **Panel Administrativo:** Ubicado en `/admin/auditoria`, permite visualizar quién realizó cada acción, en qué fecha y ver el detalle comparativo de los campos.

### 5.2. Módulo de Reportes e Indicadores
- **Hojas de Vida (PDF):** Generación de fichas técnicas individuales para equipos, resumiendo especificaciones y últimos movimientos.
- **Actas de Entrega:** Documentos legales generables en PDF para la firma de custodia por parte de los trabajadores.
- **Exportación de Datos:** Soporte integrado para `PDF` (`dompdf`) y `Excel` (`excel`). El acceso a estas herramientas está blindado por los permisos `reportes-pdf` y `reportes-excel` respectivamente.
- **Dashboard Visual:** El panel de inicio incluye KPIs en tiempo real y gráficos de barras (`Chart.js`) sobre la salud física del inventario.

---

## 6. Configuración Centralizada
Se implementó un **Panel de Configuración General** unificado que reemplaza ajustes dispersos:
- **Grupo Incidencias:** Control de roles técnicos y reglas de activos obligatorios.
- **Grupo Perfil:** Toggles para habilitar/deshabilitar qué campos son editables/solicitables globalmente.
- **Tabla `configuracions`:** Almacenamiento tipo Clave-Valor para máxima flexibilidad.

---

## 7. Guía de Interfaz (Aesthetics)
- **Glassmorphism:** Uso de opacidades y desenfoques (backdrop-filter) en modales y tarjetas.
- **Bootstrap Custom:** Se priorizan paletas de colores armónicas (Azure, Indigo, Teal) sobre los colores primarios base.
- **Limpieza de Modales:** Livewire requiere un script de limpieza manual para eliminar el `.modal-backdrop` de Bootstrap tras cierres asíncronos.
- **Scroll Fijo en Modales (Obligatorio):** Todo `<div class="modal-body">` debe incorporar estrictamente el CSS en línea `style="max-height: 65vh; overflow-y: auto;"`. Esto garantiza que los botones de acción (`modal-footer`) nunca sean desplazados fuera de la pantalla, evitando el antiestético doble scroll del navegador.
- **Contención Estricta de Alertas:** Cualquier bloque de advertencia dinámico, alerta de error o cuadro de **"Justificación del Cambio"** debe ir SIEMPRE DENTRO del `modal-body` afectado por el patrón de 65vh. Está estrictamente prohibido ubicar elementos expansivos entre el `modal-body` y el `modal-footer`.
- **Estándar de Modales de Detalle:** Los modales de "Vista Rápida" deben seguir un layout de 3 columnas (Identificación, Especificaciones, Notas) con etiquetas estandarizadas (ej. *"Ubicación"* para departamentos). El footer solo debe contener el enlace a Asociaciones y el botón de Cerrar.
- **Estándar de Dashboard de Asociaciones:** La información debe segregarse en pestañas dinámicas protegidas por permisos:
    - *Pestaña 1 (Humano/Espacial):* Trabajador y Departamento (Responsable y Ubicación).
    - *Pestaña 2 (Hardware/Técnico):* Equipos vinculados (Computadores o Dispositivos).
    - *Pestañas Siguientes:* Insumos e Incidencias.
- **Estándar de Botones en Paneles:** Para mantener la consistencia en todos los módulos de movimientos y gestión:
    - **Orden e Integración:** Los botones de exportación (Excel/PDF) deben ubicarse a la izquierda del botón de acción principal. Se apoyarán en un filtro global que maneje un estado "todos" (para visualizar un panorama completo en reportes).
    - **Estilo:** Excel (`btn-outline-success border-2 fw-bold`), PDF (`btn-outline-danger shadow-sm`), Acción Principal (`btn-primary fw-bold`).
    - **Seguridad:** Los botones de acción principal en paneles de movimientos deben estar estrictamente encapsulados en directivas `@can` con el sufijo `-crear`. Adicionalmente, se debe separar rígidamente la lógica de "Visualización u Operación" (`ver-incidencias`) de la lógica "Administrativa Central" (`admin-incidencias`) para evitar que perfiles híbridos vean configuraciones a las que no conciernen.

---

## 8. Mantenimiento del Sistema
- **Storage Link:** Es obligatorio ejecutar `php artisan storage:link` para visualizar avatares.
- **Seeders de Inicialización:** 
    1. `RolesAndPermissionsSeeder` (Define el espectro de seguridad).
    2. `IncidenciasSeeder` (Carga configuraciones iniciales).
    3. `DatabaseSeeder` (Orquesta la construcción total).


---
*Este instructivo debe ser actualizado ante cualquier cambio en las reglas de negocio o arquitectura del sistema.*
