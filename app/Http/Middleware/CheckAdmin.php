<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Debes iniciar sesión para acceder a esta zona.');
        }

        // 2. Verificar si el usuario tiene el rol de administrador
        // Accedemos a la relación 'roles' definida en el modelo Usuario
        $user = Auth::user();
        $isAdmin = $user->roles()->where('slug_rol', 'admin')->exists();

        if (!$isAdmin) {
            // Si no es admin, redirigir al inicio con un mensaje de error
            return redirect('/')->with('error', 'No tienes permisos para acceder al panel administrativo.');
        }

        return $next($request);
    }
}
