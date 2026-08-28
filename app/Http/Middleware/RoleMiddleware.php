<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verifica que el usuario tenga uno de los roles permitidos.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        // Si no hay usuario autenticado, Laravel se encarga
        // de enviarlo al inicio de sesión.
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Obtenemos el usuario actualmente autenticado.
        $usuario = auth()->user();

        // Obtenemos el nombre de su rol.
        $rol = $usuario->role?->nombre;

        // Si el usuario no tiene rol o su rol no está permitido,
        // mostramos un error de autorización.
        if (!$rol || !in_array($rol, $roles)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}

