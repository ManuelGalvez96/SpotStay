<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
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
        $usuarioId = (int) ($user->id_usuario ?? $user->id ?? 0);
        $tieneRolRequerido = $usuarioId > 0 && DB::table('tbl_rol_usuario as ru')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('ru.id_usuario_fk', $usuarioId)
            ->whereIn('r.slug_rol', $roles)
            ->exists();

        if (!$tieneRolRequerido) {
            // Excepción controlada: un arrendador puede entrar al panel de gestor si tiene propiedades asignadas como gestor.
            if (in_array('gestor', $roles, true)) {
                $tienePropiedadesComoGestor = $usuarioId > 0 && DB::table('tbl_propiedad')
                    ->where('id_gestor_fk', $usuarioId)
                    ->exists();

                if ($tienePropiedadesComoGestor) {
                    return $next($request);
                }
            }

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
