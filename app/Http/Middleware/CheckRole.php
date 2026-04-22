<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja una solicitud entrante y verifica si el usuario tiene al menos uno de los roles necesarios.
     * De lo contrario, cierra la sesión e informa del error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles Los slugs de los roles permitidos (ej: 'admin', 'miembro', 'inquilino')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Si el usuario no está logueado, lo mandamos al login estándar
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Acceso denegado:<br>Debes iniciar sesión.');
        }


        // 2. Si está logueado pero NO tiene ninguno de los roles requeridos
        $user = Auth::user();
        if (!$user->roles()->whereIn('slug_rol', $roles)->exists()) {

            // Acción radical de seguridad solicitada por el usuario:
            // Expulsar al usuario completamente del sistema
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $rolesRequeridos = implode('<br>', $roles);
            return redirect('/login')->with('error', "Acceso denegado:<br>Tu cuenta no tiene permisos suficientes <br>Se requiere uno de estos roles: <br> $rolesRequeridos");
        }

        return $next($request);
    }
}
