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

        // 2. Verificar Suscripción Mensual (Para TODOS los roles)
        $tienePendientePago = \App\Models\Suscripcion::where('id_usuario_fk', $user->id_usuario)
            ->where('estado_suscripcion', 'pendiente_pago')
            ->exists();

        $ultimaSuscripcionActiva = \App\Models\Suscripcion::where('id_usuario_fk', $user->id_usuario)
            ->whereIn('estado_suscripcion', ['activa', 'cancelada'])
            ->latest('id_suscripcion')
            ->first();

        $suscripcionCaducada = $ultimaSuscripcionActiva && $ultimaSuscripcionActiva->fin_suscripcion && \Carbon\Carbon::parse($ultimaSuscripcionActiva->fin_suscripcion)->isPast();

        if ($suscripcionCaducada) {
            \Illuminate\Support\Facades\DB::transaction(function() use ($ultimaSuscripcionActiva, $user) {
                // Comprobar si hay una suscripción programada (downgrade diferido)
                $programada = \App\Models\Suscripcion::where('id_usuario_fk', $user->id_usuario)
                    ->where('estado_suscripcion', 'programada')
                    ->latest('id_suscripcion')
                    ->first();

                // Marcar la suscripción actual como caducada
                $ultimaSuscripcionActiva->update([
                    'estado_suscripcion' => 'caducada',
                    'actualizado_suscripcion' => \Carbon\Carbon::now()
                ]);

                if ($programada) {
                    // Activar la programada a pendiente de pago para Stripe
                    $programada->update([
                        'estado_suscripcion' => 'pendiente_pago',
                        'inicio_suscripcion' => \Carbon\Carbon::now(),
                        'fin_suscripcion' => \Carbon\Carbon::now()->copy()->addMonth(),
                        'actualizado_suscripcion' => \Carbon\Carbon::now()
                    ]);

                    DB::table('tbl_usuario')
                        ->where('id_usuario', $user->id_usuario)
                        ->update([
                            'stripe_status' => 'pending_payment'
                        ]);
                } else {
                    // Si es una suscripción caducada de 0€ (Miembro Estándar gratis), se renueva automáticamente y no bloquea
                    if ((float)$ultimaSuscripcionActiva->precio_pagado_suscripcion <= 0) {
                        \App\Models\Suscripcion::create([
                            'id_usuario_fk' => $user->id_usuario,
                            'id_plan_fk' => $ultimaSuscripcionActiva->id_plan_fk,
                            'plan_suscripcion' => $ultimaSuscripcionActiva->plan_suscripcion,
                            'max_propiedades_suscripcion' => $ultimaSuscripcionActiva->max_propiedades_suscripcion,
                            'precio_pagado_suscripcion' => 0.00,
                            'estado_suscripcion' => 'activa',
                            'inicio_suscripcion' => \Carbon\Carbon::now(),
                            'fin_suscripcion' => \Carbon\Carbon::now()->copy()->addMonth(),
                            'creado_suscripcion' => \Carbon\Carbon::now(),
                            'actualizado_suscripcion' => \Carbon\Carbon::now(),
                        ]);

                        DB::table('tbl_usuario')
                            ->where('id_usuario', $user->id_usuario)
                            ->update([
                                'stripe_status' => 'active'
                            ]);
                    } else {
                        // Suscripción de pago vencida, crear pendiente de pago del mismo plan para forzar pasarela
                        \App\Models\Suscripcion::create([
                            'id_usuario_fk' => $user->id_usuario,
                            'id_plan_fk' => $ultimaSuscripcionActiva->id_plan_fk,
                            'plan_suscripcion' => $ultimaSuscripcionActiva->plan_suscripcion,
                            'max_propiedades_suscripcion' => $ultimaSuscripcionActiva->max_propiedades_suscripcion,
                            'precio_pagado_suscripcion' => $ultimaSuscripcionActiva->precio_pagado_suscripcion,
                            'estado_suscripcion' => 'pendiente_pago',
                            'inicio_suscripcion' => \Carbon\Carbon::now(),
                            'fin_suscripcion' => \Carbon\Carbon::now()->copy()->addMonth(),
                            'creado_suscripcion' => \Carbon\Carbon::now(),
                            'actualizado_suscripcion' => \Carbon\Carbon::now(),
                        ]);

                        DB::table('tbl_usuario')
                            ->where('id_usuario', $user->id_usuario)
                            ->update([
                                'stripe_status' => 'expired'
                            ]);
                    }
                }
            });

            // Recargar suscripción activa tras transicionar
            $suscripcionCaducada = false;

            // Recargar estado de pendiente de pago
            $tienePendientePago = \App\Models\Suscripcion::where('id_usuario_fk', $user->id_usuario)
                ->where('estado_suscripcion', 'pendiente_pago')
                ->exists();
        }

        $esArrendador = DB::table('tbl_rol_usuario as ru')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('ru.id_usuario_fk', $user->id_usuario)
            ->where('r.slug_rol', 'arrendador')
            ->exists();

        // 2. Estas restricciones solo aplican al rol de 'arrendador'
        if ($esArrendador) {
            // Si es arrendador, la cuenta DEBE estar activa en stripe. Si es otro rol, solo bloquea si caducó o tiene pago pendiente
            if ($user->stripe_status !== 'active' || $tienePendientePago || $suscripcionCaducada) {
                if (!$request->is('miembro/suscripcion*')) {
                    $mensaje = ($tienePendientePago || $suscripcionCaducada)
                        ? 'Tienes un pago de suscripción pendiente. Por favor, completa tu suscripción para continuar.' 
                        : 'Para acceder a esta sección, primero debes activar tu suscripción mensual.';
                    return redirect()->route('miembro.suscripcion.index')
                        ->with('info', $mensaje);
                }
            }

            // 3. Verificar Cuenta de Cobros / IBAN (Solo para Arrendadores)
            if (!$user->stripe_account_id) {
                if (!$request->is('arrendador/configurar-stripe*') && 
                    !$request->is('miembro/suscripcion*') && 
                    !$request->is('arrendador/guardar-iban*')) {
                    return redirect()->route('arrendador.stripe.configurar')
                        ->with('info', '¡Suscripción activa! Ahora configura tus datos bancarios para recibir pagos.');
                }
            }
        } elseif ($tienePendientePago || $suscripcionCaducada) {
            if (!$request->is('miembro/suscripcion*')) {
                return redirect()->route('miembro.suscripcion.index')
                    ->with('info', 'Tienes un pago de suscripción pendiente. Por favor, completa tu suscripción para continuar.');
            }
        }

        return $next($request);
    }
}