<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Roles;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Admin\IncidenciasConfig;
use App\Livewire\Dashboard\MainDashboard;
use App\Livewire\Catalogos\Marcas;
use App\Livewire\Catalogos\TiposDispositivo;
use App\Livewire\Catalogos\SistemasOperativos;
use App\Livewire\Catalogos\Puertos;
use App\Livewire\Catalogos\Procesadores;
use App\Livewire\Catalogos\Gpus;
use App\Livewire\Asignaciones\Trabajadores;
use App\Livewire\Asignaciones\Departamentos;
use App\Livewire\Asignaciones\Dependencias;
use App\Livewire\Inventario\Computadores;
use App\Livewire\Inventario\Dispositivos;
use App\Livewire\Movimientos\PanelComputadores;
use App\Livewire\Movimientos\PanelDispositivos;
use App\Livewire\Movimientos\PanelInsumos;

// Ruta para invitados (Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Rutas protegidas (Solo usuarios autenticados)
Route::middleware('auth')->group(function () {
    
    // Nuestro nuevo Dashboard
    Route::get('/', MainDashboard::class)->name('dashboard');
    Route::get('/admin/roles', Roles::class)->name('admin.roles')->can('ver-roles');
    Route::get('/admin/usuarios', Usuarios::class)->name('admin.usuarios')->can('ver-usuarios');
    // Configuración General
    Route::get('/admin/configuracion', \App\Livewire\Admin\ConfiguracionGeneral::class)->name('admin.configuracion');
    Route::get('/admin/auditoria', \App\Livewire\Admin\Auditoria::class)->name('admin.auditoria');
    // Reportes y Auditoría
    Route::prefix('reportes')->name('reportes.')->group(function() {
        // PDFs
        Route::get('/computador/{id}/ficha', [\App\Http\Controllers\ReporteController::class, 'computadorFicha'])->name('computador.ficha');
        Route::get('/dispositivo/{id}/ficha', [\App\Http\Controllers\ReporteController::class, 'dispositivoFicha'])->name('dispositivo.ficha');
        Route::get('/insumo/{id}/ficha', [\App\Http\Controllers\ReporteController::class, 'insumoFicha'])->name('insumo.ficha');
        Route::get('/gpu/{id}/ficha', [\App\Http\Controllers\ReporteController::class, 'gpuFicha'])->name('gpu.ficha');
        Route::get('/incidencia/{id}/ficha', [\App\Http\Controllers\ReporteController::class, 'incidenciaFicha'])->name('incidencia.ficha');

        // Excels Inventario
        Route::get('/inventario/computadores/excel', [\App\Http\Controllers\ReporteController::class, 'computadoresExcel'])->name('inventario.computadores.excel');
        Route::get('/inventario/dispositivos/excel', [\App\Http\Controllers\ReporteController::class, 'dispositivosExcel'])->name('inventario.dispositivos.excel');
        Route::get('/inventario/insumos/excel', [\App\Http\Controllers\ReporteController::class, 'insumosExcel'])->name('inventario.insumos.excel');
        Route::get('/inventario/software/excel', [\App\Http\Controllers\ReporteController::class, 'softwareExcel'])->name('inventario.software.excel');
        
        // Excels Operativos y Catálogos
        Route::get('/catalogo/{tipo}/excel', [\App\Http\Controllers\ReporteController::class, 'catalogoExcel'])->name('catalogo.excel');
        Route::get('/incidencias/excel', [\App\Http\Controllers\ReporteController::class, 'incidenciasExcel'])->name('incidencias.excel');
        Route::get('/movimientos/{segmento}/excel', [\App\Http\Controllers\ReporteController::class, 'movimientosExcel'])->name('movimientos.excel');
        Route::get('/usuarios/excel', [\App\Http\Controllers\ReporteController::class, 'usuariosExcel'])->name('usuarios.excel');

        // Masivo
        Route::post('/masivo/excel', [\App\Http\Controllers\ReporteController::class, 'reporteMasivo'])->name('masivo.excel');
    });

    // Catalogos
    Route::get('/catalogos/marcas', Marcas::class)->name('catalogos.marcas')->can('ver-marcas');
    Route::get('/catalogos/tipos-dispositivo', TiposDispositivo::class)->name('catalogos.tipos-dispositivo')->can('ver-tipos-dispositivo');
    Route::get('/catalogos/sistemas-operativos', SistemasOperativos::class)->name('catalogos.sistemas-operativos')->can('ver-sistemas-operativos');
    Route::get('/catalogos/puertos', Puertos::class)->name('catalogos.puertos')->can('ver-puertos');
    Route::get('/catalogos/procesadores', Procesadores::class)->name('catalogos.procesadores')->can('ver-procesadores');
    Route::get('/catalogos/gpus', Gpus::class)->name('catalogos.gpus')->can('ver-gpus');

    // Asignaciones
    Route::get('/asignaciones/departamentos', Departamentos::class)->name('asignaciones.departamentos')->can('ver-departamentos');
    Route::get('/asignaciones/dependencias', Dependencias::class)->name('asignaciones.dependencias')->can('ver-departamentos');
    Route::get('/asignaciones/trabajadores', Trabajadores::class)->name('asignaciones.trabajadores')->can('ver-trabajadores');

    // Módulos de Inventario
    Route::prefix('inventario')->name('inventario.')->group(function () {
        // NUEVA: Ruta para el módulo de Computadores
        Route::get('/computadores', Computadores::class)->name('computadores')->can('ver-computadores');
        Route::get('/dispositivos', Dispositivos::class)->name('dispositivos')->can('ver-dispositivos');
        Route::get('/insumos', \App\Livewire\Inventario\Insumos::class)->name('insumos')->can('ver-insumos');
        // NUEVA: Ruta para Software
        Route::get('/software', \App\Livewire\Inventario\Software::class)->name('software')->can('ver-software');
    });

    // Módulo de Incidencias (Operativo)
    Route::prefix('incidencias')->name('incidencias.')->group(function () {
        Route::get('/reportar', \App\Livewire\Incidencias\CrearTicket::class)->name('crear')->can('crear-ticket');
        Route::get('/gestion', \App\Livewire\Incidencias\Gestion::class)->name('gestion')->can('ver-incidencias');
    });

    // Módulo de Movimientos
    Route::prefix('movimientos')->name('movimientos.')->group(function () {
        Route::get('/computadores', PanelComputadores::class)->name('computadores')->can('movimientos-computadores-ver');
        Route::get('/dispositivos', PanelDispositivos::class)->name('dispositivos')->can('movimientos-dispositivos-ver');
        Route::get('/insumos', PanelInsumos::class)->name('insumos')->can('movimientos-insumos-ver');
        Route::get('/solicitudes-perfil', \App\Livewire\Movimientos\SolicitudesPerfil::class)->name('solicitudes-perfil')->can('admin-solicitudes-perfil');
    });

    // Dashboard de Asociaciones y Perfil
    Route::get('/asociaciones/{tipo}/{id}', \App\Livewire\AsociacionesDashboard::class)->name('asociaciones');
    Route::get('/perfil', \App\Livewire\Perfil\MiPerfil::class)->name('perfil');

    // Ruta simple para cerrar sesión
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

});