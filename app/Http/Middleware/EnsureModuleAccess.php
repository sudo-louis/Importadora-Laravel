<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Uso:
     *  ->middleware('module:reportes')
     *  ->middleware('module:usuarios')
     *  ->middleware('module:actividad')
     *  ->middleware('module:contenedores')
     *
     * Alias:
     *  plantillas => reportes
     *
     * Nota:
     *  Dashboard lo dejamos libre (solo auth), no pasa por aquí.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, que resuelva auth middleware antes, pero por seguridad:
        if (!$user) {
            abort(401);
        }

        $module = mb_strtolower(trim($module));

        // Alias: Plantillas pertenece a Reportes
        if ($module === 'plantillas') {
            $module = 'reportes';
        }

        // Reglas especiales
        if ($module === 'contenedores') {
            // Contenedores = permiso contenedores/crear OR cualquier pestaña
            $allowed =
                $user->hasPermiso('contenedores', 'crear') ||
                $user->hasPermiso('registro') ||
                $user->hasPermiso('liberacion') ||
                $user->hasPermiso('docs') ||
                $user->hasPermiso('cotizacion') ||
                $user->hasPermiso('despacho') ||
                $user->hasPermiso('gastos');

            if (!$allowed) {
                abort(403, 'No tienes acceso al módulo Contenedores.');
            }

            return $next($request);
        }

        // Regla general: si tiene cualquier permiso dentro del módulo
        if (!$user->canAccessModule($module)) {
            abort(403, 'No tienes acceso a este módulo.');
        }

        return $next($request);
    }
}
