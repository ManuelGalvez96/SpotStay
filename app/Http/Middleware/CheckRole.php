<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja una solicitud entrante y verifica si el usuario tiene el rol necesario.
     * De lo contrario, cierra la sesión e informa del error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role El slug del rol requerido (ej: 'admin', 'arrendador')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Si el usuario no está logueado, lo mandamos al login estándar
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Acceso denegado:<br>Debes iniciar sesión.');
        }


        // 2. Si está logueado pero NO tiene el rol específico requerido
        $user = Auth::user();
        if (!$user->roles()->where('slug_rol', $role)->exists()) {

            // Acción radical de seguridad solicitada por el usuario:
            // Expulsar al usuario completamente del sistema
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', "Acceso denegado:<br>Tu cuenta no tiene permisos para la sección de $role.");
        }

        return $next($request);
    }
}
