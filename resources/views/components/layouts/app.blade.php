<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>{{ env('ORG_NOMBRE', 'SIGEINV') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles

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
    </style>
</head>
<body class="bg-light">

    @auth
    <div class="d-flex vh-100 overflow-hidden">
        
        <div id="sidebarMenu" class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark shadow-sm" style="width: 280px; z-index: 1000;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <div>
                    <strong class="d-block fs-5">{{ env('ORG_NOMBRE', 'SIGEINV') }}</strong>
                    <small class="text-white-50" style="font-size: 0.8rem;">{{ env('ORG_DEPENDENCIA', 'Sistema de Inventario') }}</small>
                </div>
            </a>
            <hr>

            <ul class="nav nav-pills flex-column mb-auto overflow-auto" id="menuLateral">
                <li class="nav-item mb-1">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-white' }}">Dashboard</a>
                </li>

                <li class="nav-item mb-1">
                    <a href="#submenuCatalogos" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        Catálogos <small>▼</small>
                    </a>
                    <div class="collapse" id="submenuCatalogos" data-bs-parent="#menuLateral">
                        <ul class="nav flex-column ms-3 mt-1">
                            <li class="nav-item"><a href="{{ route('catalogos.marcas') }}" class="nav-link {{ request()->routeIs('catalogos.marcas') ? 'text-white' : 'text-white-50' }} px-2 py-1">Marcas</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Tipos de Dispositivo</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Sistemas Operativos</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Puertos</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Departamentos</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item mb-1">
                    <a href="#submenuInventario" data-bs-toggle="collapse" class="nav-link text-white d-flex justify-content-between align-items-center">
                        Inventario <small>▼</small>
                    </a>
                    <div class="collapse" id="submenuInventario" data-bs-parent="#menuLateral">
                        <ul class="nav flex-column ms-3 mt-1">
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Computadores</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Dispositivos</a></li>
                            <li class="nav-item"><a href="#" class="nav-link text-white-50 px-2 py-1">Consumibles</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item mb-1 mt-3">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-white-50 text-uppercase" style="font-size: 0.75rem;">
                        <span>Administración</span>
                    </h6>
                </li>
                <li class="nav-item mb-1">
                    <a href="{{ route('admin.roles') }}" class="nav-link {{ request()->routeIs('admin.roles') ? 'active' : 'text-white' }} d-flex align-items-center">
                        <i class="bi bi-shield-lock me-2"></i> Roles y Permisos
                    </a>
                </li>
            </ul>
            <hr>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <strong>{{ Auth::user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <main class="d-flex flex-column flex-grow-1 bg-light overflow-hidden">
            <header class="bg-white shadow-sm px-4 py-3 d-flex align-items-center justify-content-between">
                <!--
                <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('sidebarMenu').classList.toggle('d-none')">
                    ☰ Menú
                </button>
                -->
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

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('livewire:initialized', () => {
            
            // Escuchar evento para ABRIR modal
            Livewire.on('abrir-modal', (event) => {
                let data = event[0] || event;
                if (data && data.id) {
                    let modal = new bootstrap.Modal(document.getElementById(data.id));
                    modal.show();
                }
            });

            // Escuchar evento para CERRAR modal
            Livewire.on('cerrar-modal', (event) => {
                let data = event[0] || event;
                if (data && data.id) {
                    let modalEl = document.getElementById(data.id);
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { 
                        modal.hide(); 
                    }
                }
            });

            // Escuchar evento para MOSTRAR toast
            Livewire.on('mostrar-toast', (event) => {
                let data = event[0] || event;
                if (data && data.mensaje) {
                    document.getElementById('toastMessage').innerText = data.mensaje;
                    let toastEl = document.getElementById('liveToast');
                    let toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                    toast.show();
                }
            });

        });
    </script>
</body>
</html>