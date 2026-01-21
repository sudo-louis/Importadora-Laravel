<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        // Si quieres, aquí podrías permitir dashboard siempre sin permisos,
        // pero mejor lo controlamos desde rutas.
        if (!$user->canAccessModule($module)) {
            abort(403, 'No tienes acceso a este módulo');
        }

        return $next($request);
    }
}
