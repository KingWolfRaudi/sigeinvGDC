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

### 3.2. Gestión de Movimientos y Ciclo de Vida
- **Borradores e Historial:** Rastreo total de cambios de custodia (`Movimientos`).
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
- **Auditoría:** Incluir siempre columnas: `Creado Por`, `Modificado Por`, `Fecha Registro`, `Última Modificación`.
- **ID Técnica:** La columna ID se mantiene en Excel por utilidad administrativa.

### 5.2. Reportes PDF (Fichas Técnicas)
- **Privacidad:** Prohibido el uso de IDs de sistema. Usar Bien Nacional o Folio como referencia.
- **Ubicación de Acciones:** El botón de exportación PDF debe situarse en las acciones de cada registro individual y no en la cabecera del módulo para evitar confusión con reportes masivos.

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
- **Scroll Limitado**: `modal-body` con `max-height: 65vh; overflow-y: auto;`.
- **Limpieza de Backdrops**: Script manual obligatorio tras cierres de Livewire para evitar bloqueos de UI.
- **Alertas**: Deben estar contenidas dentro del `modal-body`.

---

## 7. Mantenimiento del Sistema
- **Storage Link:** Necesario para gestión de avatares.
- **Seeders:** Seguir el orden `Roles` -> `Configuraciones` -> `Datos Maestros`.

---
*Este instructivo se actualiza a la V5.0 para reflejar la estandarización Premium de SIGEINV.*
