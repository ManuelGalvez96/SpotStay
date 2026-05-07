<?php

namespace App\Http\Controllers\inquilino;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use App\Models\Pago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InquilinoController extends Controller
{
    public function gestionarPropiedades(Request $request)
    {
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        // ID del usuario autenticado
        $userId = $usuario->id_usuario;

        $this->actualizarCuotasAtrasadas($userId);

        // --- CONTROL DE ACCESO ---
        $alquileresActivosInquilino = DB::table('tbl_alquiler')
            ->where('id_inquilino_fk', $userId)
            ->where('estado_alquiler', 'activo')
            ->exists();

        $alquileresActivosPropietario = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_propiedad.id_arrendador_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->exists();

        if (!$alquileresActivosInquilino && !$alquileresActivosPropietario) {
            $urlRedirect = '/login';
            if ($usuario->roles()->where('slug_rol', 'admin')->exists()) {
                $urlRedirect = '/admin/dashboard';
            } elseif ($usuario->roles()->whereIn('slug_rol', ['miembro', 'inquilino', 'propietario'])->exists()) {
                $urlRedirect = '/miembro/inicio';
            }
            return redirect($urlRedirect)->with('error', 'Acceso restringido: <br>Solo inquilinos o propietarios con alquileres activos pueden acceder a esta sección.');
        }



        // 1. Contratos Activos (Total general para KPIs, no se filtra)
        $totalContratos = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($query) use ($userId) {
                $query->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->count(DB::raw('DISTINCT tbl_propiedad.id_propiedad'));

        // 2. Días para el próximo pago
        $proximoPago = AlquilerCuota::query()
            ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
            ->where('tbl_alquiler.id_inquilino_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->whereIn('tbl_alquiler_cuota.estado', ['pendiente', 'atrasado'])
            ->orderBy('tbl_alquiler_cuota.mes_cuota', 'asc')
            ->select('tbl_alquiler_cuota.mes_cuota')
            ->first();

        if ($proximoPago && $proximoPago->mes_cuota) {
            $fechaPago = Carbon::parse($proximoPago->mes_cuota)->day(1);
            $diasParaPago = Carbon::now()->diffInDays($fechaPago, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : round($diasParaPago);
        } else {
            $fechaPago = Carbon::now()->addMonth()->day(1);
            $diasParaPago = round(Carbon::now()->diffInDays($fechaPago));
        }

        // 3. Incidencias Totales Activas (de las propiedades del usuario)
        $totalIncidencias = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->leftJoin('tbl_alquiler', function ($join) use ($userId) {
                $join->on('tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
                    ->where('tbl_alquiler.id_inquilino_fk', '=', $userId)
                    ->where('tbl_alquiler.estado_alquiler', '=', 'activo');
            })
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
            ->where(function ($query) use ($userId) {
                $query->where('tbl_propiedad.id_arrendador_fk', $userId)
                    ->orWhereNotNull('tbl_alquiler.id_alquiler');
            })
            ->count(DB::raw('DISTINCT tbl_incidencia.id_incidencia'));

        // 4. Listado de Propiedades Únicas (FILTRADO)
        $query = DB::table('tbl_propiedad')
            ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->leftJoin('tbl_fotos', function ($join) {
                $join->on('tbl_fotos.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
                    ->whereRaw('tbl_fotos.id_foto = (select min(id_foto) from tbl_fotos where id_propiedad_fk = tbl_propiedad.id_propiedad)');
            })
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($qb) use ($userId) {
                $qb->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            // Excluir contratos cuya fecha de fin ya ha pasado más de 7 días
            ->where(function ($qb) {
                $qb->whereNull('tbl_alquiler.fecha_fin_alquiler')
                    ->orWhereRaw('tbl_alquiler.fecha_fin_alquiler >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
            });

        // Aplicar filtros dinámicos
        if ($request->filled('q')) {
            $query->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('ciudad')) {
            $query->where('tbl_propiedad.ciudad_propiedad', $request->ciudad);
        }

        $alquileres = $query->select(
            'tbl_propiedad.*',
            DB::raw("TRIM(CONCAT_WS(', ', 
                TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), 
                NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), 
                NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta ')
            )) as direccion_propiedad"),
            DB::raw('MIN(tbl_fotos.ruta_foto) as ruta_foto'),
            DB::raw('MIN(tbl_alquiler.id_alquiler) as id_alquiler'),
            DB::raw('MIN(tbl_alquiler.estado_alquiler) as estado_alquiler'),
            DB::raw('MIN(tbl_alquiler.fecha_inicio_alquiler) as fecha_inicio_alquiler'),
            DB::raw('MIN(CASE WHEN tbl_alquiler.id_inquilino_fk = ' . $userId . ' THEN tbl_alquiler.fecha_fin_alquiler END) as fecha_fin_alquiler'),
            DB::raw('(SELECT COUNT(*) FROM tbl_incidencia WHERE id_propiedad_fk = tbl_propiedad.id_propiedad AND estado_incidencia IN ("abierta", "en_proceso")) as total_incidencias_propiedad')
        )
            ->groupBy('tbl_propiedad.id_propiedad')
            ->get();

        // 4.5. Calcular datos de alerta fin de contrato para cada alquiler en el grid
        $hoy = \Carbon\Carbon::today();
        $ahora = \Carbon\Carbon::now();
        foreach ($alquileres as $alquiler) {
            $alquiler->nombres_companeros = DB::table('tbl_alquiler')
                ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')
                ->where('tbl_alquiler.id_propiedad_fk', $alquiler->id_propiedad)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->where('tbl_alquiler.id_inquilino_fk', '<>', $userId)
                ->pluck('tbl_usuario.nombre_usuario')
                ->toArray();

            $alquiler->mostrarAlertaFin = false;
            $alquiler->diasFinContrato = null;
            $alquiler->esMismoDia = false;
            $alquiler->tiempoRestanteHoy = null;
            $alquiler->banner_foto_url = $alquiler->ruta_foto
                ? asset('storage/' . $alquiler->ruta_foto)
                : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
            $alquiler->estado_pago_actual = 'pagado';
            $alquiler->dias_para_pago = 0;
            $alquiler->fecha_proximo_pago = null;
            $alquiler->num_pagos_atrasados = 0;
            $alquiler->total_deuda = 0;
            $alquiler->cuota_pendiente_id = null;
            $alquiler->pago_atrasado = 0;
            $alquiler->haExpirado = false;
            $alquiler->diasExpirado = null;
            $alquiler->num_gastos_pendientes = 0;

            if (!empty($alquiler->id_alquiler)) {
                $resumenPago = $this->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);
                $alquiler->estado_pago_actual = $resumenPago['estado_pago_actual'];
                $alquiler->dias_para_pago = $resumenPago['dias_para_pago'];
                $alquiler->fecha_proximo_pago = $resumenPago['fecha_proximo_pago'];
                $alquiler->num_pagos_atrasados = $resumenPago['num_pagos_atrasados'];
                $alquiler->total_deuda = $resumenPago['total_deuda'];
                $alquiler->cuota_pendiente_id = $resumenPago['cuota_pendiente_id'];
                $alquiler->pago_atrasado = $resumenPago['num_pagos_atrasados'];

                // Contar suministros pendientes para el grid
                if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
                    $alquiler->num_gastos_pendientes = DB::table('tbl_gasto_cuota_detalle')
                        ->where('id_alquiler_fk', $alquiler->id_alquiler)
                        ->where('id_pagador_fk', $userId)
                        ->whereIn('estado_detalle', ['pendiente', 'atrasado'])
                        ->count();
                }
            }

            if (!empty($alquiler->fecha_fin_alquiler)) {
                $fin = \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->startOfDay();

                if ($fin->format('Y-m-d') === $hoy->format('Y-m-d')) {
                    $alquiler->mostrarAlertaFin = true;
                    $alquiler->diasFinContrato = 0;
                } elseif ($fin->gt($hoy)) {
                    $dias = (int) $hoy->diffInDays($fin);
                    $alquiler->diasFinContrato = $dias;
                    $alquiler->mostrarAlertaFin = $dias <= 30;
                } else {
                    $alquiler->haExpirado = true;
                    $alquiler->diasExpirado = abs((int) $hoy->diffInDays($fin, false));
                    $alquiler->mostrarAlertaFin = true;
                }
            }
        }

        // 5. Obtener ciudades únicas para el filtro a partir de las propiedades visibles
        $ciudades = $alquileres
            ->pluck('ciudad_propiedad')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Si es una petición AJAX (Fetch), devolver solo el grid
        if ($request->ajax()) {
            return view('inquilino.partials.grid_propiedades', compact('alquileres'))->render();
        }

        return view('inquilino.gestionar_propiedades', [
            'totalContratos' => $totalContratos,
            'diasParaPago' => $diasParaPago,
            'totalIncidencias' => $totalIncidencias,
            'alquileres' => $alquileres,
            'ciudades' => $ciudades
        ]);
    }

    public function verPropiedad($id)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $userId = $usuario->id_usuario;

        $this->actualizarCuotasAtrasadas($userId);

        // 1. Obtener el alquiler activo para esta propiedad y usuario (inquilino o propietario)
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->leftJoin('tbl_contrato', 'tbl_contrato.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->leftJoin('tbl_usuario as propietario', 'propietario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->where('tbl_alquiler.id_propiedad_fk', $id)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($query) use ($userId) {
                $query->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->select(
                'tbl_alquiler.*',
                'tbl_propiedad.*',
                DB::raw("TRIM(CONCAT_WS(', ', 
                    TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), 
                    NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), 
                    NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta ')
                )) as direccion_propiedad"),
                'tbl_contrato.url_pdf_contrato',
                'tbl_contrato.estado_contrato as estado_contrato_pdf',
                'propietario.nombre_usuario as nombre_propietario'
            )
            ->first();

        if (!$alquiler) {
            return redirect()->route('gestionar_propiedades')->with('error', 'No tienes un alquiler activo para esta propiedad.');
        }



        // 2. Fotos de la propiedad
        $fotos = DB::table('tbl_fotos')
            ->where('id_propiedad_fk', $id)
            ->get();

        $fotos = $fotos->map(function ($foto) {
            $foto->url_foto = asset('public/img/' . $foto->ruta_foto);
            return $foto;
        });

        $fotoPrincipal = $fotos->isNotEmpty() ? $fotos->first()->url_foto : null;

        // 3. Detectar si el contrato finaliza en menos de 30 días
        $proximaFinalizacion = false;
        $diasParaFinContrato = null;
        $fechaFinContrato    = null;
        $esIndefinido        = false;
        $diasRestantesMes    = null;
        $estadoPagoActual    = 'pendiente';

        $hoy = Carbon::today();

        if (!empty($alquiler->fecha_fin_alquiler)) {
            $finContrato = Carbon::parse($alquiler->fecha_fin_alquiler)->startOfDay();

            if ($finContrato->format('Y-m-d') === $hoy->format('Y-m-d')) {
                $proximaFinalizacion = true;
                $diasParaFinContrato = 0;
                $fechaFinContrato    = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            } elseif ($finContrato->gt($hoy)) {
                $diasParaFinContrato = (int) $hoy->diffInDays($finContrato);
                if ($diasParaFinContrato <= 30) {
                    $proximaFinalizacion = true;
                    $fechaFinContrato    = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                }
            } else {
                // Ha expirado (fin en el pasado)
                $proximaFinalizacion = true;
                $diasParaFinContrato = -1 * (int) $finContrato->diffInDays($hoy);
                $fechaFinContrato    = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            }
        } else {
            // CONTRATO INDEFINIDO
            $esIndefinido = true;
            $finDeMes = $hoy->copy()->endOfMonth()->startOfDay();
            $diasRestantesMes = (int) $hoy->diffInDays($finDeMes);
        }

        // Compañeros de piso (excluyendo al usuario actual)
        $companeros = DB::table('tbl_alquiler')
            ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')
            ->where('tbl_alquiler.id_propiedad_fk', $alquiler->id_propiedad_fk)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler.id_inquilino_fk', '<>', $userId)
            ->pluck('tbl_usuario.nombre_usuario')
            ->toArray();

        // 4. Próximo pago (basado en cuotas de alquiler)
        $resumenPago = $this->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);
        $estadoPagoActual = $resumenPago['estado_pago_actual'];
        $diasParaPago = $resumenPago['dias_para_pago'];
        $fechaProximoPago = $resumenPago['fecha_proximo_pago'];
        $numPagosAtrasados = $resumenPago['num_pagos_atrasados'];
        $totalDeuda = $resumenPago['total_deuda'];
        $cuotaPendienteId = $resumenPago['cuota_pendiente_id'];
        $montoCuotaActual = (float) ($resumenPago['cuota_pendiente_importe'] ?? 0);

        // 4. Incidencias (Todas las de la propiedad)
        $incidencias = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id)
            ->orderBy('creado_incidencia', 'desc')
            ->get();

        // 5. Historial de Pagos
        $historialPagos = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $alquiler->id_alquiler)
            ->where('id_pagador_fk', $userId)
            ->orderBy('creado_pago', 'desc')
            ->get();

        // 6. Gastos Extras (Suministros: Agua, Luz, etc.)
        $totalGastosPendientes = 0;
        $numGastosPendientes = 0;
        $conceptosGastos = "";

        // Contamos inquilinos activos en la propiedad para dividir
        $numInquilinos = DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $alquiler->id_propiedad_fk)
            ->where('estado_alquiler', 'activo')
            ->count();
        $numInquilinos = $numInquilinos > 0 ? $numInquilinos : 1;

        if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
            $consultaGastos = DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', $alquiler->id_alquiler)
                ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $userId)
                ->whereIn('tbl_gasto_cuota_detalle.estado_detalle', ['pendiente', 'atrasado']);

            $totalGastosPendientes = (float) $consultaGastos->sum('tbl_gasto_cuota_detalle.importe_detalle');
            $numGastosPendientes = $consultaGastos->count();
            $listaGastos = $consultaGastos->select('tbl_gasto.categoria_gasto', 'tbl_gasto.concepto_gasto', 'tbl_gasto_cuota_detalle.importe_detalle')->get();

            // Obtenemos los nombres de los servicios para el SweetAlert
            $nombresServicios = $listaGastos->pluck('concepto_gasto')->unique()->toArray();
            $conceptosGastos = implode(", ", $nombresServicios);
        } else {
            $listaGastos = collect();
        }

        // Aplicamos la división si hay varios inquilinos
        if ($numInquilinos > 1) {
            $totalDeuda = $totalDeuda / $numInquilinos;
            $totalGastosPendientes = $totalGastosPendientes / $numInquilinos;
            $montoCuotaActual = $montoCuotaActual / $numInquilinos;
        }

        // 5. Historial de Pagos desglosado
        $historialAlquiler = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $alquiler->id_alquiler)
            ->where('id_pagador_fk', $userId)
            ->where('tipo_pago', 'alquiler')
            ->orderBy('creado_pago', 'desc')
            ->get();

        $historialGastos = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $alquiler->id_alquiler)
            ->where('id_pagador_fk', $userId)
            ->where('tipo_pago', 'gasto')
            ->orderBy('creado_pago', 'desc')
            ->get();

        return view('inquilino.ver_propiedad', [
            'alquiler'            => $alquiler,
            'fotos'               => $fotos,
            'fotoPrincipal'       => $fotoPrincipal,
            'diasParaPago'        => $diasParaPago,
            'fechaProximoPago'    => $fechaProximoPago,
            'proximaFinalizacion' => $proximaFinalizacion,
            'diasParaFinContrato' => $diasParaFinContrato,
            'fechaFinContrato'    => $fechaFinContrato,
            'esIndefinido'        => $esIndefinido,
            'diasRestantesMes'    => $diasRestantesMes,
            'estadoPagoActual'    => $estadoPagoActual,
            'numPagosAtrasados'   => $numPagosAtrasados,
            'totalDeuda'          => $totalDeuda,
            'montoCuotaActual'    => $montoCuotaActual,
            'cuotaPendienteId'    => $cuotaPendienteId,
            'totalGastosPendientes' => $totalGastosPendientes,
            'numGastosPendientes'   => $numGastosPendientes,
            'listaGastos'           => $listaGastos,
            'conceptosGastos'     => $conceptosGastos,
            'numInquilinos'       => $numInquilinos,
            'companeros'          => $companeros,
            'historialAlquiler'   => $historialAlquiler,
            'historialGastos'     => $historialGastos,
            'esInquilino'         => true,
            'pdfEjemplo'          => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
        ]);
    }

    public function obtenerEstadoContrato($id)
    {
        $usuario = Auth::user();
        if (!$usuario) return response()->json(['error' => 'No autorizado'], 401);

        $alquiler = DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->first();

        if (!$alquiler) return response()->json(['error' => 'Alquiler no encontrado'], 404);

        $hoy = Carbon::now();
        $fechaFin = !empty($alquiler->fecha_fin_alquiler) ? Carbon::parse($alquiler->fecha_fin_alquiler)->endOfDay() : null;
        
        $datos = [
            'es_indefinido' => empty($fechaFin),
            'expirado' => false,
            'dias_exceso' => 0,
            'semana_excedida' => false,
            'mensaje' => ''
        ];

        if ($fechaFin) {
            $datos['expirado'] = $hoy->gt($fechaFin);
            if ($datos['expirado']) {
                $datos['dias_exceso'] = (int) $fechaFin->diffInDays($hoy);
                $datos['semana_excedida'] = $datos['dias_exceso'] >= 7;
                
                if ($datos['semana_excedida']) {
                    $datos['mensaje'] = "⚠️ Alerta: Has superado el plazo de una semana tras el fin de contrato.";
                } else {
                    $datos['mensaje'] = "Contrato finalizado hace " . $datos['dias_exceso'] . " días.";
                }
            }
        }

        return response()->json($datos);
    }

    public function reportarIncidencia(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria' => 'required|string',
            'prioridad' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Obtener el gestor de la propiedad (o el arrendador si no hay gestor)
            $propiedad = DB::table('tbl_propiedad')
                ->where('id_propiedad', $id)
                ->select('id_gestor_fk', 'id_arrendador_fk')
                ->first();

            $idAsignado = null;
            if ($propiedad) {
                $idAsignado = (int) ($propiedad->id_gestor_fk ?? 0);
                if ($idAsignado <= 0) {
                    $idAsignado = (int) ($propiedad->id_arrendador_fk ?? 0);
                }
                $idAsignado = $idAsignado > 0 ? $idAsignado : null;
            }

            // 1. Crear la incidencia
            $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_reporta_fk' => $usuario->id_usuario,
                'id_asignado_fk' => $idAsignado,
                'titulo_incidencia' => $request->titulo,
                'descripcion_incidencia' => $request->descripcion,
                'categoria_incidencia' => $request->categoria,
                'prioridad_incidencia' => $request->prioridad,
                'estado_incidencia' => 'abierta',
                'creado_incidencia' => Carbon::now(),
                'actualizado_incidencia' => Carbon::now()
            ]);

            // 2. Crear el primer registro en el historial
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $idIncidencia,
                'id_usuario_fk' => $usuario->id_usuario,
                'comentario_historial' => 'Incidencia reportada por el inquilino/propietario.',
                'cambio_estado_historial' => 'abierta',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Incidencia reportada correctamente.'
                ]);
            }

            return redirect()->back()->with('success', 'Incidencia reportada correctamente. Se ha añadido al listado.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al reportar: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al reportar la incidencia: ' . $e->getMessage());
        }
    }

    public function getIncidencias(Request $request, $id)
    {
        $estado = $request->query('estado', 'todas');
        $autor = $request->query('autor', 'todas');

        $query = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id);

        if ($estado !== 'todas') {
            $query->where('estado_incidencia', $estado);
        }

        if ($autor === 'mias') {
            $query->where('id_reporta_fk', auth()->id());
        }

        $incidencias = $query->orderBy('creado_incidencia', 'desc')->get();

        return response()->json($incidencias->map(function ($inc) {
            return [
                'id' => $inc->id_incidencia,
                'titulo' => $inc->titulo_incidencia,
                'fecha' => Carbon::parse($inc->creado_incidencia)->format('d/m/Y'),
                'estado' => $inc->estado_incidencia,
                'estado_texto' => ucfirst(str_replace('_', ' ', $inc->estado_incidencia)),
                'id_reporta' => $inc->id_reporta_fk,
                'auth_id' => auth()->id()
            ];
        }));
    }

    public function getDetalleIncidencia($id)
    {
        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return response()->json(['error' => 'Incidencia no encontrada'], 404);
        }

        return response()->json([
            'id' => $incidencia->id_incidencia,
            'titulo' => $incidencia->titulo_incidencia,
            'descripcion' => $incidencia->descripcion_incidencia,
            'categoria' => ucfirst(str_replace('_', ' ', $incidencia->categoria_incidencia ?? 'N/A')),
            'prioridad' => ucfirst($incidencia->prioridad_incidencia ?? 'N/A'),
            'estado' => ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia ?? 'N/A')),
            'fecha' => Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y H:i')
        ]);
    }

    public function obtenerEstadosIncidencias()
    {
        // Estados disponibles en el sistema (completos)
        $estados = [
            'abierta' => 'Abiertas',
            'en_proceso' => 'En proceso',
            'esperando_decision' => 'Esperando decisión',
            'esperando_pago' => 'Esperando pago',
            'resuelta' => 'Resueltas',
            'cerrada' => 'Cerradas'
        ];

        return response()->json([
            'success' => true,
            'estados' => $estados
        ]);
    }


    public function pagarCuotaAlquiler(int $cuotaId)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Sesión expirada.'], 401);
        }

        $userId = (int) ($usuario->id_usuario ?? 0);
        $tipoPago = request()->query('tipo'); // 'alquiler' o 'gasto'

        try {
            DB::beginTransaction();

            // Buscamos la cuota de referencia para saber de qué alquiler hablamos
            $cuotaReferencia = AlquilerCuota::find($cuotaId);

            // Si es un pago de gasto y no viene cuotaId (o es 0), buscamos el alquiler activo
            $idAlquiler = $cuotaReferencia ? $cuotaReferencia->id_alquiler_fk : null;

            if (!$idAlquiler) {
                $idAlquiler = DB::table('tbl_alquiler')
                    ->where('id_inquilino_fk', $userId)
                    ->where('estado_alquiler', 'activo')
                    ->value('id_alquiler');
            }

            if (!$idAlquiler) {
                throw new \Exception('No se pudo identificar el alquiler asociado.');
            }

            $ahora = now();

            // Contamos inquilinos activos de la propiedad para dividir
            $alquilerActivo = DB::table('tbl_alquiler')->where('id_alquiler', $idAlquiler)->first();
            $numInquilinos = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $alquilerActivo->id_propiedad_fk)
                ->where('estado_alquiler', 'activo')
                ->count();
            $numInquilinos = $numInquilinos > 0 ? $numInquilinos : 1;

            // 1. Procesar Alquiler
            if (!$tipoPago || $tipoPago === 'alquiler') {
                // Buscamos SOLO la cuota específica que se ha solicitado pagar
                $cuotasAPagar = AlquilerCuota::where('id_alquiler_fk', $idAlquiler)
                    ->where('id_alquiler_cuota', $cuotaId)
                    ->whereIn('estado', ['pendiente', 'atrasado'])
                    ->get();

                foreach ($cuotasAPagar as $cuota) {
                    $importeDividido = (float) $cuota->importe_base;

                    // DIVISIÓN: Si el importe base es el total del piso, dividimos.
                    if ($numInquilinos > 1) {
                        $importeDividido = $importeDividido / $numInquilinos;
                    }

                    Pago::create([
                        'id_pagador_fk' => $userId,
                        'id_alquiler_fk' => $idAlquiler,
                        'id_alquiler_cuota_fk' => $cuota->id_alquiler_cuota,
                        'tipo_pago' => 'alquiler',
                        'concepto_pago' => 'Cuota alquiler ' . Carbon::parse((string) $cuota->mes_cuota)->format('m/Y'),
                        'importe_pago' => $importeDividido,
                        'estado_pago' => 'pagado',
                        'referencia_pago' => 'ALQ-' . $cuota->id_alquiler_cuota . '-' . $ahora->format('YmdHis'),
                        'fecha_confirmacion_pago' => $ahora,
                        'creado_pago' => $ahora,
                        'actualizado_pago' => $ahora,
                    ]);

                    $cuota->update([
                        'estado' => 'pagado',
                        'pagado_en' => $ahora,
                    ]);
                }
            }

            // 2. Procesar Gastos
            if (!$tipoPago || $tipoPago === 'gasto') {
                if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
                    $gastosAPagar = DB::table('tbl_gasto_cuota_detalle')
                        ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                        ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                        ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', $idAlquiler)
                        ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $userId)
                        ->whereIn('tbl_gasto_cuota_detalle.estado_detalle', ['pendiente', 'atrasado'])
                        ->select('tbl_gasto_cuota_detalle.*', 'tbl_gasto.concepto_gasto', 'tbl_gasto.categoria_gasto')
                        ->get();

                    foreach ($gastosAPagar as $gasto) {
                        $importeGastoDividido = (float) $gasto->importe_detalle;

                        $conceptoFinal = ucfirst($gasto->categoria_gasto);
                        if (!empty($gasto->concepto_gasto)) {
                            $conceptoFinal .= " (" . $gasto->concepto_gasto . ")";
                        }

                        Pago::create([
                            'id_pagador_fk' => $userId,
                            'id_alquiler_fk' => $idAlquiler,
                            'id_gasto_cuota_detalle_fk' => $gasto->id_gasto_cuota_detalle,
                            'tipo_pago' => 'gasto',
                            'concepto_pago' => $conceptoFinal,
                            'importe_pago' => $importeGastoDividido,
                            'estado_pago' => 'pagado',
                            'referencia_pago' => 'GST-' . $gasto->id_gasto_cuota_detalle . '-' . $ahora->format('YmdHis'),
                            'fecha_confirmacion_pago' => $ahora,
                            'creado_pago' => $ahora,
                            'actualizado_pago' => $ahora,
                        ]);

                        DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_detalle', $gasto->id_gasto_cuota_detalle)
                            ->update([
                                'estado_detalle' => 'pagado',
                                'actualizado_detalle' => $ahora
                            ]);

                        // Cierre de cuota principal si todos han pagado
                        $pendientes = DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_fk', $gasto->id_gasto_cuota_fk)
                            ->where('estado_detalle', '<>', 'pagado')
                            ->count();

                        if ($pendientes === 0) {
                            DB::table('tbl_gasto_cuota')
                                ->where('id_gasto_cuota', $gasto->id_gasto_cuota_fk)
                                ->update(['estado_cuota' => 'pagado', 'actualizado_cuota' => $ahora]);
                        } else {
                            DB::table('tbl_gasto_cuota')
                                ->where('id_gasto_cuota', $gasto->id_gasto_cuota_fk)
                                ->update(['estado_cuota' => 'parcial', 'actualizado_cuota' => $ahora]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pagos procesados correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function actualizarCuotasAtrasadas(int $userId): void
    {
        AlquilerCuota::query()
            ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
            ->where('tbl_alquiler.id_inquilino_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pendiente')
            ->whereDate('tbl_alquiler_cuota.fecha_vencimiento', '<', Carbon::today()->toDateString())
            ->update([
                'tbl_alquiler_cuota.estado' => 'atrasado',
                'tbl_alquiler_cuota.updated_at' => now(),
            ]);

        if (Schema::hasTable('tbl_gasto_cuota') && Schema::hasTable('tbl_gasto_cuota_detalle')) {
            DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_gasto_cuota_detalle.id_alquiler_fk')
                ->where('tbl_alquiler.id_inquilino_fk', $userId)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->where('tbl_gasto_cuota_detalle.estado_detalle', 'pendiente')
                ->whereDate('tbl_gasto_cuota.vencimiento_cuota', '<', Carbon::today()->toDateString())
                ->update([
                    'tbl_gasto_cuota_detalle.estado_detalle' => 'atrasado',
                    'tbl_gasto_cuota_detalle.actualizado_detalle' => now(),
                ]);

            DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto_cuota_detalle', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk', '=', 'tbl_gasto_cuota.id_gasto_cuota')
                ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_gasto_cuota_detalle.id_alquiler_fk')
                ->where('tbl_alquiler.id_inquilino_fk', $userId)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                ->whereDate('tbl_gasto_cuota.vencimiento_cuota', '<', Carbon::today()->toDateString())
                ->update([
                    'tbl_gasto_cuota.estado_cuota' => 'atrasado',
                    'tbl_gasto_cuota.actualizado_cuota' => now(),
                ]);
        }
    }

    private function obtenerResumenPagoAlquiler(int $alquilerId, $fechaInicio = null): array
    {
        $hoy = Carbon::today();

        // Obtener fecha de inicio si no se pasó
        if (!$fechaInicio) {
            $alquiler = Alquiler::find($alquilerId);
            $fechaInicio = $alquiler?->fecha_inicio_alquiler;
        }

        $fechaInicio = Carbon::parse($fechaInicio ?? now());
        $diaInicio = (int) $fechaInicio->format('d');

        // Determinar el mes de cuota vigente basado en período de 23 del mes anterior al 22 del mes actual
        $mesVigente = $hoy->copy();

        // Si hoy es menor al día de inicio: estamos en el período del mes anterior
        if ((int) $hoy->format('d') < $diaInicio) {
            $mesVigente = $hoy->copy()->subMonth();
        }

        // Buscar cuota vigente (del mes calculado)
        $cuotasAlquiler = AlquilerCuota::query()
            ->where('id_alquiler_fk', $alquilerId)
            ->orderBy('mes_cuota', 'asc')
            ->get();

        // Determinamos si el usuario está al día para el mes vigente
        $deudaHastaHoy = $cuotasAlquiler->filter(function (AlquilerCuota $cuota) use ($mesVigente) {
            return in_array($cuota->estado, ['pendiente', 'atrasado']) &&
                Carbon::parse((string) $cuota->mes_cuota)->format('Y-m') <= $mesVigente->format('Y-m');
        });

        $estadoPagoActual = $deudaHastaHoy->isEmpty() ? 'pagado' : 'pendiente';

        // La cuota de referencia para el botón de pago siempre será la más antigua pendiente (aunque sea futura si se quiere pagar por adelantado, pero el estado principal será 'pagado' si no hay deuda)
        $cuotaVigente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) {
            return in_array($cuota->estado, ['pendiente', 'atrasado']);
        });

        $cuotaReferencia = $cuotaVigente;

        // Si la cuota vigente ya está pagada (está al día), buscamos la siguiente cuota pendiente para el aviso de "Próximo pago"
        if ($estadoPagoActual === 'pagado') {
            $proximaPendiente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) use ($mesVigente) {
                return in_array($cuota->estado, ['pendiente', 'atrasado']) &&
                    Carbon::parse((string) $cuota->mes_cuota)->format('Y-m') > $mesVigente->format('Y-m');
            });
            if ($proximaPendiente) {
                $cuotaReferencia = $proximaPendiente;
            }
        }

        $diasParaPago = 0;
        $fechaProximoPago = $hoy->copy()->addMonth()->day($diaInicio)->toDateString();

        if ($cuotaReferencia) {
            // El pago vence al final del día de vencimiento (23:59:59).
            $mesReferencia = Carbon::parse((string) $cuotaReferencia->mes_cuota);

            // Calculamos el vencimiento: mes de la cuota + 1 mes, mismo día de inicio.
            // Usamos setDay para evitar desbordamientos en meses cortos (ej: 31 de enero -> 28 de febrero)
            $fechaVencimiento = $mesReferencia->copy()->addMonth();
            $ultimoDiaMesDestino = (int) $fechaVencimiento->daysInMonth;
            $diaVencimientoEfectivo = min($diaInicio, $ultimoDiaMesDestino);

            $fechaVencimiento = $fechaVencimiento->day($diaVencimientoEfectivo)->endOfDay();

            $diasParaPago = Carbon::now()->diffInDays($fechaVencimiento, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : (int) round($diasParaPago);
            $fechaProximoPago = $fechaVencimiento->toDateString();
        }

        // Contar pagos atrasados
        $cuotasPendientes = $cuotasAlquiler->filter(function (AlquilerCuota $c) {
            return in_array($c->estado, ['pendiente', 'atrasado']);
        });

        $numPagosAtrasados = $cuotasPendientes->filter(function (AlquilerCuota $c) use ($mesVigente) {
            return Carbon::parse((string) $c->mes_cuota)->format('Y-m') < $mesVigente->format('Y-m');
        })->count();

        $totalDeuda = (float) $cuotasPendientes->filter(function (AlquilerCuota $c) use ($mesVigente) {
            return Carbon::parse((string) $c->mes_cuota)->format('Y-m') <= $mesVigente->format('Y-m');
        })->sum('importe_base');

        return [
            'estado_pago_actual' => $estadoPagoActual,
            'dias_para_pago' => $diasParaPago,
            'fecha_proximo_pago' => $fechaProximoPago,
            'num_pagos_atrasados' => $numPagosAtrasados,
            'total_deuda' => $totalDeuda,
            'cuota_pendiente_id' => $cuotaVigente?->id_alquiler_cuota,
            'cuota_pendiente_importe' => (float) ($cuotaVigente?->importe_base ?? 0),
        ];
    }

    /**
     * Permite al inquilino cerrar una incidencia que él mismo ha reportado.
     */
    public function cerrarIncidencia($id)
    {
        $userId = Auth::id();

        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return back()->with('error', 'Incidencia no encontrada.');
        }

        // Seguridad: Solo el autor que reportó puede cerrar la incidencia
        if ($incidencia->id_reporta_fk != $userId) {
            return back()->with('error', 'No tienes permiso para cerrar esta incidencia.');
        }

        // Seguridad adicional: No cerrar si ya está resuelta
        if ($incidencia->estado_incidencia === 'resuelta') {
            return back()->with('info', 'Esta incidencia ya está marcada como resuelta.');
        }

        try {
            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'estado_incidencia' => 'resuelta',
                    'actualizado_incidencia' => now()
                ]);

            if (request()->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', '¡Incidencia cerrada correctamente! Gracias por confirmar la solución.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al cerrar la incidencia: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve el historial de pagos de suministros en JSON.
     */
    public function obtenerHistorialSuministros($id)
    {
        $userId = Auth::id();

        $historial = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $id)
            ->where('id_pagador_fk', $userId)
            ->where('tipo_pago', 'gasto')
            ->orderBy('creado_pago', 'desc')
            ->get();

        return response()->json($historial);
    }

    /**
     * Devuelve el historial de pagos de alquiler en JSON.
     */
    public function obtenerHistorialAlquiler($id)
    {
        $userId = Auth::id();

        $historial = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $id)
            ->where('id_pagador_fk', $userId)
            ->where('tipo_pago', 'alquiler')
            ->orderBy('creado_pago', 'desc')
            ->get();

        return response()->json($historial);
    }
}
