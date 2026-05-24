<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (($docRoot = $_SERVER['DOCUMENT_ROOT'] ?? null) && realpath($docRoot) !== realpath($this->app->publicPath())) {
            $this->app->usePublicPath($docRoot);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $usuario = \Illuminate\Support\Facades\Auth::user();

                $nombre = $usuario->nombre_usuario ?? $usuario->name ?? $usuario->email_usuario ?? '';
                $tieneFoto = !empty($usuario->avatar_usuario) || !empty($usuario->foto_usuario);
                $foto = $usuario->avatar_usuario ?? $usuario->foto_usuario ?? '';

                // Coge la suscripción más reciente del usuario
                $suscripcion = $usuario->suscripciones()->latest('id_suscripcion')->first();
                
                // Si el plan es Gratuito muestra anuncios
                $mostrarAnuncios = $suscripcion?->plan_suscripcion === 'Gratuito';

                $view->with([
                    'nombreUsuario' => $nombre,
                    'tieneFoto' => $tieneFoto,
                    'fotoUsuario' => $tieneFoto ? (str_starts_with($foto, 'http') ? $foto : asset('storage/' . $foto)) : '',
                    'inicialUsuario' => $nombre !== '' ? strtoupper(substr($nombre, 0, 1)) : '',
                    'esInquilino' => $usuario->alquileres()->where('estado_alquiler', 'activo')->exists(),
                    'tienePagos' => $usuario->alquileres()->where('estado_alquiler', 'activo')->exists() || \Illuminate\Support\Facades\DB::table('tbl_pago')->where('id_pagador_fk', $usuario->id_usuario)->exists(),
                    'esArrendador' => $usuario->roles()->where('slug_rol', 'arrendador')->exists(),
                    'esGestor' => $usuario->roles()->where('slug_rol', 'gestor')->exists(),
                    'mostrarAnuncios' => $mostrarAnuncios,
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
                    'mostrarAnuncios' => true,
                ]);
            }
        });
    }
}
