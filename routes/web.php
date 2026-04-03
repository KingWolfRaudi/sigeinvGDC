<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\Roles;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Dashboard;
use App\Livewire\Catalogos\Marcas;
use App\Livewire\Catalogos\TiposDispositivo;
use App\Livewire\Catalogos\SistemasOperativos;
use App\Livewire\Catalogos\Puertos;
use App\Livewire\Catalogos\Procesadores;
use App\Livewire\Catalogos\Gpus;
use App\Livewire\Asignaciones\Trabajadores;
use App\Livewire\Asignaciones\Departamentos;
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
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/admin/roles', Roles::class)->name('admin.roles')->can('ver-roles');
    Route::get('/admin/usuarios', Usuarios::class)->name('admin.usuarios')->can('ver-usuarios');
    // Catalogos
    Route::get('/catalogos/marcas', Marcas::class)->name('catalogos.marcas')->can('ver-marcas');
    Route::get('/catalogos/tipos-dispositivo', TiposDispositivo::class)->name('catalogos.tipos-dispositivo')->can('ver-tipos-dispositivo');
    Route::get('/catalogos/sistemas-operativos', SistemasOperativos::class)->name('catalogos.sistemas-operativos')->can('ver-sistemas-operativos');
    Route::get('/catalogos/puertos', Puertos::class)->name('catalogos.puertos')->can('ver-puertos');
    Route::get('/catalogos/procesadores', Procesadores::class)->name('catalogos.procesadores')->can('ver-procesadores');
    Route::get('/catalogos/gpus', Gpus::class)->name('catalogos.gpus')->can('ver-gpus');
    // Asignaciones
    Route::get('/asignaciones/departamentos', Departamentos::class)->name('asignaciones.departamentos')->can('ver-departamentos');
    Route::get('/asignaciones/trabajadores', Trabajadores::class)->name('asignaciones.trabajadores')->can('ver-trabajadores');
    // Módulos de Inventario
    Route::prefix('inventario')->name('inventario.')->group(function () {
        
        // Ruta que ya arreglaste para Trabajadores
        //Route::get('/trabajadores', Trabajadores::class)->name('trabajadores');
        
        // NUEVA: Ruta para el módulo de Computadores
        Route::get('/computadores', Computadores::class)->name('computadores')->can('ver-computadores');
        Route::get('/dispositivos', Dispositivos::class)->name('dispositivos')->can('ver-dispositivos');
        Route::get('/insumos', \App\Livewire\Inventario\Insumos::class)->name('insumos')->can('ver-insumos');
        // Aquí irán las futuras rutas (dispositivos, consumibles, etc.)
    });

    // Módulo de Movimientos
    Route::prefix('movimientos')->name('movimientos.')->group(function () {
        Route::get('/computadores', PanelComputadores::class)->name('computadores')->can('movimientos-computadores-ver');
        Route::get('/dispositivos', PanelDispositivos::class)->name('dispositivos')->can('movimientos-dispositivos-ver');
        Route::get('/insumos', PanelInsumos::class)->name('insumos')->can('movimientos-insumos-ver');
    });
    //Route::get('/trabajadores', \App\Livewire\Inventario\Trabajadores::class);
    //Route::get('/inventario/trabajadores', Trabajadores::class)->name('inventario.trabajadores');

    // Ruta simple para cerrar sesión
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

});