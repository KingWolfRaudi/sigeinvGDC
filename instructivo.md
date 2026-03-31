INSTRUCTIVO DEL PROYECTO SIGEINV GDC (Versión 2.5)
Sistema de Gestión de Inventario Tecnológico
1. Stack Tecnológico Principal
    • Backend: PHP 8.3+ y Laravel 10+
    • Frontend: Livewire 3, Bootstrap 5 y Bootstrap Icons.
    • Base de Datos: MariaDB (Enfoque estrictamente relacional).
    • Seguridad y Permisos: spatie/laravel-permission (Asignación por Roles y Permisos).

2. Reglas de Base de Datos y Modelos
    1. SoftDeletes (Obligatorio): Todos los catálogos y módulos de inventario utilizan SoftDeletes tanto en la migración ($table->softDeletes()) como en el modelo (use SoftDeletes;). NUNCA se borran registros permanentemente por motivos de auditoría.
    2. Casteo de Booleanos ("Regla de Oro"): Todos los campos de estado (ej. activo) deben ser casteados en el modelo: protected $casts = ['activo' => 'boolean'];.
    3. Integridad Referencial Estricta: Las llaves foráneas a catálogos maestros deben usar onDelete('restrict'). Las llaves foráneas en tablas pivote pueden usar cascadeOnDelete().
    4. Relaciones Many-to-Many: Las conexiones múltiples (como los puertos en computadores o GPUs) jamás se guardan como texto plano o JSON. Se deben crear y utilizar tablas pivote (ej. gpu_puerto, computador_puerto).

3. Convenciones de UI, Vistas (Blade) y Notificaciones
    • Estructura del Sidebar (app.blade.php): * Debe usar flex-nowrap y overflow-y-auto para evitar deformaciones en el scroll interno.
        ◦ Acordeones Inteligentes: Deben mantenerse abiertos si la ruta coincide (request()->routeIs('inventario.*') ? 'show' : '').
        ◦ Enlaces protegidos: Deben estar envueltos en @can('ver-modulo') y mantener la estética text-white o text-white-50 según su estado activo.
    • Sistema Unificado de Toasts: Existe un único contenedor dinámico de Toasts de Bootstrap en app.blade.php. Los componentes Livewire deben emitir avisos usando $this->dispatch('toast', mensaje: '...', tipo: 'success' | 'error' | 'info');.
    • Cierre de Modales a Prueba de Fallos: El evento $this->dispatch('cerrar-modal') cierra inteligentemente cualquier modal abierto de Bootstrap mediante un listener global, enviando o no el ID del modal.
    • Estructura Estándar de Vistas Livewire:
        ◦ Cabecera: Fila con Título (Izq), Buscador con wire:model.live.debounce.300ms (Centro) y Botón "Nuevo" (Der).
        ◦ Tabla: Campos ordenables haciendo clic en la cabecera (wire:click="sortBy('campo')").
        ◦ Botones de Acción: Estandarizados (btn-success/btn-secondary para toggle, btn-info para ver detalles, btn-primary para editar, btn-danger para eliminar). Protegidos con directivas @can.
        ◦ Modales: Uso de directiva wire:ignore.self. Debe haber un modal de Formulario y un modal de Detalle (Read-Only).

4. Convenciones de Lógica y Controladores (Livewire)
    1. Eliminación Segura (Software-level Restrict): Debido al uso de SoftDeletes, la base de datos no bloquea eliminaciones de registros con hijos. ES OBLIGATORIO que el método eliminar($id) del controlador verifique la existencia de relaciones antes de borrar (ej. if ($marca->procesadores()->exists()) { ... abortar y emitir toast de error ... }).
    2. Forzado de Mayúsculas (strtoupper): SOLO aplica para "Texto Técnico" (ej. Tipo de Memoria: GDDR6). NO aplica para nombres, apellidos, marcas, ni departamentos. Estos se guardan tal cual los ingresa el usuario.
    3. Sufijos Automáticos: La memoria RAM y almacenamiento se manejan como enteros, pero se muestran o guardan con el sufijo "GB". La frecuencia con "MHz". Al editar, se debe limpiar el sufijo (str_replace) para los inputs number.
    4. Buscador Resiliente: El filtro de búsqueda en render() debe estar envuelto en un grupo where(function($query) { ... }) para no romper la paginación de Livewire (paginate(10)). Debe buscar en relaciones usando orWhereHas.
    5. Modales Rápidos ("Al Vuelo"): Los formularios de creación deben permitir crear dependencias directamente intercambiando un <select> por un <input> (ej. crear un Departamento al registrar Trabajador, o una Marca al registrar Procesador/GPU).

5. Módulos Desarrollados y Estado Actual
Catálogos Base (Completados y Refactorizados con Eliminación Segura):
    • Marcas, Tipos de Dispositivos, Sistemas Operativos, Puertos, Departamentos, Procesadores, GPUs.
Inventario (En Desarrollo):
    • ✅ Trabajadores: CRUD completado. Cuenta con "Creación Rápida de Departamento". Incluye Observer (TrabajadorObserver) que automatiza la cuenta de usuario, asigna el rol base "Trabajador", y sincroniza nombre/estado activo con la tabla users.
    • ⏳ Computadores (Próximo a desarrollar): Requiere manejo complejo de relaciones (muchas llaves foráneas), tabla pivote para puertos y múltiples modales de creación rápida "al vuelo".

6. Instrucciones Específicas para la IA Asistente
Si eres una IA leyendo esto en una nueva sesión, obedece estrictamente lo siguiente:
    1. Siempre incluye la etiqueta de apertura <?php en los bloques de código PHP.
    2. NUNCA asumas el diseño visual. Basa tus respuestas de vistas Blade en el estándar estricto mencionado en la sección 3 (Buscador central debounce, botones de acción con @can e iconos de Bootstrap).
    3. Antes de crear un módulo, DEBES incluir su respectiva inyección de permisos en el RolesAndPermissionsSeeder de Spatie y proporcionar el bloque HTML de cómo se debe ver en el layout principal (app.blade.php).
    4. Si se menciona código existente, asume que ya tiene los SoftDeletes, la "Eliminación Segura" y el casteo de booleanos implementado.
