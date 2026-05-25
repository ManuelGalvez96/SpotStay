<?php

namespace App\Providers;

use App\Models\Plan;
use App\Models\Usuario;
use App\Models\Suscripcion;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (basename(base_path()) === 'laravel') {
            /** @var \Illuminate\Foundation\Application $application */
            $application = $this->app;
            $application->usePublicPath(dirname(base_path()));
        } elseif (($docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null) && realpath($docRoot) !== realpath($this->app->publicPath())) {
            /** @var \Illuminate\Foundation\Application $application */
            $application = $this->app;
            $application->usePublicPath($docRoot);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                /** @var Usuario $usuario */
                $usuario = \Illuminate\Support\Facades\Auth::user();

                $this->sincronizarSuscripcionVencida($usuario);

                $esGestor = $usuario->roles()->where('slug_rol', 'gestor')->exists();
                $notificacionesGestor = collect();
                $notificacionesGestorSinLeer = 0;

                // Notificaciones generales para cualquier usuario (miembro, arrendador, inquilino, gestor)
                $notificacionesUsuario = collect();
                $notificacionesUsuarioSinLeer = 0;

                $notificacionesQueryBase = DB::table('tbl_notificacion')
                    ->where('id_usuario_fk', $usuario->id_usuario);

                $notificacionesUsuarioSinLeer = (clone $notificacionesQueryBase)
                    ->where('leida_notificacion', false)
                    ->count();

                // Mostrar únicamente notificaciones sin leer en el dropdown
                $notificacionesUsuario = (clone $notificacionesQueryBase)
                    ->where('leida_notificacion', false)
                    ->select(
                        'id_notificacion',
                        'tipo_notificacion',
                        'titulo_notificacion',
                        'mensaje_notificacion',
                        'url_notificacion',
                        'icono_notificacion',
                        'color_notificacion',
                        'leida_notificacion',
                        'creado_notificacion',
                        'tipo_entidad_notificacion',
                        'id_entidad_notificacion'
                    )
                    ->orderBy('creado_notificacion', 'desc')
                    ->limit(6)
                    ->get();

                if ($esGestor) {
                    // keep existing gestor-specific variables for backward compatibility
                    $notificacionesGestor = $notificacionesUsuario;
                    $notificacionesGestorSinLeer = $notificacionesUsuarioSinLeer;
                }

                $nombre = $usuario->nombre_usuario ?? $usuario->name ?? $usuario->email_usuario ?? '';
                $tieneFoto = !empty($usuario->avatar_usuario) || !empty($usuario->foto_usuario);
                $foto = $usuario->avatar_usuario ?? $usuario->foto_usuario ?? '';
                $fotoUrl = '';

                if ($tieneFoto) {
                    if (str_starts_with($foto, 'http')) {
                        $fotoUrl = $foto;
                    } elseif (str_starts_with($foto, 'public/img/')) {
                        $fotoUrl = asset(substr($foto, 7));
                    } elseif (str_starts_with($foto, 'img/')) {
                        $fotoUrl = asset($foto);
                    } else {
                        $fotoUrl = asset('storage/' . ltrim($foto, '/'));
                    }
                }

                // Coge la suscripción más reciente del usuario
                $suscripcion = $usuario->suscripciones()->latest('id_suscripcion')->first();
                
                // Si el plan es Gratuito muestra anuncios
                $mostrarAnuncios = $suscripcion?->plan_suscripcion === 'Gratuito' || $suscripcion?->plan_suscripcion === "Miembro Estándar";
                $esGestorUsuario = $usuario->roles()->where('slug_rol', 'gestor')->exists()
                    || DB::table('tbl_propiedad')
                        ->where('id_gestor_fk', $usuario->id_usuario)
                        ->exists();

                $view->with([
                    'nombreUsuario' => $nombre,
                    'tieneFoto' => $tieneFoto,
                    'fotoUsuario' => $fotoUrl,
                    'inicialUsuario' => $nombre !== '' ? strtoupper(substr($nombre, 0, 1)) : '',
                    'esInquilino' => $usuario->alquileres()->where('estado_alquiler', 'activo')->exists(),
                    'tienePagos' => $usuario->alquileres()->where('estado_alquiler', 'activo')->exists() || \Illuminate\Support\Facades\DB::table('tbl_pago')->where('id_pagador_fk', $usuario->id_usuario)->exists(),
                    'esArrendador' => $usuario->roles()->where('slug_rol', 'arrendador')->exists(),
                    'notificacionesGestor' => $notificacionesGestor,
                    'notificacionesGestorSinLeer' => $notificacionesGestorSinLeer,
                    'notificacionesUsuario' => $notificacionesUsuario,
                    'notificacionesUsuarioSinLeer' => $notificacionesUsuarioSinLeer,
                    'mostrarAnuncios' => $mostrarAnuncios,
                    'esGestor' => $esGestorUsuario,
                ]);
            } else {
                $view->with([
                    'nombreUsuario' => '',
                    'tieneFoto' => false,
                    'fotoUsuario' => '',
                    'inicialUsuario' => '',
                    'esInquilino' => false,
                    'esArrendador' => false,
                    'esGestor' => false,
                    'notificacionesGestor' => collect(),
                    'notificacionesGestorSinLeer' => 0,
                    'notificacionesUsuario' => collect(),
                    'notificacionesUsuarioSinLeer' => 0,
                    'mostrarAnuncios' => true,
                ]);
            }
        });
    }

    private function sincronizarSuscripcionVencida(Usuario $usuario): void
    {
        $esMiembro = $usuario->roles()->whereIn('slug_rol', ['miembro', 'inquilino'])->exists();

        if (!$esMiembro) {
            return;
        }

        $suscripcionActual = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcionActual || $suscripcionActual->estado_suscripcion !== 'cancelada') {
            return;
        }

        if (!$suscripcionActual->fin_suscripcion || Carbon::now()->lt(Carbon::parse($suscripcionActual->fin_suscripcion))) {
            return;
        }

        $planGratuito = Plan::where('rol_destino', 'miembro')
            ->where('precio_plan', '<=', 0)
            ->where('activo_plan', true)
            ->orderBy('id_plan')
            ->first();

        if (!$planGratuito) {
            return;
        }

        DB::transaction(function () use ($usuario, $suscripcionActual, $planGratuito) {
            $suscripcionActual->update([
                'id_plan_fk' => $planGratuito->id_plan,
                'plan_suscripcion' => $planGratuito->nombre_plan,
                'max_propiedades_suscripcion' => $planGratuito->max_propiedades_plan,
                'precio_pagado_suscripcion' => $planGratuito->precio_plan,
                'inicio_suscripcion' => Carbon::now(),
                'fin_suscripcion' => null,
                'estado_suscripcion' => 'activa',
                'actualizado_suscripcion' => Carbon::now(),
            ]);

            $usuario->update([
                'stripe_status' => 'active',
            ]);
        });
    }
}
