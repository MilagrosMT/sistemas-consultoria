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

    // Reclutamiento
    Route::livewire('/reclutamiento', 'reclutamiento')
        ->name('reclutamiento');

    // Planillas
    Route::livewire('/planillas', 'planillas')
        ->name('planillas');

    // Administración tributaria
    Route::livewire('/administracion-tributaria', 'administracion-tributaria')
        ->name('administracion-tributaria');

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
