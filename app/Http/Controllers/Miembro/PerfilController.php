<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function show($id)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if ((int) $id !== (int) $usuario->id_usuario) {
            return redirect('/miembro/inicio')->with('error', 'No puedes acceder a un perfil que no es tuyo.');
        }

        return view('miembro.perfil', $this->obtenerDatosVista($usuario));
    }

    public function configuracion()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        return view('miembro.configuracion', $this->obtenerDatosVista($usuario));
    }

    public function actualizar(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $datosValidados = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:100'],
            'email_usuario' => [
                'required',
                'email',
                'max:150',
                Rule::unique('tbl_usuario', 'email_usuario')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'telefono_usuario' => ['nullable', 'string', 'max:20'],
            'dni_usuario' => ['nullable', 'string', 'max:20'],
            'direccion_fiscal_usuario' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento_usuario' => ['nullable', 'date'],
            'avatar_usuario' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'nombre_usuario.required' => 'El nombre es obligatorio.',
            'email_usuario.required' => 'El correo electrónico es obligatorio.',
            'email_usuario.email' => 'El correo electrónico no es válido.',
            'email_usuario.unique' => 'Este correo electrónico ya está registrado.',
            'avatar_usuario.image' => 'El archivo debe ser una imagen.',
            'avatar_usuario.mimes' => 'La imagen debe ser JPEG, PNG, GIF o WebP.',
            'avatar_usuario.max' => 'La imagen no puede superar los 2MB.',
        ]);

        $datosValidados['nombre_usuario'] = trim($datosValidados['nombre_usuario']);
        $datosValidados['email_usuario'] = trim($datosValidados['email_usuario']);

        foreach (['telefono_usuario', 'dni_usuario', 'direccion_fiscal_usuario'] as $campoOpcional) {
            if (array_key_exists($campoOpcional, $datosValidados)) {
                $datosValidados[$campoOpcional] = filled($datosValidados[$campoOpcional])
                    ? trim((string) $datosValidados[$campoOpcional])
                    : null;
            }
        }

        if ($request->filled('contrasena_usuario')) {
            $request->validate([
                'contrasena_usuario' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[0-9]/'],
            ], [
                'contrasena_usuario.required' => 'La nueva contraseña es obligatoria.',
                'contrasena_usuario.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'contrasena_usuario.confirmed' => 'Las contraseñas no coinciden.',
                'contrasena_usuario.regex' => 'La contraseña debe contener al menos un número.',
            ]);

            $datosValidados['contrasena_usuario'] = Hash::make($request->contrasena_usuario);
        }

        if ($request->hasFile('avatar_usuario')) {
            $archivoAvatar = $request->file('avatar_usuario');
            $directorioAvatar = public_path('img/avatares/' . $usuario->id_usuario);

            if (!File::exists($directorioAvatar)) {
                File::makeDirectory($directorioAvatar, 0755, true);
            }

            $nombreArchivo = 'avatar_' . $usuario->id_usuario . '_' . time() . '.' . $archivoAvatar->getClientOriginalExtension();
            $rutaCompletaAvatar = $directorioAvatar . DIRECTORY_SEPARATOR . $nombreArchivo;
            File::put($rutaCompletaAvatar, file_get_contents($archivoAvatar->getRealPath()));

            $datosValidados['avatar_usuario'] = 'img/avatares/' . $usuario->id_usuario . '/' . $nombreArchivo;
        }

        $usuario->update($datosValidados);

        return redirect()->route('miembro.perfil.show', ['id' => $usuario->id_usuario])
            ->with('success', 'Tu perfil se ha actualizado correctamente.');
    }

    public function actualizarPlan(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $rolDestinatario = $this->obtenerRolDestinatario($usuario);
        abort_unless($rolDestinatario !== null, 403);

        $datosValidados = $request->validate([
            'id_plan' => [
                'required',
                'integer',
                Rule::exists('tbl_plan', 'id_plan')
                    ->where('activo_plan', true)
                    ->where('rol_destino', $rolDestinatario),
            ],
        ], [
            'id_plan.required' => 'Debes seleccionar un plan.',
            'id_plan.exists' => 'El plan seleccionado no está disponible.',
        ]);

        $plan = Plan::where('id_plan', $datosValidados['id_plan'])
            ->where('activo_plan', true)
            ->where('rol_destino', $rolDestinatario)
            ->firstOrFail();

        $suscripcionActual = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        DB::transaction(function () use ($usuario, $plan, $suscripcionActual) {
            $datosSuscripcion = [
                'id_plan_fk' => $plan->id_plan,
                'plan_suscripcion' => $plan->nombre_plan,
                'max_propiedades_suscripcion' => $plan->max_propiedades_plan,
                'precio_pagado_suscripcion' => $plan->precio_plan,
                'estado_suscripcion' => $plan->precio_plan > 0 ? 'pendiente_pago' : 'activa',
                'inicio_suscripcion' => Carbon::now(),
                'actualizado_suscripcion' => Carbon::now(),
            ];

            if ($plan->precio_plan > 0) {
                $datosSuscripcion['fin_suscripcion'] = Carbon::now()->copy()->addMonth();
            } else {
                $datosSuscripcion['fin_suscripcion'] = null;
            }

            if ($suscripcionActual) {
                $suscripcionActual->update($datosSuscripcion);
            } else {
                Suscripcion::create(array_merge($datosSuscripcion, [
                    'id_usuario_fk' => $usuario->id_usuario,
                    'creado_suscripcion' => Carbon::now(),
                ]));
            }

            $usuario->update([
                'stripe_status' => $plan->precio_plan > 0 ? 'pending_payment' : 'active',
            ]);
        });

        if ($plan->precio_plan > 0) {
            return redirect()->route('miembro.suscripcion.index')
                ->with('info', 'Has cambiado de plan. Completa el pago para activar la nueva suscripción.');
        }

        return redirect()->route('miembro.configuracion')
            ->with('success', 'Tu plan de suscripción se ha actualizado correctamente.');
    }

    public function cancelarSuscripcion(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $rolDestinatario = $this->obtenerRolDestinatario($usuario);
        abort_unless($rolDestinatario !== null, 403);

        $suscripcionActual = Suscripcion::with('plan')
            ->where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcionActual || (float) $suscripcionActual->precio_pagado_suscripcion <= 0) {
            return redirect()->route('miembro.configuracion')
                ->with('info', 'Ya tienes la suscripción gratuita activa.');
        }

        if ($suscripcionActual->estado_suscripcion === 'cancelada' && $suscripcionActual->fin_suscripcion) {
            return redirect()->route('miembro.configuracion')
                ->with('info', 'La cancelación ya está programada.');
        }

        // Calcular la fecha de renovación (fin de suscripción) desde la BD o pagos recientes
        $fechaPagoSuscripcion = DB::table('tbl_pago')
            ->where('id_pagador_fk', $usuario->id_usuario)
            ->where('tipo_pago', 'suscripcion')
            ->where('estado_pago', 'pagado')
            ->orderByDesc('fecha_confirmacion_pago')
            ->value('fecha_confirmacion_pago');

        $fechaFinSuscripcion = $suscripcionActual->fin_suscripcion
            ? Carbon::parse($suscripcionActual->fin_suscripcion)
            : ($fechaPagoSuscripcion
                ? Carbon::parse($fechaPagoSuscripcion)->copy()->addMonth()
                : ($suscripcionActual->inicio_suscripcion
                    ? Carbon::parse($suscripcionActual->inicio_suscripcion)->copy()->addMonth()
                    : null));

        // Si no existe una fecha fin calculable, usar fin de mes como respaldo
        $fechaProgramada = $fechaFinSuscripcion ?? Carbon::now()->endOfMonth();

        $suscripcionActual->update([
            'estado_suscripcion' => 'cancelada',
            'fin_suscripcion' => $fechaProgramada,
            'actualizado_suscripcion' => Carbon::now(),
        ]);

        return redirect()->route('miembro.configuracion')
            ->with('success', 'Tu suscripción se cancelará en la fecha de renovación y después volverá al plan gratuito.');
    }

    public function reactivarSuscripcion(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $rolDestinatario = $this->obtenerRolDestinatario($usuario);
        abort_unless($rolDestinatario !== null, 403);

        $suscripcionActual = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcionActual || $suscripcionActual->estado_suscripcion !== 'cancelada') {
            return redirect()->route('miembro.configuracion')
                ->with('info', 'No hay ninguna suscripción cancelada para reactivar.');
        }

        $suscripcionActual->update([
            'estado_suscripcion' => 'activa',
            'fin_suscripcion' => null,
            'actualizado_suscripcion' => Carbon::now(),
        ]);

        $usuario->update([
            'stripe_status' => 'active',
        ]);

        return redirect()->route('miembro.configuracion')
            ->with('success', 'Tu suscripción ha sido reactivada correctamente.');
    }

    private function obtenerDatosVista($usuario): array
    {
        $usuario->loadMissing('roles');

        $this->sincronizarSuscripcionVencida($usuario);

        $rolDestinatario = $this->obtenerRolDestinatario($usuario);
        $esArrendador = $rolDestinatario === 'arrendador';

        $suscripcionActual = Suscripcion::with('plan')
            ->where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        $diasRestantesSuscripcion = null;
        if ($suscripcionActual) {
            $fechaPagoSuscripcion = DB::table('tbl_pago')
                ->where('id_pagador_fk', $usuario->id_usuario)
                ->where('tipo_pago', 'suscripcion')
                ->where('estado_pago', 'pagado')
                ->orderByDesc('fecha_confirmacion_pago')
                ->value('fecha_confirmacion_pago');

            $fechaFinSuscripcion = $suscripcionActual->fin_suscripcion
                ? Carbon::parse($suscripcionActual->fin_suscripcion)
                : ($fechaPagoSuscripcion
                    ? Carbon::parse($fechaPagoSuscripcion)->copy()->addMonth()
                    : ($suscripcionActual->inicio_suscripcion
                        ? Carbon::parse($suscripcionActual->inicio_suscripcion)->copy()->addMonth()
                        : null));

            if ($fechaFinSuscripcion) {
                $diasRestantesSuscripcion = max(0, Carbon::now()->startOfDay()->diffInDays($fechaFinSuscripcion->startOfDay(), false));
            }
        }

        $planesDisponibles = collect();

        if ($rolDestinatario !== null) {
            $idPlanActual = $suscripcionActual?->id_plan_fk;

            // Considerar suscripción todavía vigente aunque esté marcada como 'cancelada'
            $precioPlanActual = null;
            if ($suscripcionActual) {
                $esVigentePorFecha = false;
                if ($suscripcionActual->estado_suscripcion === 'activa') {
                    $esVigentePorFecha = true;
                } elseif ($suscripcionActual->estado_suscripcion === 'cancelada' && $suscripcionActual->fin_suscripcion) {
                    $fin = Carbon::parse($suscripcionActual->fin_suscripcion);
                    if (Carbon::now()->lt($fin)) {
                        // Cancelada pero aún dentro del periodo pagado
                        $esVigentePorFecha = true;
                    }
                }

                if ($esVigentePorFecha) {
                    $precioPlanActual = (float) $suscripcionActual->precio_pagado_suscripcion;
                }
            }

            $planesDisponibles = Plan::where('activo_plan', true)
                ->where('rol_destino', $rolDestinatario)
                ->when($idPlanActual, function ($query) use ($idPlanActual) {
                    $query->where('id_plan', '!=', $idPlanActual);
                })
                ->when($precioPlanActual !== null, function ($query) use ($precioPlanActual) {
                    $query->where('precio_plan', '>=', $precioPlanActual);
                })
                ->orderBy('precio_plan')
                ->orderBy('id_plan')
                ->get();
        }

        $fotoPerfil = null;
        if (!empty($usuario->avatar_usuario)) {
            $fotoPerfil = $this->resolverRutaImagen($usuario->avatar_usuario);
        } elseif (!empty($usuario->foto_usuario)) {
            $fotoPerfil = $this->resolverRutaImagen($usuario->foto_usuario);
        }

        return compact('usuario', 'esArrendador', 'suscripcionActual', 'planesDisponibles', 'fotoPerfil', 'rolDestinatario', 'diasRestantesSuscripcion');
    }

    private function obtenerRolDestinatario(Usuario $usuario): ?string
    {
        if ($usuario->roles->contains(fn($rol) => $rol->slug_rol === 'arrendador')) {
            return 'arrendador';
        }

        if ($usuario->roles->contains(fn($rol) => $rol->slug_rol === 'inquilino')) {
            return 'miembro';
        }

        if ($usuario->roles->contains(fn($rol) => $rol->slug_rol === 'miembro')) {
            return 'miembro';
        }

        return null;
    }

    private function resolverRutaImagen(string $rutaImagen): string
    {
        if (str_starts_with($rutaImagen, 'http')) {
            return $rutaImagen;
        }

        if (str_starts_with($rutaImagen, 'public/img/')) {
            return asset(substr($rutaImagen, 7));
        }

        return asset(ltrim($rutaImagen, '/'));
    }

    private function sincronizarSuscripcionVencida(Usuario $usuario): void
    {
        $esMiembro = $usuario->roles->contains(fn($rol) => in_array($rol->slug_rol, ['miembro', 'inquilino'], true));

        if (!$esMiembro) {
            return;
        }

        $suscripcionActual = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcionActual) {
            return;
        }

        if ($suscripcionActual->estado_suscripcion !== 'cancelada') {
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
    }
}
