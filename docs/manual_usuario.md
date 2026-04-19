# Manual de Usuario — SIGEINV
### Sistema de Gestión de Inventario Tecnológico y Mesa de Ayuda
**Versión 1.0 | Área de Tecnología de la Información**

---

## Tabla de Contenidos

1. [Introducción y Acceso al Sistema](#1-introducción-y-acceso-al-sistema)
2. [Roles y Permisos](#2-roles-y-permisos)
3. [Panel de Control (Dashboard)](#3-panel-de-control-dashboard)
4. [Inventario de Computadores](#4-inventario-de-computadores)
5. [Inventario de Dispositivos](#5-inventario-de-dispositivos)
6. [Almacén de Insumos y Herramientas](#6-almacén-de-insumos-y-herramientas)
7. [Catálogo de Software](#7-catálogo-de-software)
8. [Panel de Soporte e Incidencias](#8-panel-de-soporte-e-incidencias)
9. [Panel de Movimientos](#9-panel-de-movimientos)
10. [Catálogos del Sistema](#10-catálogos-del-sistema)
11. [Asignaciones (Trabajadores y Departamentos)](#11-asignaciones-trabajadores-y-departamentos)
12. [Administración del Sistema](#12-administración-del-sistema)
13. [Mi Perfil](#13-mi-perfil)
14. [Reportes y Exportaciones](#14-reportes-y-exportaciones)
15. [Tema Oscuro y Personalización Visual](#15-tema-oscuro-y-personalización-visual)
16. [Glosario de Términos](#16-glosario-de-términos)

---

## 1. Introducción y Acceso al Sistema

**SIGEINV** es el Sistema de Gestión de Inventario Tecnológico y Mesa de Ayuda de la organización. Permite registrar, controlar y dar seguimiento a todos los activos tecnológicos (computadores, dispositivos, insumos y software), así como gestionar las solicitudes de soporte técnico (incidencias) con trazabilidad completa.

### 1.1 Cómo Iniciar Sesión

1. Abre tu navegador (Chrome, Firefox, Edge) y dirígete a la dirección del sistema.
2. En la pantalla de inicio verás el formulario de autenticación:
   - **Usuario o Correo Electrónico:** Ingresa tu nombre de usuario o correo institucional.
   - **Contraseña:** Ingresa tu contraseña. Haz clic en el ícono 👁 para visualizarla.
   - **Mantener sesión iniciada:** Activa esta opción si deseas que el sistema recuerde tu sesión.
3. Haz clic en **"Entrar al Sistema"**.

> **Nota:** Si el botón dice "Verificando...", el sistema está procesando tu solicitud. Espera unos instantes.

### 1.2 Cómo Cerrar Sesión

1. En la barra lateral izquierda, haz clic en tu **nombre de usuario** (parte inferior del menú).
2. En el menú desplegable, selecciona **"Cerrar Sesión"**.

---

## 2. Roles y Permisos

SIGEINV utiliza un sistema de roles y permisos que determina qué secciones y acciones están disponibles para cada usuario.

| Rol | Descripción |
|-----|-------------|
| **Super Administrador** | Acceso total sin restricciones. Único rol ineditable desde la interfaz. |
| **Administrador** | Gestión completa de inventario, incidencias, movimientos y usuarios. |
| **Técnico Resolutor** | Gestiona y atiende incidencias asignadas. Acceso a movimientos relacionados. |
| **Inventariador** | Registra y actualiza activos del inventario tecnológico. |
| **Usuario Estándar** | Puede crear tickets de soporte y consultar su perfil. |

> Los permisos específicos se configuran desde **Administración > Roles y Permisos**.

---

## 3. Panel de Control (Dashboard)

Pantalla principal al iniciar sesión. Proporciona una vista consolidada del estado operativo en tiempo real.

### 3.1 Métricas Operativas

| Tarjeta | Descripción |
|---------|-------------|
| 🔴 **Tickets sin Asignar** | Incidencias sin técnico asignado. Requieren atención inmediata. |
| 🟡 **En Curso** | Casos activos con técnico trabajando en la solución. |
| 🟠 **Movimientos Solicitados** | Solicitudes de movimiento de activos pendientes de aprobación. |
| ⚠️ **Insumos Críticos** | Insumos con stock igual o inferior al mínimo establecido. |

### 3.2 Panel de Composición de Hardware

Dos gráficos interactivos basados en datos reales del inventario:
- **Distribución de RAM:** Clasifica equipos por capacidad (4GB o menos, 8GB, 16GB, más de 16GB).
- **Tecnología de Almacenamiento:** Compara equipos con SSD/NVME frente a HDD mecánicos.

Indicadores laterales muestran la **capacidad total de RAM** y **almacenamiento total** gestionado.

### 3.3 Mesa de Ayuda (Sección Inferior)

Dos paneles en paralelo:

**Panel Izquierdo — Atención Rápida:**
- **Técnico resolutor:** Muestra tus casos asignados pendientes.
- **Administrador:** Muestra los últimos tickets abiertos del sistema.

**Panel Derecho — Historial de Resoluciones:**
- **Técnico resolutor:** Tus casos resueltos recientemente.
- **Administrador:** Historial global de incidencias resueltas.

El botón **"Ir al Panel de Gestión"** te lleva directamente al módulo completo de incidencias.

---

## 4. Inventario de Computadores

Gestión de todos los equipos de computación: desktops, laptops y servidores.

### 4.1 Información en la Tabla Principal

- **Identificación:** Bien Nacional y Serial del equipo.
- **Tipo/Equipo:** Tipo de computador y nombre asignado.
- **Marca/Hardware:** Marca, RAM total y almacenamiento total.
- **Red:** Dirección IP y MAC.
- **Ubicación:** Departamento asignado.
- **Condición:** Estado físico (Operativo / Dañado / En Revisión).
- **Estado:** Activo o Inactivo *(visible según permisos)*.

**Indicadores especiales:**
- 🟡 **"En revisión":** Equipo con movimiento pendiente de aprobación.
- 🔵 **"Borrador":** Tienes un borrador de cambio no enviado para ese equipo.

### 4.2 Barra de Búsqueda y Filtros

- **Buscador:** Busca por Bien Nacional, Serial o dirección IP.
- **Filtro de Estado:** Todos / Solo Activos / Solo Inactivos *(según permisos)*.

### 4.3 Registrar un Nuevo Computador

1. Haz clic en **"+ Nuevo"**.
2. Completa el formulario:
   - Datos de identificación (tipo, nombre, Bien Nacional, serial).
   - Asignación (departamento y trabajador responsable).
   - Hardware (sistema operativo, procesador, GPU, módulos de RAM y discos).
   - Conectividad (IP, MAC, tipo de conexión, puertos).
   - Estado físico y observaciones.
3. Haz clic en **"Guardar Computador"**.

### 4.4 Ver Detalles de un Equipo

Clic en el ícono **👁 (Ver)**. Se abre una ficha completa con toda la información técnica. Desde aquí puedes acceder al botón **"Asociaciones"** para ver los dispositivos vinculados.

### 4.5 Editar un Equipo

1. Clic en el ícono **✏️ (Editar)**.
2. Modifica los campos necesarios.
3. **Obligatorio:** Ingresa una **Justificación del Cambio** (mínimo 10 caracteres).
4. Clic en **"Guardar Computador"**.

> El cambio queda en estado **"Borrador"** hasta que un administrador lo apruebe desde el Panel de Movimientos.

### 4.6 Cambiar Estado Activo/Inactivo

Clic en el botón de palanca (🟢/⚫) en las acciones. Esto genera un movimiento de "Cambio de Estatus" que requiere aprobación.

### 4.7 Descargar Ficha PDF

Clic en el ícono **📄 (PDF)** en las acciones del registro para descargar la ficha técnica *(según permisos)*.

---

## 5. Inventario de Dispositivos

Gestión de periféricos y equipos de red: impresoras, routers, switches, monitores, proyectores, etc.

Funciona de manera similar al módulo de Computadores con estas particularidades:
- **Tipo de Dispositivo:** Se selecciona de un catálogo predefinido.
- **Asociación a Computador:** Un dispositivo puede vincularse a un equipo host.
- No lleva módulos de hardware interno (RAM/discos).

Las acciones disponibles son las mismas: Ver, Editar, Cambiar Estado, Descargar PDF y Exportar Excel.

---

## 6. Almacén de Insumos y Herramientas

Control de stock de consumibles y herramientas: cartuchos, papel, cables, repuestos, herramientas, etc.

### 6.1 Campos Principales

| Campo | Descripción |
|-------|-------------|
| **Nombre** | Nombre descriptivo del insumo. |
| **Categoría** | Grupo al que pertenece (Tóner, Herramienta, Cable, etc.). |
| **Unidad de Medida** | Unidades, metros, litros, etc. |
| **Medida Actual** | Stock disponible actualmente. |
| **Medida Mínima** | Nivel de alerta de stock crítico. |
| **Reutilizable** | Si debe ser devuelto tras su uso (ej. herramientas). |
| **Ubicación** | Departamento y responsable asignado. |

### 6.2 Alertas de Stock Crítico

Cuando el stock actual cae al nivel mínimo, el sistema:
1. Destaca el registro en el inventario.
2. Aumenta el contador de **"Insumos Críticos"** en el Dashboard.

---

## 7. Catálogo de Software

Inventario de licencias de software instaladas en los equipos de la organización.

### 7.1 Información Registrada

- Nombre y versión del software.
- Tipo de licencia: **Libre** o **Privativo**.
- Arquitectura: 32-bit o 64-bit.
- Serial/Clave de activación (mostrada en formato protegido).
- Equipo asignado donde está instalado.
- Registro de auditoría (quién registró y cuándo).

### 7.2 Filtrado

Puedes filtrar por texto libre (nombre) o por tipo de licencia.

---

## 8. Panel de Soporte e Incidencias

Sistema de dos niveles: usuarios reportan fallas, técnicos las atienden.

### 8.1 Reportar un Ticket (Vista del Usuario)

Accede desde **Panel de Soporte > Reportar**.

1. **Departamento:** Se asigna automáticamente si tu usuario está vinculado a un trabajador. Caso contrario, selecciónalo manualmente.
2. **Tipo de Activo Afectado:** Computador, Dispositivo u Otro.
3. **Activo Específico:** Equipo afectado del listado.
4. **Tipo de Problema:** Categoría del inconveniente.
5. **Descripción Detallada:** Explica la falla con el mayor detalle posible.
6. Clic en **"Enviar Reporte"**.

Recibirás confirmación con el número de folio único (`#00001`).

### 8.2 Gestión de Incidencias (Vista Técnico/Administrador)

Accede desde **Panel de Soporte > Gestión**.

**Información en la tabla:**
- Folio, Solicitante, Activo Afectado (tipo y BN/nombre), Categoría del Problema, Técnico Asignado, Estado.

**Acciones sobre una incidencia:**

| Acción | Descripción |
|--------|-------------|
| **Ver Detalle (👁)** | Abre el modal con toda la información del caso. |
| **Asignar Técnico** | Asigna un técnico resolutor al caso. |
| **Registrar Diagnóstico** | Documenta el análisis técnico realizado. |
| **Marcar Solventado** | Indica que el problema fue resuelto. |
| **Cerrar Incidencia** | Cierre definitivo *(puede ser irreversible según configuración)*. |
| **¿Amerita Movimiento?** | Activa el flag para crear un movimiento de inventario desde esta incidencia. |
| **Registrar Movimiento** | Aparece solo si el caso está guardado y tiene el flag activo. Redirige al panel de movimientos con datos precargados. |
| **Descargar PDF** | Genera la ficha técnica de la incidencia *(según permisos)*. |

> **Botón de Movimiento en Tabla:** Cuando una incidencia tiene el flag "¿Amerita Movimiento?" activo, aparece un botón directo en la fila de la tabla para agilizar el proceso sin abrir el modal.

**Filtros disponibles:** Texto libre, Técnico Asignado, Estado y Departamento.

---

## 9. Panel de Movimientos

Registro formal de todos los cambios de custodia, reasignación y actualización de activos. Garantizan trazabilidad completa y requieren aprobación.

### 9.1 Módulos Disponibles

- Movimientos de Computadores
- Movimientos de Dispositivos
- Movimientos de Insumos
- Solicitudes de Perfil

### 9.2 Flujo de Aprobación

```
Borrador → Solicitado → Aprobado / Rechazado
```

| Estado | Descripción |
|--------|-------------|
| **Borrador** | Creado pero no enviado. Solo visible para su creador. |
| **Solicitado** | Enviado para aprobación. Visible para administradores. |
| **Aprobado** | Validado y aplicado al inventario. |
| **Rechazado** | Denegado con justificación. |

### 9.3 Registrar un Movimiento

1. Clic en **"+ Nuevo Movimiento"**.
2. Selecciona el **Tipo de Operación:**
   - Actualización de Datos
   - Cambio de Departamento
   - Reasignación de Trabajador
   - Cambio de Estado Físico
   - Cambio de Estatus (Activo/Inactivo)
3. Selecciona el activo afectado.
4. Completa los nuevos valores.
5. Ingresa la **Justificación** obligatoria.
6. Clic en **"Guardar como Borrador"** o **"Enviar para Aprobación"**.

### 9.4 Movimientos desde Incidencias

Al originarse desde una incidencia, el sistema:
- Pre-selecciona el activo vinculado.
- Precarga la justificación: *"Vinculado a la incidencia #XXXXX"*.
- Registra el Folio de Incidencia para trazabilidad cruzada.

### 9.5 Aprobar o Rechazar un Movimiento

*(Requiere permiso de aprobación)*

1. Localiza el movimiento "Solicitado" en la tabla.
2. Clic en **Ver Cambio (👁)**.
3. Revisa el comparativo (valores anteriores vs. propuestos).
4. Clic en **"Aprobar"** o **"Rechazar"**.

### 9.6 Solicitudes de Perfil

Gestiona peticiones de cambio de datos personales de trabajadores (nombre, cédula, correo). Pasan por flujo de aprobación con vigencia de **180 días**.

---

## 10. Catálogos del Sistema

Tablas de referencia que alimentan los formularios del inventario. Se acceden desde el menú **Catálogos**.

| Catálogo | Descripción |
|----------|-------------|
| **Marcas** | Fabricantes de equipos (Dell, HP, Lenovo, etc.). |
| **Tipos de Dispositivo** | Clasificaciones de periféricos. |
| **Sistemas Operativos** | SO para asignar a computadores. |
| **Puertos de Conexión** | USB, HDMI, RJ45, etc. |
| **Procesadores** | Modelos de CPU disponibles. |
| **GPUs** | Modelos de tarjetas gráficas. |

Todos permiten **crear, editar y activar/desactivar** registros. Los inactivos no aparecen en formularios pero se conservan en base de datos.

---

## 11. Asignaciones (Trabajadores y Departamentos)

### 11.1 Departamentos

Gestiona la estructura organizativa. Cada activo y trabajador debe estar adscrito a un departamento.

- **Crear:** Botón "+ Nuevo".
- **Editar:** Ícono ✏️ en la tabla.
- **Desactivar:** Queda inactivo preservando la integridad de registros vinculados.

### 11.2 Trabajadores

Registro del personal de la institución. Campos principales:
- Nombres y Apellidos
- Cédula de Identidad
- Departamento
- Correo institucional (generado automáticamente con el formato institucional configurado)

> Los trabajadores pueden vincularse a cuentas de usuario para que sus incidencias se asocien automáticamente a su perfil y departamento.

---

## 12. Administración del Sistema

*(Acceso restringido a usuarios con permisos administrativos)*

### 12.1 Gestión de Usuarios

Desde **Administración > Usuarios**:
- Crear nuevos usuarios del sistema.
- Editar datos de acceso (nombre, correo, username, contraseña).
- Asignar roles a cada usuario.
- Activar/Desactivar cuentas.
- Vincular usuario a un trabajador.

> El usuario `super-admin` no puede ser modificado ni eliminado desde la interfaz.

### 12.2 Roles y Permisos

Desde **Administración > Roles y Permisos**:
- Crear roles personalizados.
- Asignar permisos individuales mediante casillas de verificación agrupadas por módulo.
- Ver qué usuarios tienen cada rol.

Los permisos están organizados por categorías: Inventario, Incidencias, Movimientos, Reportes, Administración.

### 12.3 Configuración General

Desde **Administración > Configuración General**:

| Parámetro | Descripción |
|-----------|-------------|
| **Cierre Irreversible de Incidencias** | Una incidencia cerrada no puede reabrirse. |
| **Activo Obligatorio en Incidencias** | Obliga a asociar un activo al crear una incidencia. |

### 12.4 Auditoría de Logs

Desde **Administración > Auditoría de Logs**:

Registro histórico de todas las acciones en el sistema. Permite:
- Filtrar por módulo, usuario, tipo de acción y rango de fechas.
- Ver el **Diff** de cada cambio: estado "Antes" y "Después".

---

## 13. Mi Perfil

Accede haciendo clic en tu nombre en la barra lateral, o desde el menú desplegable de usuario.

### 13.1 Información del Perfil

- Nombre completo, username y correo electrónico.
- **Avatar / Foto de Perfil:** Haz clic sobre el avatar para subir una imagen (JPG, PNG).

### 13.2 Cambiar Contraseña

Ingresa tu contraseña actual y la nueva (dos veces para confirmar).

### 13.3 Solicitud de Cambio de Datos de Trabajador

Si estás vinculado a un trabajador, puedes solicitar cambios en tus datos personales. Estas solicitudes requieren aprobación administrativa con vigencia de **180 días**.

### 13.4 Activos Asignados

Sección que muestra los activos tecnológicos que tienes asignados actualmente.

---

## 14. Reportes y Exportaciones

### 14.1 Exportación Excel

Disponible en módulos de Inventario y Movimientos *(según permiso `reportes-excel`)*. Botón **"Excel"** en la barra de acciones superior.

**Opciones:**
- **Vista Actual:** Solo los registros con los filtros activos.
- **Todo el Inventario:** Sin filtros, exportación completa.

**Contenido incluido:**
- Todos los campos y relaciones (nombre del departamento en lugar del ID).
- Columnas de auditoría: Creado Por, Modificado Por, Fechas.
- En movimientos: Folio de Incidencia vinculada (si aplica).

### 14.2 Fichas PDF

Disponibles en módulos de Inventario e Incidencias *(según permiso `reportes-pdf`)*. Se abren en una nueva pestaña del navegador.

**Fichas disponibles:**
- **Ficha Técnica de Computador:** Hardware, asignación, red e historial de movimientos.
- **Ficha Técnica de Dispositivo:** Datos del periférico, asignación y trazabilidad.
- **Ficha de Incidencia:** Detalle del caso, diagnóstico, resolución y movimientos asociados.

---

## 15. Tema Oscuro y Personalización Visual

### 15.1 Activar el Modo Oscuro

En la barra superior derecha del sistema encontrarás un botón circular con ícono de **🌙 luna**.

- **Clic:** Activa el **Modo Oscuro** (interfaz en tonos oscuros, ícono cambia a ☀️).
- **Clic de nuevo:** Vuelve al **Modo Claro**.

Tu preferencia se guarda automáticamente en el navegador y se aplica en tu próxima visita.

---

## 16. Glosario de Términos

| Término | Definición |
|---------|------------|
| **Bien Nacional (BN)** | Número de inventario oficial asignado por la organización a cada activo. |
| **Folio** | Número único de identificación de una incidencia (formato `#00001`). |
| **Movimiento** | Registro formal de un cambio en datos, custodia o estado de un activo. |
| **Borrador** | Movimiento creado pero no enviado para aprobación. Solo visible para su creador. |
| **Trazabilidad** | Capacidad del sistema de rastrear el historial completo de cambios. |
| **Insumo Crítico** | Insumo con stock actual menor o igual al nivel mínimo configurado. |
| **Resolutor** | Técnico con permisos para atender y cerrar incidencias. |
| **SoftDelete** | El dato se oculta de la interfaz pero se conserva en la base de datos para auditoría. |
| **Diff** | Comparativo visual de los valores "Antes" y "Después" de un cambio registrado. |

---

*Manual de Usuario SIGEINV — Versión 1.0*
*Para soporte técnico sobre el sistema, contacte al Área de Tecnología de la Información.*
