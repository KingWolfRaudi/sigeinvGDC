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

    // Administración
    Route::get('/admin/roles', \App\Livewire\Admin\Roles::class)->name('admin.roles')->can('ver-roles');
    Route::get('/admin/usuarios', \App\Livewire\Admin\Usuarios::class)->name('admin.usuarios')->can('ver-usuarios');
    Route::get('/admin/configuracion', \App\Livewire\Admin\ConfiguracionGeneral::class)->name('admin.configuracion');
    
    // Auditoría
    Route::get('/admin/auditoria', \App\Livewire\Admin\Auditoria::class)->name('admin.auditoria');
    Route::get('/admin/auditoria-tecnicos', \App\Livewire\Admin\AuditoriaTecnicos::class)->name('admin.auditoria-tecnicos');
    Route::get('/admin/auditoria-usuarios', \App\Livewire\Admin\AuditoriaUsuarios::class)->name('admin.auditoria-usuarios');

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
        Route::get('/logs/excel', [\App\Http\Controllers\ReporteController::class, 'logsExcel'])->name('logs.excel');
        Route::get('/logs/pdf', [\App\Http\Controllers\ReporteController::class, 'logsPdf'])->name('logs.pdf');
        Route::get('/auditoria-usuario/{id}/pdf', [\App\Http\Controllers\ReporteController::class, 'auditoriaUsuarioPdf'])->name('auditoria-usuario.pdf');

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
    Route::get('/asociaciones/{tipo}/{id}', \App\Livewire\AsociacionesDashboard::class)->name('asociaciones')->can('ver-dashboard');
    Route::get('/perfil', \App\Livewire\Perfil\MiPerfil::class)->name('perfil');

    // Ruta simple para cerrar sesión
    Route::post('/logout', function () {
        $user = Auth::user();
        if ($user) {
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('Cierre de sesión');
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Route::get('/seed-permissions', function () {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $p1 = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver-auditoria-tecnicos', 'guard_name' => 'web'], ['descripcion' => 'Permite visualizar la vista de auditoría de técnicos.']);
        $p2 = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver-auditoria-usuarios', 'guard_name' => 'web'], ['descripcion' => 'Permite visualizar la vista de auditoría individual de usuarios.']);
        
        $r1 = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
        if($r1) { $r1->givePermissionTo($p1); $r1->givePermissionTo($p2); }
        
        $r2 = \Spatie\Permission\Models\Role::where('name', 'administrador')->first();
        if($r2) { $r2->givePermissionTo($p1); $r2->givePermissionTo($p2); }
        
        return 'Permisos actualizados';
    });

});