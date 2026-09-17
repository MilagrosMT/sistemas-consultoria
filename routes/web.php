<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
| Todos los usuarios autenticados y verificados pueden acceder.
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Gestión del personal
|--------------------------------------------------------------------------
| Acceso para:
| - Administrador
| - Recursos Humanos
| - Contabilidad
| - Consulta
*/

Route::middleware([
    'auth',
    'verified',
    'role:Administrador,Recursos Humanos,Contabilidad,Consulta'
])->group(function () {

    // Empleados
    Route::livewire('/empleados', 'empleados')
        ->name('empleados');

// Clientes
Route::livewire('/clientes', 'clientes')
    ->name('clientes');

    // Reclutamiento
    Route::livewire('/reclutamiento', 'reclutamiento')
        ->name('reclutamiento');

    // Planillas
    Route::livewire('/planillas', 'planillas')
        ->name('planillas');

    // Administración tributaria
    Route::livewire('/administracion-tributaria', 'administracion-tributaria')
        ->name('administracion-tributaria');
Route::livewire('/reportes','reportes')->name('reportes');
// Servicios
Route::livewire('/servicios', 'servicios')
    ->name('servicios');
// Contratos
Route::livewire('/contratos', 'contratos')
    ->name('contratos');

// Marketing
Route::livewire('/marketing', 'marketing')
    ->name('marketing');
// Ventas / Facturación
Route::livewire('/ventas', 'ventas')
    ->name('ventas');
Route::livewire('/ventas-clientes', 'ventas-clientes')->name('ventas-clientes');
Route::livewire('/caja-clientes', 'caja-clientes')->name('caja-clientes');
Route::livewire('/procesamiento-contable', 'procesamiento-contable')->name('procesamiento-contable');
Route::livewire('/operaciones', 'operaciones')->name('operaciones');
Route::livewire('/comprobante/{venta}', 'comprobante')->name('comprobante');
Route::livewire('/compras', 'compras')->name('compras');
// Configuración general
Route::livewire('/configuracion', 'configuracion')
    ->name('configuracion');

});


/*
|--------------------------------------------------------------------------
| Administración del sistema
|--------------------------------------------------------------------------
| Solo los administradores pueden gestionar:
| - Usuarios
| - Roles y permisos
*/

Route::middleware([
    'auth',
    'verified',
    'role:Administrador'
])->group(function () {

    Route::livewire('/usuarios', 'usuarios')
        ->name('usuarios');

    Route::livewire('/roles', 'roles')
        ->name('roles');

});


/*
|--------------------------------------------------------------------------
| Configuración
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
