<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolicitudAlquilerController extends Controller
{
    public function store(Request $request, $id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->first();

        if (!$propiedad) {
            return redirect()->back()->with('error', 'La propiedad no existe.');
        }

        $usuario = Auth::user();

        if (!$usuario) {
            return redirect('/login');
        }

        DB::beginTransaction();

        try {
            $ahora = Carbon::now();

            $idRolInquilino = DB::table('tbl_rol')
                ->where('slug_rol', 'inquilino')
                ->value('id_rol');

            $debeCerrarSesion = false;

            if ($idRolInquilino) {
                $tieneRol = DB::table('tbl_rol_usuario')
                    ->where('id_usuario_fk', $usuario->id_usuario)
                    ->where('id_rol_fk', $idRolInquilino)
                    ->exists();

                if (!$tieneRol) {
                    DB::table('tbl_rol_usuario')->insert([
                        'id_usuario_fk' => $usuario->id_usuario,
                        'id_rol_fk' => $idRolInquilino,
                        'asignado_rol_usuario' => Carbon::now(),
                    ]);

                    $debeCerrarSesion = true;
                }
            }

            $existeAlquilerActivo = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $id)
                ->whereIn('estado_alquiler', ['pendiente', 'activo'])
                ->exists();

            if ($existeAlquilerActivo) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Esta propiedad ya tiene un alquiler activo o pendiente.');
            }

            $alquilerId = DB::table('tbl_alquiler')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_inquilino_fk' => $usuario->id_usuario,
                'fecha_inicio_alquiler' => Carbon::today()->toDateString(),
                'fecha_fin_alquiler' => null,
                'precio_alquiler' => $propiedad->precio_propiedad,
                'estado_alquiler' => 'activo',
                'aprobado_alquiler' => $ahora,
                'creado_alquiler' => $ahora,
                'actualizado_alquiler' => $ahora,
            ]);

            DB::table('tbl_pago')->insert([
                'id_alquiler_fk' => $alquilerId,
                'id_pagador_fk' => $usuario->id_usuario,
                'tipo_pago' => 'alquiler',
                'concepto_pago' => 'Pago inicial alquiler',
                'importe_pago' => $propiedad->precio_propiedad,
                'estado_pago' => 'pagado',
                'referencia_pago' => 'PI-' . $alquilerId . '-' . $ahora->format('YmdHis'),
                'fecha_confirmacion_pago' => $ahora,
                'creado_pago' => $ahora,
                'actualizado_pago' => $ahora,
            ]);

            DB::table('tbl_propiedad')
                ->where('id_propiedad', $id)
                ->update([
                    'estado_propiedad' => 'alquilada',
                ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'No se pudo completar el alquiler.');
        }

        if ($debeCerrarSesion) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login');
        }

        return redirect('/inquilino/gestionar-propiedades');
    }
}
