<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\Contrato;
use App\Models\Pago;

class AlquilerController extends Controller
{
    /**
     * Mostrar listado de alquileres con KPI
     */
    public function index()
    {
        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario as inquilino', 'tbl_alquiler.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_usuario as arrendador', 'tbl_propiedad.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_alquiler.estado_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.precio_propiedad',
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador'
            )
            ->paginate(10);

        $activos = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'activo')
            ->count();

        $pendientes = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'pendiente')
            ->count();

        $rechazados = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'rechazado')
            ->count();

        $finalizanMes = DB::table('tbl_alquiler')
            ->whereMonth('fecha_fin_alquiler', Carbon::now()->month)
            ->whereYear('fecha_fin_alquiler', Carbon::now()->year)
            ->count();

        $propiedades = DB::table('tbl_propiedad')->get();
        $propiedadesPublicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->get();
        $inquilinos = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol', 'tbl_rol_usuario.id_rol_fk', '=', 'tbl_rol.id_rol')
            ->where('tbl_rol.nombre_rol', 'inquilino')
            ->select('tbl_usuario.id_usuario', 'tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario')
            ->get();

        return view('admin.alquileres', compact(
            'alquileres',
            'activos',
            'pendientes',
            'rechazados',
            'finalizanMes',
            'propiedades',
            'propiedadesPublicadas',
            'inquilinos'
        ));
    }

    /**
     * Obtener detalle de alquiler (JSON)
     */
    public function show($id)
    {
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario as inquilino', 'tbl_alquiler.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_usuario as arrendador', 'tbl_propiedad.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->select(
                'tbl_alquiler.*',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.precio_propiedad',
                'tbl_propiedad.foto_propiedad',
                'inquilino.nombre_usuario as nombre_usuario_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_usuario_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador'
            )
            ->where('tbl_alquiler.id_alquiler', $id)
            ->first();

        if (!$alquiler) {
            return response()->view('error.404');
        }

        // Generar initiales
        $partes = explode(' ', $alquiler->nombre_usuario_inquilino);
        $alquiler->inicialesInq = strtoupper($partes[0][0] . (isset($partes[1]) ? $partes[1][0] : ''));

        $partes = explode(' ', $alquiler->nombre_usuario_arrendador);
        $alquiler->inicialesArr = strtoupper($partes[0][0] . (isset($partes[1]) ? $partes[1][0] : ''));

        // Colores
        $colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7', '#CCE5FF', '#FDE8C8'];
        $alquiler->colorInq = $colores[$alquiler->id_inquilino_fk % 10];
        $alquiler->colorArr = $colores[$alquiler->id_propiedad_fk % 10];

        // Contrato
        $contrato = DB::table('tbl_contrato')
            ->where('id_alquiler_fk', $id)
            ->first();

        if (!$contrato) {
            $contrato = (object) [
                'firmado_arrendador' => false,
                'firmado_inquilino' => false,
                'estado_contrato' => 'pendiente'
            ];
        }

        // Pago (fianza)
        $pago = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $id)
            ->where('tipo_pago', 'fianza')
            ->first();

        if (!$pago) {
            $pago = (object) [
                'estado_pago' => 'pendiente',
                'importe_pago' => $alquiler->precio_propiedad * 2,
                'referencia_pago' => '—'
            ];
        }

        // Historial
        $historial = [];
        if ($alquiler->creado_alquiler) {
            $historial[] = [
                'comentario' => 'Solicitud de alquiler creada',
                'estado' => 'pendiente',
                'fecha' => Carbon::parse($alquiler->creado_alquiler)->format('Y-m-d H:i')
            ];
        }
        if ($alquiler->aprobado_alquiler) {
            $historial[] = [
                'comentario' => 'Alquiler aprobado',
                'estado' => 'aprobado',
                'fecha' => Carbon::parse($alquiler->aprobado_alquiler)->format('Y-m-d H:i')
            ];
        }

        return response()->json([
            'alquiler' => $alquiler,
            'contrato' => $contrato,
            'pago' => $pago,
            'historial' => $historial
        ]);
    }

    /**
     * Filtrar alquileres (read-only)
     */
    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario', 'tbl_alquiler.id_inquilino_fk', '=', 'tbl_usuario.id_usuario')
            ->where('tbl_alquiler.estado_alquiler', 'pendiente')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.estado_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_usuario.nombre_usuario'
            );

        if ($request->has('q') && $request->q) {
            $q = '%' . $request->q . '%';
            $query->where(function ($where) use ($q) {
                $where->orWhere('tbl_propiedad.titulo_propiedad', 'LIKE', $q)
                    ->orWhere('tbl_usuario.nombre_usuario', 'LIKE', $q);
            });
        }

        $alquileres = $query->get();

        return response()->json(['alquileres' => $alquileres]);
    }

    /**
     * Aprobar alquiler (TRANSACTION - touches 2 tables)
     */
    public function aprobar($id)
    {
        try {
            DB::beginTransaction();

            // Obtener el alquiler para saber la propiedad
            $alquiler = DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->first();

            if (!$alquiler) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => 'Alquiler no encontrado']);
            }

            // Actualizar alquiler a activo
            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->update([
                    'estado_alquiler' => 'activo',
                    'id_admin_aprueba_fk' => auth()->id(),
                    'aprobado_alquiler' => now()
                ]);

            // Actualizar propiedad a alquilada
            DB::table('tbl_propiedad')
                ->where('id_propiedad', $alquiler->id_propiedad_fk)
                ->update([
                    'estado_propiedad' => 'alquilada'
                ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Rechazar alquiler (single table, no transaction)
     */
    public function rechazar($id)
    {
        try {
            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->update([
                    'estado_alquiler' => 'rechazado'
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Crear nuevo alquiler (TRANSACTION - touches 2 tables)
     */
    public function crear(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validar datos
            $request->validate([
                'id_propiedad' => 'required|exists:tbl_propiedad,id_propiedad',
                'id_inquilino' => 'required|exists:tbl_usuario,id_usuario',
                'fecha_inicio' => 'required|date',
                'precio' => 'required|numeric|min:0'
            ]);

            // Crear alquiler
            $alquilerId = DB::table('tbl_alquiler')->insertGetId([
                'id_propiedad_fk' => $request->id_propiedad,
                'id_inquilino_fk' => $request->id_inquilino,
                'fecha_inicio_alquiler' => $request->fecha_inicio,
                'fecha_fin_alquiler' => $request->fecha_fin ?? null,
                'estado_alquiler' => 'pendiente',
                'aprobado_alquiler' => null,
                'creado_alquiler' => now(),
                'actualizado_alquiler' => now()
            ]);

            // Crear pago fianza
            $fianza = $request->precio * 2;
            DB::table('tbl_pago')->insert([
                'id_alquiler_fk' => $alquilerId,
                'id_pagador_fk' => $request->id_inquilino,
                'tipo_pago' => 'fianza',
                'importe_pago' => $fianza,
                'estado_pago' => 'pendiente',
                'referencia_pago' => 'FZ-' . $alquilerId . '-' . now()->format('Ymd'),
                'creado_pago' => now(),
                'actualizado_pago' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id_alquiler' => $alquilerId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
