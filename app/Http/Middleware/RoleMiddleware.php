<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verifica que el usuario esté activo y tenga un rol permitido.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $usuario = auth()->user();

        if (! $usuario->activo) {
            abort(403, 'Tu cuenta está inactiva. Contacta con el administrador.');
        }

        $rol = $usuario->role?->nombre;

        if (! $rol || ! in_array($rol, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}