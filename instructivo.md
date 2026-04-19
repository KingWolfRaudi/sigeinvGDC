# Instructivo Técnico y Operacional: SigeinvGDC (V5.0)

Este documento constituye la fuente de verdad absoluta para el desarrollo y mantenimiento del **Sistema de Gestión de Inventario Tecnológico (SigeinvGDC)**. Define las reglas arquitectónicas, los flujos de negocio y los estándares técnicos que deben seguirse rigurosamente.

---

## 1. Stack Tecnológico y Arquitectura Core
El sistema está construido sobre una arquitectura moderna y escalable:
- **Lenguaje:** PHP 8.3+
- **Framework:** Laravel 10/12.
- **Frontend:** Livewire 3 (Interacción en tiempo real) + Bootstrap 5.
- **Iconografía:** Bootstrap Icons (Estandarizados en Header y Acciones).
- **Base de Datos:** MariaDB (Relacional con integridad referencial avanzada).

---

## 2. Reglas de Oro del Desarrollo (Inviolables)

### 2.1. Gestión de Datos y Modelos
- **SoftDeletes:** Obligatorio para todas las tablas operativos y de seguridad (`users` inclusive).
- **Casteos Booleanos:** Obligatorio para columnas `activo` y flags de configuración.
- **Gestión de Auditoría (RecordSignature):** 
    - Todos los modelos operativos (Equipos, Insumos, Software, Usuarios) DEBEN usar el trait `RecordSignature`.
    - Este trait gestiona automáticamente `created_by` y `updated_by`.
    - Las relaciones de auditoría deben llamarse `creator()` y `updater()`.
- **Visibilidad de IDs:** Está **estrictamente prohibido** mostrar el ID de base de datos en la interfaz de usuario (Tablas, Listados, Modales) o reportes PDF. Solo se permite su uso en exportaciones Excel para fines técnicos.

### 2.2. Estándares Livewire 3
- **Deep Search:** Búsqueda profunda en múltiples tablas usando `whereHas` y grupos de condiciones.
- **Toasts:** Uso exclusivo de `$this->dispatch('mostrar-toast', ...)` para notificaciones fluidas.
- **Filtros de Estado:** Siempre incluir un modo "todos" y proteger la visibilidad por permisos (`ver-estado-modulo`).

---

## 3. Módulos y Lógica de Negocio

### 3.1. Inventario Tecnológico
- **Computadores:** Gestión de hardware dinámico (RAM/Discos). Los modelos deben autonivelar unidades (GB) para reportes.
- **Dispositivos:** Periféricos con trazabilidad de red (IP) y asociación a Computadores.
- **Insumos/Herramientas:** 
    - Control de stock con medidas estrictas (Enteros para unidades, Double para medidas físicas).
    - **Nivel de Detalle Completo:** Los registros de insumos deben rastrear su ubicación física (Departamento) y su asignatario (Trabajador/Equipo).
- **Software:** Control de licencias y arquitecturas. Incluye trazabilidad de quién registró el programa.

### 3.2. Gestión de Incidencias (Workflow de Trazabilidad Total V2)
- **Interfaz DUAL:** El módulo de incidencias opera con dos enfoques separados e integrados:
    1. **Frontend Usuario (`CrearTicket`):** Interfaz simplificada para reportar fallas, atada automáticamente al Trabajador y Departamento. 
    2. **Backend Técnico (`Gestion`):** Gestión centralizada para el rol `resolutor-incidencia`.
- **Integración Operativa-Administrativa:** Si una incidencia requiere un cambio físico de activos, el sistema habilita el flag "¿Amerita Movimiento?". Al activarse, permite un salto inteligente hacia los paneles de movimientos, arrastrando el ID del activo y el Folio de la incidencia para asegurar la trazabilidad.
- **Asignación de Técnicos:** Basada estrictamente en el rol `resolutor-incidencia` y validación de especialidades técnicas. Se eliminaron configuraciones de roles manuales (vestigios) en favor del sistema de permisos de Spatie.
- **Visibilidad en Tabla:** La tabla de gestión debe mostrar siempre el **Tipo de Activo** y el **Nombre/BN del Activo** relacionado, junto con un indicador visual (badge) si el registro tiene un movimiento pendiente.

### 3.3. Gestión de Movimientos y Ciclo de Vida
- **Borradores e Historial:** Rastreo total de cambios de custodia (`Movimientos`).
- **Trazabilidad de Origen:** Todo movimiento generado desde una incidencia debe capturar obligatoriamente el `incidencia_id` en la base de datos.
- **Automatización de Justificación:** Al ser derivado de una incidencia, el sistema debe precargar el campo de justificación con: `"Vinculado a la incidencia #XXXXX"`.
- **Diff Engine:** Almacenamiento JSON solo de campos modificados para optimizar espacio y auditoría.

---

## 4. Auditoría y Seguridad
- **Inmutabilidad:** El rol `super-admin` es ineditable desde la interfaz.
- **Activity Logs:** Captura de estados "Antes" y "Después" vía Spatie.
- **Solicitudes de Perfil:** Workflow de aprobación de 180 días para cambios de datos sensibles.

---

## 5. Sistema de Reportes (Estándar Premium)

### 5.1. Exportaciones Excel
- **Campos Requeridos:** Deben incluir TODA la información del modelo, incluyendo asociaciones (ej. en Insumos mostrar el nombre del Departamento, no el ID).
- **Auditoría Cruzada:** Todo reporte de movimientos debe mapear explícitamente el **Folio de Incidencia** si este fue generado desde un caso técnico.
- **Auditoría Estándar:** Incluir siempre columnas: `Creado Por`, `Modificado Por`, `Fecha Registro`, `Última Modificación`.
- **ID Técnica:** La columna ID se mantiene en Excel por utilidad administrativa.

### 5.2. Reportes PDF (Fichas Técnicas y Trazabilidad)
- **Trazabilidad Cruzada:** 
    - Toda ficha de activo (Computador, Dispositivo o Insumo) DEBE incluir en su historial de movimientos una columna que referencie el **Folio de Incidencia** asociado.
    - Todo reporte de incidencia DEBE incluir una sección de **Resolución Administrativa** si esta generó un movimiento de inventario.
- **Privacidad:** Prohibido el uso de IDs de sistema (autoincrementales puros) en textos descriptivos. Usar Bien Nacional o el Folio Formateado como referencia.
- **Ubicación de Acciones:** El botón de exportación PDF debe situarse en las acciones de cada registro individual.

### 5.3. Seguridad en Exportaciones
- **Blindaje Frontend:** Todo botón de exportación, ya sea Excel (en cabeceras) o PDF (en tablas/modales), DEBE estar protegido por las directivas `@can('reportes-excel')` o `@can('reportes-pdf')` correspondientes.
---

## 6. Guía de Interfaz (Premium UI)

### 6.1. Patrón de Layout "Premium"
Todas las vistas principales deben seguir esta estructura:
1.  **Header Especial**: Bloque superior con icono descriptivo en caja sombreada (`bg-primary bg-opacity-10`), título destacado (`h2 fw-bold`) y descripción.
2.  **Card Flotante de Acciones**: Tarjeta con `rounded-4` y `shadow-sm` que agrupa búsqueda, filtros y botones de acción (Excel, Nuevo).
3.  **Contenedor de Tabla**: Tarjeta independiente con `card-body p-0` y `overflow-hidden`.

### 6.2. Estilos de Botones
- **Exportación Excel**: `btn-outline-success border-2 fw-bold`.
- **Exportación PDF**: `btn-outline-danger shadow-sm` (Ícono de PDF).
- **Acción Principal (Nuevo)**: `btn-primary fw-bold shadow-sm`.
- **Acciones de Tabla**: Botones pequeños (`btn-sm`) con iconos limpios.

### 6.3. Modales y Experiencia de Usuario
- **Dimensiones Estándar:** Los modales generadores de alta complejidad (Movimientos) deben usar la clase `.modal-xl` con un ancho máximo del `90%` vía CSS inline (`style="max-width: 90%;"`) para asegurar paridad visual entre módulos.
- **Scroll Limitado**: `modal-body` con `max-height: 65vh; overflow-y: auto;`.
- **Auto-Apertura**: El sistema permite la apertura programática de modales mediante eventos Livewire (`dispatch('abrir-modal')`) al detectar parámetros de redirección (`auto_open`).
- **Limpieza de Backdrops**: Script manual obligatorio tras cierres de Livewire para evitar bloqueos de UI.
- **Alertas y Contexto**: Deben estar contenidas dentro del `modal-body`. Si existe una incidencia vinculada, se debe mostrar una alerta persistente de contexto (`bg-primary bg-opacity-10`) en la cabecera del modal.

---

## 7. Mantenimiento del Sistema
- **Storage Link:** Necesario para gestión de avatares.
- **Seeders:** Seguir el orden `Roles` -> `Configuraciones` -> `Datos Maestros`.

---
*Este instructivo se actualiza a la V6.0 para reflejar la Trazabilidad Total SIGEINV-INCIDENCIAS.*
