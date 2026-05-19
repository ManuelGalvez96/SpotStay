<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IncidenciaController extends Controller
{
    public function inicio(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = DB::table('tbl_usuario')
            ->select('id_usuario', 'nombre_usuario')
            ->where('id_usuario', $arrendadorId)
            ->first();

        $query = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->join('tbl_usuario as reporta', 'reporta.id_usuario', '=', 'i.id_reporta_fk')
            ->leftJoin('tbl_usuario as asignado', 'asignado.id_usuario', '=', 'i.id_asignado_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'i.id_incidencia',
                'i.titulo_incidencia',
                'i.estado_incidencia',
                'i.prioridad_incidencia',
                'i.creado_incidencia',
                'i.presupuesto_importe_incidencia',
                'i.responsable_pago_incidencia',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'p.ciudad_propiedad',
                'reporta.nombre_usuario as nombre_reporta',
                'asignado.nombre_usuario as nombre_asignado'
            );

        $titulo = trim((string) $request->query('titulo', ''));
        $propiedad = trim((string) $request->query('propiedad', ''));
        $estado = (string) $request->query('estado', '');
        $prioridad = (string) $request->query('prioridad', '');
        $fecha = (string) $request->query('fecha', '');

        if ($titulo !== '') {
            $query->where('i.titulo_incidencia', 'like', '%' . $titulo . '%');
        }

        if ($propiedad !== '') {
            $query->where(function ($sub) use ($propiedad) {
                $sub->where('p.titulo_propiedad', 'like', '%' . $propiedad . '%')
                    ->orWhereRaw("CONCAT_WS(' ', p.calle_propiedad, p.numero_propiedad, p.piso_propiedad, p.puerta_propiedad) like ?", ['%' . $propiedad . '%'])
                    ->orWhere('p.ciudad_propiedad', 'like', '%' . $propiedad . '%');
            });
        }

        if (in_array($estado, ['abierta', 'en_proceso', 'esperando_decision', 'esperando_pago', 'resuelta', 'cerrada'], true)) {
            $query->where('i.estado_incidencia', $estado);
        }

        if (in_array($prioridad, ['alta', 'media', 'baja', 'urgente'], true)) {
            if ($prioridad === 'alta') {
                $query->whereIn('i.prioridad_incidencia', ['alta', 'urgente']);
            } else {
                $query->where('i.prioridad_incidencia', $prioridad);
            }
        }

        if ($fecha !== '') {
            $query->whereDate('i.creado_incidencia', $fecha);
        }

        $incidencias = $query
            ->orderByDesc('i.creado_incidencia')
            ->paginate(12)
            ->withQueryString();

        $totalIncidencias = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->count();

        $esperandoDecision = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('i.estado_incidencia', 'esperando_decision')
            ->count();

        $esperandoPago = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('i.estado_incidencia', 'esperando_pago')
            ->count();

        $resueltas = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->whereIn('i.estado_incidencia', ['resuelta', 'cerrada'])
            ->count();

        return view('arrendador.incidencias', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'incidencias' => $incidencias,
            'titulo' => $titulo,
            'propiedad' => $propiedad,
            'estado' => $estado,
            'prioridad' => $prioridad,
            'fecha' => $fecha,
            'totalIncidencias' => $totalIncidencias,
            'esperandoDecision' => $esperandoDecision,
            'esperandoPago' => $esperandoPago,
            'resueltas' => $resueltas,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $incidencia = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->join('tbl_usuario as reporta', 'reporta.id_usuario', '=', 'i.id_reporta_fk')
            ->leftJoin('tbl_usuario as asignado', 'asignado.id_usuario', '=', 'i.id_asignado_fk')
            ->where('i.id_incidencia', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'i.*',
                'p.id_propiedad',
                'p.id_arrendador_fk as id_arrendador_propiedad',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'p.ciudad_propiedad',
                'reporta.nombre_usuario as nombre_reporta',
                'reporta.email_usuario as email_reporta',
                'asignado.nombre_usuario as nombre_asignado'
            )
            ->first();

        if (!$incidencia) {
            abort(404);
        }

        $historial = DB::table('tbl_historial_incidencia as h')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 'h.id_usuario_fk')
            ->where('h.id_incidencia_fk', $id)
            ->select('h.*', 'u.nombre_usuario')
            ->orderBy('h.creado_historial', 'asc')
            ->get();

        $documentos = DB::table('tbl_documento')
            ->where('tipo_entidad_documento', 'incidencia')
            ->where('id_entidad_documento', $id)
            ->orderByDesc('creado_documento')
            ->get();

        $accionActual = 'sin_accion';
        if ($incidencia->estado_incidencia === 'esperando_decision') {
            $accionActual = 'esperando_decision';
        } elseif ($incidencia->estado_incidencia === 'esperando_pago') {
            $accionActual = 'esperando_pago';
        } elseif ($incidencia->estado_incidencia === 'resuelta') {
            $accionActual = 'resuelta';
        } elseif ($incidencia->estado_incidencia === 'cerrada') {
            $accionActual = 'cerrada';
        } elseif (in_array($incidencia->estado_incidencia, ['abierta', 'en_proceso'], true)) {
            $accionActual = 'en_seguimiento';
        }

        return view('arrendador.incidencia', [
            'arrendadorId' => $arrendadorId,
            'incidencia' => $incidencia,
            'historial' => $historial,
            'documentos' => $documentos,
            'accionActual' => $accionActual,
        ]);
    }

    public function decidirPago(Request $request, int $id): RedirectResponse
    {
        $datos = $request->validate([
            'responsable_pago' => 'required|in:arrendador,inquilino',
        ]);

        $arrendadorId = $this->obtenerIdArrendador($request);

        $incidencia = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->leftJoin('tbl_usuario as u', 'u.id_usuario', '=', 'p.id_arrendador_fk')
            ->where('i.id_incidencia', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'i.id_incidencia',
                'i.titulo_incidencia',
                'i.estado_incidencia',
                'i.presupuesto_importe_incidencia',
                'p.id_propiedad',
                'u.id_usuario as id_gestor'
            )
            ->first();

        if (!$incidencia) {
            return redirect()->back()->with('error', 'No se encontró la incidencia o no te pertenece.');
        }

        if ($incidencia->estado_incidencia !== 'esperando_decision') {
            return redirect()->back()->with('error', 'La incidencia ya no está pendiente de decisión.');
        }

        if (is_null($incidencia->presupuesto_importe_incidencia)) {
            return redirect()->back()->with('error', 'No hay presupuesto disponible para decidir.');
        }

        $responsablePago = $datos['responsable_pago'];
        $mensaje = $responsablePago === 'arrendador'
            ? 'El arrendador asume el pago del presupuesto.'
            : 'El inquilino asumirá el pago del presupuesto.';

        DB::transaction(function () use ($id, $arrendadorId, $incidencia, $responsablePago, $mensaje) {
            // Actualizar incidencia
            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'responsable_pago_incidencia' => $responsablePago,
                    'estado_incidencia' => 'esperando_pago',
                    'esperando_de_incidencia' => null,
                    'pagado_presupuesto_incidencia' => false,
                    'pagado_incidencia' => null,
                    'actualizado_incidencia' => Carbon::now(),
                ]);

            // Crear historial
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $id,
                'id_usuario_fk' => $arrendadorId,
                'comentario_historial' => $mensaje,
                'cambio_estado_historial' => 'esperando_pago',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now(),
            ]);

            // Crear gasto si la tabla existe
            if (Schema::hasTable('tbl_gasto') && Schema::hasTable('tbl_gasto_cuota') && Schema::hasTable('tbl_gasto_cuota_detalle')) {
                $this->crearGastoDesdeIncidencia($id, $incidencia, $responsablePago);
            }
        });

        return redirect()->back()->with('ok', 'Decisión guardada correctamente.');
    }

    public function pagarPresupuesto(Request $request, int $id): RedirectResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $incidencia = DB::table('tbl_incidencia as i')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
            ->where('i.id_incidencia', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'i.id_incidencia',
                'i.estado_incidencia',
                'i.responsable_pago_incidencia',
                'i.presupuesto_importe_incidencia'
            )
            ->first();

        if (!$incidencia) {
            return redirect()->back()->with('error', 'No se encontró la incidencia o no te pertenece.');
        }

        if ($incidencia->estado_incidencia !== 'esperando_pago') {
            return redirect()->back()->with('error', 'La incidencia no está pendiente de pago.');
        }

        if ($incidencia->responsable_pago_incidencia !== 'arrendador') {
            return redirect()->back()->with('error', 'Solo el arrendador puede pagar esta incidencia.');
        }

        DB::transaction(function () use ($id, $arrendadorId, $incidencia) {
            $ahora = Carbon::now();

            // Marcar gasto como pagado si existe
            if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
                $detalles = DB::table('tbl_gasto_cuota_detalle as d')
                    ->join('tbl_gasto_cuota as c', 'c.id_gasto_cuota', '=', 'd.id_gasto_cuota_fk')
                    ->join('tbl_gasto as g', 'g.id_gasto', '=', 'c.id_gasto_fk')
                    ->where('g.concepto_gasto', 'like', '%Incidencia #' . $id . '%')
                    ->where('d.estado_detalle', '<>', 'pagado')
                    ->select('d.id_gasto_cuota_detalle', 'd.id_gasto_cuota_fk', 'd.id_alquiler_fk')
                    ->get();

                $cuotasActualizadas = [];
                foreach ($detalles as $detalle) {
                    DB::table('tbl_gasto_cuota_detalle')
                        ->where('id_gasto_cuota_detalle', $detalle->id_gasto_cuota_detalle)
                        ->update([
                            'estado_detalle' => 'pagado',
                            'pagado_detalle' => $ahora,
                            'actualizado_detalle' => $ahora,
                        ]);

                    // Crear registro de pago si la tabla existe
                    if (Schema::hasTable('tbl_pago')) {
                        DB::table('tbl_pago')->insert([
                            'id_alquiler_fk' => $detalle->id_alquiler_fk,
                            'id_pagador_fk' => $arrendadorId,
                            'id_gasto_cuota_detalle_fk' => $detalle->id_gasto_cuota_detalle,
                            'id_gasto_cuota_fk' => $detalle->id_gasto_cuota_fk,
                            'tipo_pago' => 'gasto',
                            'concepto_pago' => 'Incidencia #' . $id,
                            'importe_pago' => $incidencia->presupuesto_importe_incidencia,
                            'estado_pago' => 'pagado',
                            'referencia_pago' => 'INC-' . $id . '-' . $ahora->format('YmdHis'),
                            'fecha_confirmacion_pago' => $ahora,
                            'creado_pago' => $ahora,
                            'actualizado_pago' => $ahora,
                        ]);
                    }

                    $cuotasActualizadas[] = $detalle->id_gasto_cuota_fk;
                }

                // Actualizar estado de cuotas
                foreach (array_unique($cuotasActualizadas) as $cuotaId) {
                    $this->actualizarEstadoCuota($cuotaId);
                }
            }

            // Marcar incidencia como pagada
            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'pagado_presupuesto_incidencia' => true,
                    'pagado_incidencia' => $ahora,
                    'estado_incidencia' => 'resuelta',
                    'actualizado_incidencia' => $ahora,
                ]);

            // Crear historial
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $id,
                'id_usuario_fk' => $arrendadorId,
                'comentario_historial' => 'El arrendador ha pagado el presupuesto de la incidencia.',
                'cambio_estado_historial' => 'resuelta',
                'creado_historial' => $ahora,
                'actualizado_historial' => $ahora,
            ]);
        });

        return redirect()->back()->with('ok', 'Pago registrado correctamente.');
    }

    private function crearGastoDesdeIncidencia(int $idIncidencia, mixed $incidencia, string $responsablePago): void
    {
        $ahora = Carbon::now();
        $mesActual = $ahora->format('Y-m-01');
        $vencimiento = $ahora->addDay(5)->format('Y-m-d');

        // Crear gasto
        $idGasto = DB::table('tbl_gasto')->insertGetId([
            'id_propiedad_fk' => $incidencia->id_propiedad,
            'id_gestor_fk' => $incidencia->id_gestor,
            'concepto_gasto' => 'Reparación: ' . $incidencia->titulo_incidencia,
            'categoria_gasto' => 'reparacion',
            'importe_estimado' => $incidencia->presupuesto_importe_incidencia,
            'ambito_gasto' => 'propiedad',
            'pagador_gasto' => $responsablePago,
            'periodicidad_gasto' => 'unica',
            'fecha_inicio_gasto' => $ahora->format('Y-m-d'),
            'estado_gasto' => 'activo',
            'creado_gasto' => $ahora,
            'actualizado_gasto' => $ahora,
        ]);

        // Crear cuota
        $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
            'id_gasto_fk' => $idGasto,
            'mes_cuota' => $mesActual,
            'vencimiento_cuota' => $vencimiento,
            'importe_total_cuota' => $incidencia->presupuesto_importe_incidencia,
            'estado_cuota' => 'pendiente',
            'creado_cuota' => $ahora,
            'actualizado_cuota' => $ahora,
        ]);

        // Obtener el pagador (inquilino o arrendador)
        $idPagador = $responsablePago === 'inquilino'
            ? $this->obtenerIdInquilinoDelIncidente($incidencia->id_propiedad)
            : $incidencia->id_gestor;

        // Crear detalle
        DB::table('tbl_gasto_cuota_detalle')->insert([
            'id_gasto_cuota_fk' => $idCuota,
            'id_alquiler_fk' => $this->obtenerIdAlquilerDelIncidente($incidencia->id_propiedad),
            'id_pagador_fk' => $idPagador,
            'importe_detalle' => $incidencia->presupuesto_importe_incidencia,
            'estado_detalle' => 'pendiente',
            'creado_detalle' => $ahora,
            'actualizado_detalle' => $ahora,
        ]);
    }

    private function obtenerIdInquilinoDelIncidente(int $idPropiedad): int
    {
        return (int) DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_propiedad', $idPropiedad)
            ->where('a.fecha_inicio_alquiler', '<=', Carbon::now())
            ->where(function ($query) {
                $query->whereNull('a.fecha_fin_alquiler')
                    ->orWhere('a.fecha_fin_alquiler', '>=', Carbon::now());
            })
            ->value('a.id_inquilino_fk') ?? 0;
    }

    private function obtenerIdAlquilerDelIncidente(int $idPropiedad): int
    {
        return (int) DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $idPropiedad)
            ->where('fecha_inicio_alquiler', '<=', Carbon::now())
            ->where(function ($query) {
                $query->whereNull('fecha_fin_alquiler')
                    ->orWhere('fecha_fin_alquiler', '>=', Carbon::now());
            })
            ->orderByDesc('fecha_inicio_alquiler')
            ->value('id_alquiler') ?? 0;
    }

    private function actualizarEstadoCuota(int $cuotaId): void
    {
        $detalles = DB::table('tbl_gasto_cuota_detalle')
            ->where('id_gasto_cuota_fk', $cuotaId)
            ->get();

        $total = $detalles->count();
        $pagados = $detalles->where('estado_detalle', 'pagado')->count();
        $vencimiento = DB::table('tbl_gasto_cuota')->where('id_gasto_cuota', $cuotaId)->value('vencimiento_cuota');

        $estado = 'pendiente';
        $pagadoCuota = null;

        if ($total > 0 && $pagados === $total) {
            $estado = 'pagado';
            $pagadoCuota = Carbon::now();
        } elseif ($pagados > 0) {
            $estado = 'parcial';
        } elseif ($vencimiento && Carbon::parse((string) $vencimiento)->isPast()) {
            $estado = 'atrasado';
        }

        DB::table('tbl_gasto_cuota')
            ->where('id_gasto_cuota', $cuotaId)
            ->update([
                'estado_cuota' => $estado,
                'pagado_cuota' => $pagadoCuota,
                'actualizado_cuota' => Carbon::now(),
            ]);
    }

    private function obtenerIdArrendador(Request $request): int
    {
        /** @var mixed $usuario */
        $usuario = Auth::user();
        if ($usuario && method_exists($usuario, 'roles') && $usuario->roles()->where('slug_rol', 'arrendador')->exists()) {
            return (int) ($usuario->id_usuario ?? $usuario->id ?? 0);
        }

        $arrendadorId = (int) $request->query('arrendador_id', 0);
        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        $arrendadorConActividad = DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select('u.id_usuario', DB::raw('COUNT(*) as total_propiedades'))
            ->orderByDesc('total_propiedades')
            ->orderBy('u.id_usuario', 'asc')
            ->value('u.id_usuario');

        if ($arrendadorConActividad) {
            return (int) $arrendadorConActividad;
        }

        $arrendadorConRol = DB::table('tbl_rol_usuario as ru')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 'ru.id_usuario_fk')
            ->where('r.slug_rol', 'arrendador')
            ->where('u.activo_usuario', true)
            ->orderBy('u.id_usuario', 'asc')
            ->value('u.id_usuario');

        if ($arrendadorConRol) {
            return (int) $arrendadorConRol;
        }

        $arrendadorDesdePropiedad = DB::table('tbl_propiedad')
            ->orderBy('id_propiedad', 'asc')
            ->value('id_arrendador_fk');

        return $arrendadorDesdePropiedad ? (int) $arrendadorDesdePropiedad : 0;
    }

    private function obtenerSelectDireccionPropiedad(string $aliasTabla = 'p'): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return "{$aliasTabla}.direccion_propiedad as direccion_propiedad";
        }

        $partes = [];
        foreach (['calle_propiedad', 'numero_propiedad', 'piso_propiedad', 'puerta_propiedad'] as $columna) {
            if (Schema::hasColumn('tbl_propiedad', $columna)) {
                $partes[] = "NULLIF(TRIM({$aliasTabla}.{$columna}), '')";
            }
        }

        if (empty($partes)) {
            return "'' as direccion_propiedad";
        }

        return 'TRIM(CONCAT_WS(\' \' , ' . implode(', ', $partes) . ')) as direccion_propiedad';
    }
}