<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>{{ env('ORG_NOMBRE', 'SIGEINV') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles
    <style>
    /* Estandarización de Tablas SigeinvGDC */
    .th-id { width: 80px; min-width: 80px; }
    .th-estado { width: 120px; min-width: 120px; }
    .th-acciones { width: 140px; min-width: 140px; text-align: right; }
    /* La columna principal (ej. Nombre) no lleva clase, tomará el 100% del espacio sobrante */
    </style>
    <style>
        /* Transición suave para el menú lateral */
        #sidebarMenu {
            transition: all 0.3s ease-in-out;
        }
        /* Transición suave para ocultar el menú lateral */
        #sidebarMenu {
            transition: all 0.3s ease-in-out;
        }

        /* ========================================== */
        /* EFECTOS HOVER PARA EL MENÚ LATERAL         */
        /* ========================================== */
        
        /* Suaviza la animación de todos los enlaces del menú */
        #menuLateral .nav-link {
            transition: background-color 0.2s ease, color 0.2s ease;
            border-radius: 0.375rem; /* Bordes ligeramente redondeados */
        }

        /* Efecto al pasar el ratón (Hover) */
        #menuLateral .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1); /* Fondo blanco transparente */
            color: #ffffff !important; /* Texto totalmente blanco */
        }

        /* Opcional: Darle un poquito de margen izquierdo extra a los submenús en hover para efecto de profundidad */
        #menuLateral .collapse .nav-link:hover {
            padding-left: 1.5rem !important; 
        }

        /* Acentuar contraste en Modo Claro */
        [data-bs-theme="light"] body {
            background-color: #eff1f4 !important;
        }
        [data-bs-theme="light"] .bg-body-tertiary {
            background-color: #eff1f4 !important;
        }
    </style>
    <script>
        // Evitar el parpadeo blanco aplicando el tema guardado antes de renderizar
        const savedTheme = localStorage.getItem('sigeinv-theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</head>
<body class="bg-body-tertiary">

    @auth
    <div class="d-flex vh-100 overflow-hidden">
        
        <div id="sidebarMenu" class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark shadow-sm" style="width: 280px; z-index: 1000;">
            <a href="{{ route('perfil') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-2 py-1 rounded hover-bg-body-secondary">
                <div class="me-2">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 38px; height: 38px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <strong class="d-block fs-6 lh-1">{{ Auth::user()->name }}</strong>
                    <small class="text-white-50" style="font-size: 0.7rem;">@ {{ Auth::user()->username }}</small>
                </div>
            </a>
            <hr>

            <ul class="nav nav-pills flex-column mb-auto overflow-y-auto flex-nowrap w-100" id="menuLateral" style="overflow-x: hidden;">
                
                <li class="nav-item mb-1">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>

                @canany(['ver-marcas', 'ver-tipos-dispositivo', 'ver-sistemas-operativos', 'ver-puertos', 'ver-procesadores', 'ver-gpus'])
                <li class="nav-item mb-1">
                    <a href="#submenuCatalogos" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('catalogos.*') ? 'true' : 'false' }}" class="nav-link {{ request()->routeIs('catalogos.*') ? 'active' : 'text-white' }} d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-folder me-2"></i> Catálogos</div>
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('catalogos.*') ? 'show' : '' }}" id="submenuCatalogos" data-bs-parent="#menuLateral">
                        <ul class="nav flex-column ms-3 mt-1" style="border-left: 1px solid rgba(255,255,255,0.1);">
                            @can('ver-marcas')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.marcas') }}" class="nav-link {{ request()->routeIs('catalogos.marcas') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-tags me-2"></i> Marcas
                                </a>
                            </li>
                            @endcan
                            @can('ver-tipos-dispositivo')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.tipos-dispositivo') }}" class="nav-link {{ request()->routeIs('catalogos.tipos-dispositivo') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-pc-display me-2"></i> Tipos de Disp.
                                </a>
                            </li>
                            @endcan
                            @can('ver-sistemas-operativos')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.sistemas-operativos') }}" class="nav-link {{ request()->routeIs('catalogos.sistemas-operativos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-windows me-2"></i> Sist. Operativos
                                </a>
                            </li>
                            @endcan
                            @can('ver-puertos')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.puertos') }}" class="nav-link {{ request()->routeIs('catalogos.puertos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-usb-plug me-2"></i> Puertos
                                </a>
                            </li>
                            @endcan
                            @can('ver-procesadores')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.procesadores') }}" class="nav-link {{ request()->routeIs('catalogos.procesadores') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-cpu me-2"></i> Procesadores
                                </a>
                            </li>
                            @endcan
                            @can('ver-gpus')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.gpus') }}" class="nav-link {{ request()->routeIs('catalogos.gpus') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-cpu me-2"></i> Gpus
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany
                @canany(['ver-trabajadores', 'ver-departamentos'])
                <li class="nav-item mb-1">
                    <a href="#submenuAsignaciones" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('asignaciones.*') ? 'true' : 'false' }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : 'text-white' }} d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-card-checklist me-2"></i> Asignaciones</div>
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('asignaciones.*') ? 'show' : '' }}" id="submenuAsignaciones" data-bs-parent="#menuLateral">
                        <ul class="nav flex-column ms-3 mt-1" style="border-left: 1px solid rgba(255,255,255,0.1);">
                            @can('ver-trabajadores')
                            <li class="nav-item">
                                <a href="{{ route('asignaciones.trabajadores') }}" class="nav-link {{ request()->routeIs('asignaciones.trabajadores') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-person-badge me-2"></i> Trabajadores
                                </a>
                            </li>
                            @endcan
                            @can('ver-departamentos')
                            <li class="nav-item">
                                <a href="{{ route('asignaciones.departamentos') }}" class="nav-link {{ request()->routeIs('asignaciones.departamentos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-building me-2"></i> Departamentos
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                @canany(['ver-computadores', 'ver-dispositivos', 'ver-insumos', 'ver-software'])
                <li class="nav-item mb-1">
                    <a href="#submenuInventario" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('inventario.*') ? 'true' : 'false' }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'active' : 'text-white' }} d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-box-seam me-2"></i> Inventario</div>
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('inventario.*') ? 'show' : '' }}" id="submenuInventario" data-bs-parent="#menuLateral">
                        <ul class="nav flex-column ms-3 mt-1" style="border-left: 1px solid rgba(255,255,255,0.1);">
                            @can('ver-computadores')
                            <li class="nav-item">
                                <a href="{{ route('inventario.computadores') }}" class="nav-link {{ request()->routeIs('inventario.computadores') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-pc-display me-2"></i> Computadores
                                </a>
                            </li>
                            @endcan
                            @can('ver-dispositivos')
                            <li class="nav-item">
                                <a href="{{ route('inventario.dispositivos') }}" class="nav-link {{ request()->routeIs('inventario.dispositivos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-router me-2"></i> Dispositivos
                                </a>
                            </li>
                            @endcan
                            @can('ver-insumos')
                            <li class="nav-item">
                                <a href="{{ route('inventario.insumos') }}" class="nav-link {{ request()->routeIs('inventario.insumos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2"></i> Insumos/Herrm.
                                </a>
                            </li>
                            @endcan
                            @can('ver-software')
                            <li class="nav-item">
                                <a href="{{ route('inventario.software') }}" class="nav-link {{ request()->routeIs('inventario.software') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-disc me-2"></i> Software
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                {{-- Sección: Incidencias --}}
                @canany(['ver-incidencias', 'crear-ticket'])
                <li class="nav-item mb-1 mt-3">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-white-50 text-uppercase" style="font-size: 0.75rem;">
                        <span>Incidencias</span>
                    </h6>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link text-white d-flex align-items-center justify-content-between {{ request()->routeIs('incidencias.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#collapseIncidencias" role="button" aria-expanded="{{ request()->routeIs('incidencias.*') ? 'true' : 'false' }}">
                        <span><i class="bi bi-exclamation-triangle me-2"></i>Panel de Soporte</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('incidencias.*') ? 'show' : '' }}" id="collapseIncidencias">
                        <ul class="nav flex-column ms-3 border-start border-secondary ps-2">
                            @can('crear-ticket')
                            <li class="nav-item">
                                <a href="{{ route('incidencias.crear') }}" class="nav-link {{ request()->routeIs('incidencias.crear') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-plus-circle me-2"></i> Reportar
                                </a>
                            </li>
                            @endcan
                            @can('ver-incidencias')
                            <li class="nav-item">
                                <a href="{{ route('incidencias.gestion') }}" class="nav-link {{ request()->routeIs('incidencias.gestion') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-list-task me-2"></i> Gestión
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                {{-- Sección: Movimientos --}}
                @canany(['movimientos-computadores-ver', 'movimientos-dispositivos-ver', 'movimientos-insumos-ver'])
                <li class="nav-item mb-1 mt-3">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-white-50 text-uppercase" style="font-size: 0.75rem;">
                        <span>Movimientos</span>
                    </h6>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link text-white d-flex align-items-center justify-content-between {{ request()->routeIs('movimientos.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#collapseMovimientos" role="button">
                        <span><i class="bi bi-arrow-left-right me-2"></i>Panel de Flujo</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('movimientos.*') ? 'show' : '' }}" id="collapseMovimientos">
                        <ul class="nav flex-column ms-3 border-start border-secondary ps-2">
                            @can('movimientos-computadores-ver')
                            <li class="nav-item">
                                <a href="{{ route('movimientos.computadores') }}" class="nav-link {{ request()->routeIs('movimientos.computadores') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-pc-display me-2"></i> Computadores
                                </a>
                            </li>
                            @endcan
                            @can('movimientos-dispositivos-ver')
                            <li class="nav-item">
                                <a href="{{ route('movimientos.dispositivos') }}" class="nav-link {{ request()->routeIs('movimientos.dispositivos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-router me-2"></i> Dispositivos
                                </a>
                            </li>
                            @endcan
                            @can('movimientos-insumos-ver')
                            <li class="nav-item">
                                <a href="{{ route('movimientos.insumos') }}" class="nav-link {{ request()->routeIs('movimientos.insumos') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2"></i> Insumos
                                </a>
                            </li>
                            @endcan
                            @can('admin-solicitudes-perfil')
                            <li class="nav-item">
                                <a href="{{ route('movimientos.solicitudes-perfil') }}" class="nav-link {{ request()->routeIs('movimientos.solicitudes-perfil') ? 'text-white' : 'text-white-50' }} px-3 py-1 text-sm d-flex align-items-center">
                                    <i class="bi bi-person-badge me-2"></i> Solicitudes Perfil
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                @canany(['ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles', 'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'cambiar-estatus-usuarios', 'eliminar-usuarios'])
                <li class="nav-item mb-1 mt-3">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-white-50 text-uppercase" style="font-size: 0.75rem;">
                        <span>Administración</span>
                    </h6>
                </li>
                @endcanany

                @can('ver-roles')
                <li class="nav-item mb-1">
                    <a href="{{ route('admin.roles') }}" class="nav-link {{ request()->routeIs('admin.roles') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-shield-lock me-2"></i> Roles y Permisos
                    </a>
                </li>
                @endcan
                @canany(['admin-incidencias', 'admin-solicitudes-perfil'])
                <li class="nav-item mb-1">
                    <a href="{{ route('admin.configuracion') }}" class="nav-link {{ request()->routeIs('admin.configuracion') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-gear-wide-connected me-2"></i> Configuración General
                    </a>
                </li>
                @endcanany

                @can('admin-auditoria')
                <li class="nav-item mb-1">
                    <a href="{{ route('admin.auditoria') }}" class="nav-link {{ request()->routeIs('admin.auditoria') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-clock-history me-2"></i> Auditoría de Logs
                    </a>
                </li>
                @endcan
                @can('ver-usuarios')
                <li class="nav-item mb-1">
                    <a href="{{ route('admin.usuarios') }}" class="nav-link {{ request()->routeIs('admin.usuarios') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-people-fill me-2"></i> Usuarios
                    </a>
                </li>
                @endcan
            </ul>
            <hr>

            <div class="dropdown px-2">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2 rounded hover-bg-body-secondary" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 0;">
                    <div class="flex-shrink-0">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 ms-2 overflow-hidden">
                        <strong class="small d-block text-truncate">{{ Auth::user()->name }}</strong>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="{{ route('perfil') }}"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <main class="d-flex flex-column flex-grow-1 bg-body-tertiary overflow-hidden">
            <header class="bg-body shadow-sm px-4 py-3 d-flex align-items-center justify-content-between border-bottom">
                <div class="d-flex align-items-center">
                    <!-- Espacio para el breadcrumb o título -->
                </div>
                <div class="d-flex align-items-center">
                    <!-- Botón Tema Oscuro -->
                    <button id="themeToggleBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center border-0" style="width: 40px; height: 40px;" title="Cambiar Tema">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                    </button>
                </div>
            </header>
            
            <div class="flex-grow-1 overflow-auto p-4">
                {{ $slot }}
            </div>
        </main>

    </div>
    @else
    <div class="d-flex vh-100 align-items-center justify-content-center">
        <div class="container">
            {{ $slot }}
        </div>
    </div>
    @endauth

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div id="liveToast" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('livewire:initialized', () => {
            
            // 1. Escuchar evento para ABRIR modal (Mejorado para evitar duplicidad de backdrops)
            Livewire.on('abrir-modal', (event) => {
                let data = Array.isArray(event) ? event[0] : event;
                if (data && data.id) {
                    let modalEl = document.getElementById(data.id);
                    if (modalEl) {
                        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
            });

            // 2. Escuchar evento para CERRAR modal (Limpieza reforzada)
            Livewire.on('cerrar-modal', (event) => {
                let data = event ? (Array.isArray(event) ? event[0] : event) : null;
                
                // Si envías un ID específico, cierra ese modal
                if (data && data.id) {
                    let modalEl = document.getElementById(data.id);
                    if (modalEl) {
                        let modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                } else {
                    // Cierra cualquier modal que esté visible
                    document.querySelectorAll('.modal.show').forEach(modalEl => {
                        let modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    });
                }

                // LIMPIEZA FORZADA: A veces Bootstrap deja el fondo oscuro pegado
                setTimeout(() => {
                    const modalesAbiertos = document.querySelectorAll('.modal.show').length;
                    if (modalesAbiertos === 0) {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }
                }, 400); // Esperamos a que la animación de cierre termine
            });

            // 3. Sistema Unificado de Toasts (A PRUEBA DE BALAS)
            const procesarToast = (event) => {
                // 🕵️ CHIVATO: Abre la consola de tu navegador (F12) y mira qué imprime esto:
                console.log("Toast recibido desde PHP:", event);

                let data = Array.isArray(event) ? event[0] : event;
                
                if (data && data.mensaje) {
                    let toastEl = document.getElementById('liveToast');
                    document.getElementById('toastMessage').innerText = data.mensaje;
                    
                    // Limpiamos los colores de notificaciones anteriores, incluyendo el texto
                    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary', 'text-white', 'text-body');
                    
                    // Le ponemos texto blanco por defecto para que se lea bien en fondos oscuros
                    toastEl.classList.add('text-white');

                    // EXTRAEMOS EL TIPO: 
                    // 1. Buscamos 'tipo' o 'type' (por si acaso).
                    // 2. Lo pasamos a minúsculas para que 'ERROR', 'Error' o 'error' funcionen igual.
                    let tipoRaw = data.tipo || data.type || 'info';
                    let tipo = String(tipoRaw).toLowerCase(); 
                    
                    // Aplicamos el nuevo color dinámicamente con múltiples opciones
                    if (tipo === 'error' || tipo === 'danger') {
                        toastEl.classList.add('bg-danger');
                    } else if (tipo === 'success' || tipo === 'exito') {
                        toastEl.classList.add('bg-success');
                    } else if (tipo === 'warning' || tipo === 'alerta') {
                        toastEl.classList.add('bg-warning');
                        toastEl.classList.remove('text-white'); // El warning se lee mejor con texto oscuro
                        toastEl.classList.add('text-body');
                    } else {
                        toastEl.classList.add('bg-info');
                    }

                    // Disparamos la animación
                    let toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                    toast.show();
                }
            };

            // Vinculamos la función tanto al nombre nuevo como al viejo para no romper tu código anterior
            Livewire.on('toast', procesarToast);
            Livewire.on('mostrar-toast', procesarToast);
        });
    </script>
    
    <script>
        // Lógica del Botón de Modo Oscuro
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            
            if (!toggleBtn) return;

            const updateIcon = (theme) => {
                if (theme === 'dark') {
                    themeIcon.classList.remove('bi-moon-stars');
                    themeIcon.classList.add('bi-sun', 'text-warning');
                } else {
                    themeIcon.classList.remove('bi-sun', 'text-warning');
                    themeIcon.classList.add('bi-moon-stars');
                }
            };

            // Inicializar icono
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            updateIcon(currentTheme);

            toggleBtn.addEventListener('click', () => {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                const newTheme = isDark ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('sigeinv-theme', newTheme);
                updateIcon(newTheme);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>