# Manual de Usuario — SIGEINV
### Sistema de Gestión de Inventario Tecnológico y Mesa de Ayuda
**Versión 2.0 | Área de Tecnología de la Información**

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
11. [Asignaciones (Trabajadores, Departamentos y Dependencias)](#11-asignaciones)
12. [Administración del Sistema](#12-administración-del-sistema)
13. [Auditoría](#13-auditoría)
14. [Mi Perfil](#14-mi-perfil)
15. [Reportes y Exportaciones](#15-reportes-y-exportaciones)
16. [Tema Oscuro y Personalización Visual](#16-tema-oscuro-y-personalización-visual)
17. [Glosario de Términos](#17-glosario-de-términos)

---

## 1. Introducción y Acceso al Sistema

**SIGEINV** es el Sistema de Gestión de Inventario Tecnológico y Mesa de Ayuda. Permite registrar, controlar y dar seguimiento a todos los activos tecnológicos (computadores, dispositivos, insumos y software), gestionar solicitudes de soporte técnico con trazabilidad completa, y auditar la actividad de todos los usuarios del sistema.

### 1.1 Cómo Iniciar Sesión

1. Abre tu navegador (Chrome, Firefox, Edge) y dirígete a la dirección del sistema.
2. Ingresa tu **nombre de usuario** o **correo electrónico** y tu **contraseña**.
3. Activa **"Mantener sesión iniciada"** si deseas que el sistema recuerde tu acceso.
4. Haz clic en **"Entrar al Sistema"**.

> Cada inicio de sesión exitoso queda registrado en el sistema de auditoría con tu dirección IP. Los intentos fallidos también son registrados.

### 1.2 Cómo Cerrar Sesión

En la barra lateral izquierda, haz clic en tu nombre de usuario (parte inferior) y selecciona **"Cerrar Sesión"**.

---

## 2. Roles y Permisos

SIGEINV usa un sistema de roles y permisos que determina qué secciones y acciones están disponibles para cada usuario.

| Rol | Descripción |
|-----|-------------|
| **Super Administrador** | Acceso total sin restricciones. Rol ineditable desde la interfaz. |
| **Administrador** | Gestión completa de inventario, incidencias, movimientos, usuarios y auditoría. Sin permiso de eliminación permanente. |
| **Coordinador** | Supervisión total: reportes, auditoría de técnicos, incidencias completas, movimientos y consulta de inventario. |
| **Personal TI** | Acceso operativo: incidencias (menos solicitudes de perfil), movimientos y consulta/edición de inventario. |
| **Resolutor de Incidencia** | Atiende y cierra tickets asignados a su especialidad. Acceso al Dashboard. |
| **Trabajador** | Puede crear tickets de soporte y consultar su perfil. Acceso al Dashboard. |

> Los permisos específicos de cada rol se configuran desde **Administración > Roles y Permisos**.

> **Usuario sin rol:** Un usuario autenticado sin ningún rol asignado no tiene acceso al Dashboard. Al iniciar sesión será redirigido automáticamente a su Perfil, donde puede gestionar sus datos mientras espera que un administrador le asigne un rol.

---

## 3. Panel de Control (Dashboard)

Pantalla principal al iniciar sesión. Muestra el estado operativo en tiempo real.

### 3.1 Métricas Operativas (Tarjetas superiores)

| Tarjeta | Descripción |
|---------|-------------|
| 🔴 **Tickets sin Asignar** | Incidencias abiertas sin técnico asignado. |
| 🟡 **En Curso** | Casos activos con técnico trabajando. |
| 🟠 **Movimientos Solicitados** | Solicitudes pendientes de aprobación. |
| ⚠️ **Insumos Críticos** | Stock igual o inferior al mínimo establecido. |

### 3.2 Panel de Composición de Hardware

Gráficos interactivos basados en el inventario real (visible solo para roles técnicos, no para Trabajadores):
- **Distribución de RAM:** Clasifica equipos por capacidad (≤4GB, 8GB, 16GB, >16GB).
- **Tecnología de Almacenamiento:** SSD/NVME vs. HDD mecánicos.

Indicadores laterales muestran la **capacidad total de RAM** y **almacenamiento total** gestionado.

### 3.3 Mesa de Ayuda

Dos paneles paralelos en la parte inferior (visible solo para Administrador, Coordinador y Personal TI):
- **Atención Rápida:** Técnicos ven sus casos asignados; administradores ven los últimos tickets del sistema.
- **Historial de Resoluciones:** Casos resueltos recientes (del técnico o globales según el rol).

---

## 4. Inventario de Computadores

Gestión de desktops, laptops y servidores.

### 4.1 Información en la Tabla

- Bien Nacional, Serial, Tipo, Nombre del equipo, Marca, RAM total, Almacenamiento total, IP, MAC, Departamento, Estado físico y Estado (Activo/Inactivo).

**Indicadores especiales en la fila:**
- 🟡 **"En revisión":** Tiene un movimiento pendiente de aprobación.
- 🔵 **"Borrador":** Hay un borrador de cambio sin enviar para ese equipo.

### 4.2 Filtros y Búsqueda

- **Buscador:** Por Bien Nacional, Serial o IP.
- **Filtro de Estado:** Todos / Solo Activos / Solo Inactivos.
- **Filtro de Departamento:** Restringe por ubicación.

### 4.3 Registrar un Nuevo Computador

1. Clic en **"+ Nuevo"**.
2. Completa: identificación (tipo, nombre, BN, serial), asignación (departamento, dependencia, trabajador), hardware (SO, procesador, GPU, RAM, discos), conectividad (IP, MAC, tipo conexión, puertos) y estado físico.
3. Clic en **"Guardar Computador"**.

### 4.4 Ver Detalles

Clic en **👁 (Ver)**. Ficha completa con toda la información técnica. Desde aquí accedes al botón **"Asociaciones"** para ver dispositivos e insumos vinculados.

### 4.5 Editar un Equipo

1. Clic en **✏️ (Editar)**.
2. Modifica los campos necesarios.
3. **Obligatorio:** Ingresa una **Justificación del Cambio** (mín. 10 caracteres).
4. Clic en **"Guardar Computador"**.

> El cambio queda en estado **"Borrador"** hasta que un administrador lo apruebe en el Panel de Movimientos.

### 4.6 Cambiar Estado Activo/Inactivo

Clic en el botón de palanca (🟢/⚫). Genera un movimiento de "Cambio de Estatus" que requiere aprobación.

### 4.7 Descargar Ficha PDF

Clic en **📄 (PDF)** en las acciones del registro *(requiere permiso `reportes-pdf`)*.

### 4.8 Vista de Asociaciones

Muestra todos los activos vinculados al computador: dispositivos periféricos e insumos instalados, con sus datos de identificación y estado.

---

## 5. Inventario de Dispositivos

Gestión de periféricos y equipos de red: impresoras, routers, switches, monitores, proyectores, etc.

Funciona igual al módulo de Computadores con estas particularidades:
- **Tipo de Dispositivo:** Se selecciona de un catálogo predefinido.
- **Asociación a Computador:** Un dispositivo puede vincularse opcionalmente a un equipo host.
- **Asociación a Dependencia:** Se puede especificar la subdivisión del departamento.
- No lleva módulos de hardware interno (RAM/discos).

Las acciones disponibles son las mismas: Ver, Editar, Cambiar Estado, Descargar PDF, Exportar Excel y Vista de Asociaciones.

---

## 6. Almacén de Insumos y Herramientas

Control de stock de consumibles, repuestos y herramientas.

### 6.1 Campos Principales

| Campo | Descripción |
|-------|-------------|
| **Nombre** | Nombre descriptivo del insumo. |
| **Categoría** | Grupo al que pertenece (Tóner, Herramienta, Cable, etc.). |
| **Unidad de Medida** | Unidades, metros, litros, cajas, pares. |
| **Medida Actual** | Stock disponible actualmente. |
| **Medida Mínima** | Nivel de alerta de stock crítico. |
| **Reutilizable** | Si debe ser devuelto tras su uso (ej. herramientas). |
| **Instalable en Equipo** | Si el insumo se instala físicamente en un equipo. |
| **Ubicación** | Departamento, dependencia y responsable asignado. |

### 6.2 Alertas de Stock Crítico

Cuando el stock cae al nivel mínimo, el sistema destaca el registro en la tabla y aumenta el contador de **"Insumos Críticos"** en el Dashboard.

---

## 7. Catálogo de Software

Inventario de licencias de software corporativo.

### 7.1 Información Registrada

- Nombre del programa, arquitectura (32/64-bit), tipo de licencia (Libre/Privativo).
- Serial/Clave de activación.
- Estado (Activo/Inactivo).
- Auditoría: quién registró y cuándo.

### 7.2 Acciones

- **Ver:** Detalle completo incluyendo serial.
- **Editar:** Modifica cualquier campo.
- **PDF:** Descarga ficha individual del software.
- **Excel:** Exporta el catálogo completo o filtrado.

---

## 8. Panel de Soporte e Incidencias

Sistema de dos niveles: usuarios reportan fallas, técnicos las atienden.

### 8.1 Reportar un Ticket (Vista del Usuario)

Accede desde **Panel de Soporte > Reportar**.

1. **Departamento:** Se asigna automáticamente si tu usuario está vinculado a un trabajador.
2. **Tipo de Activo Afectado:** Computador, Dispositivo u Otro.
3. **Activo Específico:** Selecciona el equipo afectado.
4. **Tipo de Problema:** Categoría del inconveniente.
5. **Descripción Detallada:** Explica la falla con el mayor detalle posible.
6. Clic en **"Enviar Reporte"**.

Recibirás confirmación con el **número de folio único** (`#00001`).

### 8.2 Gestión de Incidencias (Técnico/Administrador)

Accede desde **Panel de Soporte > Gestión**.

**Filtros disponibles:** Texto libre, Técnico Asignado, Estado y Departamento.

**Acciones sobre una incidencia:**

| Acción | Descripción |
|--------|-------------|
| **Ver Detalle (👁)** | Modal completo con toda la información del caso. |
| **Asignar Técnico** | Asigna un técnico resolutor. |
| **Registrar Diagnóstico** | Documenta el análisis técnico realizado. |
| **Marcar Solventado** | Indica que el problema fue resuelto. |
| **Cerrar Incidencia** | Cierre definitivo *(puede ser irreversible según configuración)*. |
| **¿Amerita Movimiento?** | Activa el flag para crear un movimiento de inventario desde esta incidencia. |
| **Registrar Movimiento** | Aparece solo si el flag está activo. Redirige al panel de movimientos con datos precargados. |
| **Descargar PDF** | Genera la ficha técnica de la incidencia *(requiere permiso)*. |

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
| **Aprobado** | Validado y aplicado automáticamente al inventario. |
| **Rechazado** | Denegado con justificación del administrador. |
| **Ejecutado Directo** | Aplicado sin pasar por aprobación (requiere permiso especial). |

### 9.3 Tipos de Operación Disponibles

- **Actualización de Datos:** Cambio en cualquier campo descriptivo del activo.
- **Cambio de Departamento:** Reasignación de ubicación.
- **Reasignación de Trabajador:** Cambio de custodio/responsable.
- **Cambio de Estado Físico:** Operativo / Dañado / En Reparación / Baja.
- **Cambio de Estatus:** Activar o desactivar el activo.

### 9.4 Movimientos desde Incidencias

Cuando se origina desde una incidencia, el sistema:
- Pre-selecciona el activo vinculado.
- Precarga la justificación: *"Vinculado a la incidencia #XXXXX"*.
- Registra el Folio de Incidencia para trazabilidad cruzada.

### 9.5 Aprobar o Rechazar un Movimiento

*(Requiere permiso de aprobación)*

1. Localiza el movimiento en estado "Solicitado".
2. Clic en **"Ver Cambio (👁)"**.
3. Revisa el comparativo (valores anteriores vs. propuestos).
4. Clic en **"Aprobar"** o **"Rechazar"** (con motivo obligatorio si se rechaza).

### 9.6 Solicitudes de Perfil

Gestiona peticiones de cambio de datos personales de trabajadores (nombre, cédula, correo, username). Pasan por flujo de aprobación con vigencia de **180 días**. Vencida la vigencia, la solicitud expira automáticamente.

---

## 10. Catálogos del Sistema

Tablas de referencia que alimentan los formularios del inventario. Accede desde el menú **Catálogos**.

| Catálogo | Descripción |
|----------|-------------|
| **Marcas** | Fabricantes de equipos (Dell, HP, Lenovo, etc.). |
| **Tipos de Dispositivo** | Clasificaciones de periféricos (Impresora, Monitor, etc.). |
| **Sistemas Operativos** | SO disponibles para asignar a computadores. |
| **Puertos de Conexión** | USB, HDMI, RJ45, DisplayPort, etc. |
| **Procesadores** | Modelos de CPU con socket y marca. |
| **GPUs** | Modelos de tarjetas gráficas con puertos disponibles. |

Todos permiten **crear, editar y activar/desactivar** registros. Los inactivos no aparecen en formularios pero se conservan en la base de datos para trazabilidad histórica.

---

## 11. Asignaciones

### 11.1 Departamentos

Gestiona la estructura organizativa. Cada activo y trabajador debe estar adscrito a un departamento.

### 11.2 Dependencias

Subdivisionamiento dentro de un departamento (ej. una sección o unidad interna). Permite mayor precisión en la ubicación de activos y trabajadores.

### 11.3 Trabajadores

Registro del personal de la institución. Campos principales:
- Nombres, Apellidos, Cédula, Cargo.
- Departamento y Dependencia (subdivisión).
- Correo institucional (generado automáticamente con el dominio configurado).

> Los trabajadores pueden vincularse a cuentas de usuario del sistema para que sus incidencias se asocien automáticamente.

---

## 12. Administración del Sistema

*(Acceso restringido a usuarios con permisos administrativos)*

### 12.1 Gestión de Usuarios

Desde **Administración > Usuarios**:
- Crear nuevos usuarios del sistema.
- Editar datos de acceso (nombre, correo, username, contraseña).
- Asignar roles.
- Activar/Desactivar cuentas.
- Vincular usuario a un trabajador y asignar especialidad técnica.
- Establecer disponibilidad para asignación de tickets.

> El usuario `super-admin` no puede ser modificado ni eliminado desde la interfaz.

### 12.2 Roles y Permisos

Desde **Administración > Roles y Permisos**:
- Crear roles personalizados con nombre y descripción.
- Asignar permisos individuales mediante casillas de verificación agrupadas por módulo.
- Ver qué usuarios tienen cada rol asignado.

### 12.3 Configuración General

Desde **Administración > Configuración General** (organizada en pestañas):

**Pestaña: Ajustes de Incidencias**

| Parámetro | Descripción |
|-----------|-------------|
| **Cierre Irreversible** | Si activo, las incidencias cerradas no pueden reabrirse. |
| **Activo Obligatorio** | Obliga a asociar un activo al crear una incidencia. |
| **Técnicos ven todos los tickets pendientes** | Si desactivado, los técnicos solo ven en el dashboard los tickets que coinciden con su especialidad técnica. |

**Pestaña: Catálogo de Problemas**

Gestiona los tipos de problemas disponibles al crear un ticket. Cada problema puede vincularse a una especialidad técnica para el enrutamiento automático.

**Pestaña: Especialidades Técnicas**

Define las áreas de especialización del personal técnico (Redes, Hardware, Software, etc.).

**Pestaña: Perfil de Usuario**

Permite al usuario autenticado actualizar su avatar y datos básicos de su cuenta desde la misma sección de configuración.

---

## 13. Auditoría

Sección exclusiva para administradores y supervisores. Accede desde el menú **Auditoría** en la barra lateral.

### 13.1 Auditoría de Logs (Sistema General)

Registro histórico de **todas** las acciones realizadas en el sistema por cualquier usuario.

**Filtros disponibles:**
- Responsable (nombre o correo)
- Módulo / Acción
- Rango de fechas (Desde / Hasta)

**Información por registro:**
- ID, Responsable, Tipo de Acción (badge de color), Módulo Afectado, Fecha/Hora.
- Botón **"Detalle"**: abre un modal con el comparativo completo de campos modificados (valor anterior tachado en rojo → valor nuevo en verde).

**Exportaciones disponibles:**
- **Excel:** Exporta los logs con los filtros aplicados.
- **PDF:** Genera un reporte en hoja horizontal con los mismos filtros (máximo 500 registros).
- **Generador Pro:** Herramienta avanzada para crear reportes Excel multi-módulo con selección individualizada de módulos y filtros.

### 13.2 Auditoría de Técnicos

Vista enfocada en el **rendimiento operativo** del personal técnico.

**Filtros:** Técnico específico y rango de fechas (por defecto el mes actual).

**KPIs mostrados:**

| Indicador | Descripción |
|-----------|-------------|
| **Tickets Asignados** | Total de tickets que tuvo asignados en el período. |
| **Tickets Resueltos** | Casos marcados como solventados. |
| **Tickets Abiertos** | Casos sin resolver aún en el período. |
| **Tasa de Resolución** | Porcentaje de casos resueltos sobre asignados. |
| **Tiempo Promedio** | Tiempo promedio entre creación y resolución. |

Debajo de los KPIs, una tabla detalla el **historial completo de actividad de tickets** del técnico en el período.

### 13.3 Auditoría de Usuarios

Vista para inspeccionar exhaustivamente la actividad de un usuario específico.

**Cómo usarla:**
1. Usa el buscador para encontrar al usuario (por nombre, username o correo).
2. Filtra por estado (Activos / Inactivos / Todos).
3. Haz clic en **"Detalle y Auditoría"**.

**Modal de detalle:**
- **Perfil del usuario:** nombre, correo, username, roles asignados y especialidad técnica.
- **Historial de actividad:** tabla con sus últimas 100 acciones en el sistema, filtrable por rango de fechas. Cada registro muestra:
  - Fecha/Hora, Tipo de Acción, Módulo Afectado.
  - **Detalles adicionales:** lista de campos modificados con valor anterior (tachado en rojo) y valor nuevo (en verde). Los campos booleanos se muestran como Sí/No y los campos de relación como "ID X".

**Reporte PDF:** Botón dentro del modal que genera un PDF con el perfil completo del usuario y su historial de actividad filtrado por fechas (máximo 200 registros).

---

## 14. Mi Perfil

Accede haciendo clic en tu nombre en la barra lateral o desde el menú desplegable de usuario.

### 14.1 Información del Perfil

- Nombre completo, username y correo electrónico.
- **Avatar:** Haz clic sobre tu foto para subir una nueva imagen (JPG, PNG).

### 14.2 Cambiar Contraseña

Ingresa tu contraseña actual y la nueva (dos veces para confirmar).

### 14.3 Solicitud de Cambio de Datos

Si estás vinculado a un trabajador, puedes solicitar cambios en nombre, username, correo o contraseña. Estas solicitudes requieren aprobación de un administrador y tienen vigencia de **180 días**.

### 14.4 Activos Asignados

Sección que muestra los activos tecnológicos que tienes actualmente asignados (computadores e insumos).

---

## 15. Reportes y Exportaciones

### 15.1 Exportación Excel

Disponible en los módulos de Inventario, Incidencias, Movimientos, Usuarios y Auditoría *(requiere permiso `reportes-excel`)*. Botón **"Excel"** en la barra de acciones superior.

Todos los Excel exportan los datos con los **filtros activos en pantalla** en ese momento.

**Contenido incluido:**
- Todos los campos y relaciones (nombres en lugar de IDs).
- Columnas de auditoría: Creado Por, Modificado Por, Fechas.
- En movimientos: Folio de Incidencia vinculada (si aplica).

### 15.2 Fichas PDF Individuales

Disponibles en Inventario e Incidencias *(requiere permiso `reportes-pdf`)*. Se abren en nueva pestaña del navegador.

| Ficha | Contenido |
|-------|-----------|
| **Computador** | Hardware, asignación, red, historial de movimientos. |
| **Dispositivo** | Datos del periférico, asignación, puertos y movimientos. |
| **Insumo** | Stock, categoría, responsable y movimientos. |
| **GPU** | Especificaciones técnicas y puertos disponibles. |
| **Incidencia** | Detalle del caso, diagnóstico, resolución y movimientos asociados. |
| **Software** | Datos de la licencia y serial de activación. |

### 15.3 Reportes de Auditoría PDF

| Reporte | Acceso | Descripción |
|---------|--------|-------------|
| **Logs del Sistema** | Auditoría > Logs > Botón PDF | Registros filtrados en hoja horizontal (máx. 500). |
| **Auditoría de Usuario** | Auditoría > Usuarios > Modal > Botón PDF | Perfil + actividad de un usuario (máx. 200 registros). |

### 15.4 Generador Pro (Reporte Masivo)

*(Requiere permiso `reportes-masivos-filtros`)*

Accesible desde **Auditoría de Logs > "Generador Pro"**. Permite crear un archivo Excel con múltiples hojas:

1. Selecciona los módulos a incluir (Computadores, Dispositivos, Insumos, Incidencias, etc.).
2. Por módulo, elige entre **"Todo el Inventario"** o **"Vista con Filtros"**.
3. Si eliges filtros, puedes seleccionar Estado y Departamento por módulo.
4. Haz clic en **"Generar Reporte"**.

El archivo descargado tendrá una hoja separada por cada módulo seleccionado.

---

## 16. Tema Oscuro y Personalización Visual

En la barra superior derecha encontrarás el botón de cambio de tema:

- **Clic (ícono 🌙):** Activa el **Modo Oscuro**.
- **Clic de nuevo (ícono ☀️):** Vuelve al **Modo Claro**.

Tu preferencia se guarda automáticamente en el navegador y se aplica en tu próxima visita. Todos los módulos del sistema, incluyendo tablas, filtros, modales y formularios, se adaptan completamente al tema seleccionado.

---

## 17. Glosario de Términos

| Término | Definición |
|---------|------------|
| **Bien Nacional (BN)** | Número de inventario oficial asignado por la organización a cada activo. |
| **Folio** | Número único de identificación de una incidencia (formato `#00001`). |
| **Movimiento** | Registro formal de un cambio en datos, custodia o estado de un activo. |
| **Borrador** | Movimiento creado pero no enviado para aprobación. Solo visible para su creador. |
| **Trazabilidad** | Capacidad del sistema de rastrear el historial completo de cambios de un activo o usuario. |
| **Insumo Crítico** | Insumo con stock actual menor o igual al nivel mínimo configurado. |
| **Resolutor** | Técnico con permisos para atender y cerrar incidencias. |
| **SoftDelete** | El dato se oculta de la interfaz pero se conserva en la base de datos para auditoría. |
| **Diff** | Comparativo visual de los valores "Antes" y "Después" de un cambio registrado. |
| **Dependencia** | Subdivisión interna de un departamento (sección, unidad o área específica). |
| **KPI** | Indicador clave de rendimiento. En Auditoría de Técnicos mide la productividad operativa. |
| **Especialidad Técnica** | Área de conocimiento de un técnico (Redes, Hardware, Software) que determina el enrutamiento de tickets. |
| **Log** | Registro automático de una acción realizada en el sistema, con fecha, usuario y detalle del cambio. |

---

*Manual de Usuario SIGEINV — Versión 2.0 (Actualizado: Mayo 2026)*
*Para soporte técnico sobre el sistema, contacte al Área de Tecnología de la Información.*
