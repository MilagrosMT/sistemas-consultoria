
<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearUsuarioConRol(string $nombreRol): User
{
    $rol = Role::firstOrCreate(
        ['nombre' => $nombreRol],
        [
            'descripcion' => 'Rol creado para pruebas',
            'activo' => true,
        ]
    );

    return User::factory()->create([
        'role_id' => $rol->id,
        'activo' => true,
        'email_verified_at' => now(),
    ]);
}

test('administrador puede acceder a gestion de solicitudes', function () {
    $usuario = crearUsuarioConRol('Administrador');

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertOk();
});

test('recursos humanos puede acceder a gestion de solicitudes', function () {
    $usuario = crearUsuarioConRol('Recursos Humanos');

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertOk();
});

test('contabilidad puede acceder a gestion de solicitudes', function () {
    $usuario = crearUsuarioConRol('Contabilidad');

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertOk();
});

test('consulta no puede acceder a gestion de solicitudes', function () {
    $usuario = crearUsuarioConRol('Consulta');

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertForbidden();
});

test('usuario sin rol no puede acceder a gestion de solicitudes', function () {
    $usuario = User::factory()->create([
        'role_id' => null,
        'activo' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertForbidden();
});

test('usuario inactivo no puede acceder a gestion de solicitudes', function () {
    $usuario = crearUsuarioConRol('Administrador');

    // Actualizar el atributo directamente, sin asignación masiva.
    $usuario->activo = false;
    $usuario->save();
    $usuario->refresh();

    expect($usuario->activo)->toBeFalse();

    $this->actingAs($usuario)
        ->get(route('gestion-solicitudes'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
