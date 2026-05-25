<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureArrendadorIsActive
{
    /**
     * Maneja una solicitud entrante y verifica si el arrendador ha cumplido
     * con sus obligaciones financieras (pago) y de configuración (Connect).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $usuarioId = (int) ($user->id_usuario ?? $user->id ?? 0);

        // 1. Si no está logueado, al login
        if (!$user || $usuarioId <= 0) {
            return redirect()->route('login');
        }

        $esArrendador = DB::table('tbl_rol_usuario as ru')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('ru.id_usuario_fk', $usuarioId)
            ->where('r.slug_rol', 'arrendador')
            ->exists();

        // 2. Estas restricciones solo aplican al rol de 'arrendador'
        if ($esArrendador) {
            
            // PASO 1: Verificar Suscripción Mensual
            if ($user->stripe_status !== 'active') {
                if (!$request->is('miembro/suscripcion*')) {
                    return redirect()->route('miembro.suscripcion.index')
                        ->with('info', 'Para acceder a esta sección, primero debes activar tu suscripción mensual.');
                }
            }

            // PASO 2: Verificar Cuenta de Cobros / IBAN (Solo para Arrendadores)
            if (!$user->stripe_account_id) {
                if (!$request->is('arrendador/configurar-stripe*') && 
                    !$request->is('miembro/suscripcion*') && 
                    !$request->is('arrendador/guardar-iban*')) {
                    return redirect()->route('arrendador.stripe.configurar')
                        ->with('info', '¡Suscripción activa! Ahora configura tus datos bancarios para recibir pagos.');
                }
            }
        }

        return $next($request);
    }
}
