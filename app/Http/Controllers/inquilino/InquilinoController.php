<?php

namespace App\Http\Controllers\inquilino;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

        // Lógica de usuario consistente con Miembro
        $nombreUsuario = $usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '';
        $tieneFoto = !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';

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
            $alquiler->mostrarAlertaFin = false;
            $alquiler->diasFinContrato = null;
            $alquiler->esMismoDia = false;
            $alquiler->tiempoRestanteHoy = null;
            $alquiler->banner_foto_url = $alquiler->ruta_foto
                ? asset('public/img/' . $alquiler->ruta_foto)
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

            if (!empty($alquiler->id_alquiler)) {
                $resumenPago = $this->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);
                $alquiler->estado_pago_actual = $resumenPago['estado_pago_actual'];
                $alquiler->dias_para_pago = $resumenPago['dias_para_pago'];
                $alquiler->fecha_proximo_pago = $resumenPago['fecha_proximo_pago'];
                $alquiler->num_pagos_atrasados = $resumenPago['num_pagos_atrasados'];
                $alquiler->total_deuda = $resumenPago['total_deuda'];
                $alquiler->cuota_pendiente_id = $resumenPago['cuota_pendiente_id'];
                $alquiler->pago_atrasado = $resumenPago['num_pagos_atrasados'];
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

        // 5. Obtener ciudades únicas para el filtro
        $ciudades = DB::table('tbl_propiedad')
            ->join('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->where(function ($qb) use ($userId) {
                $qb->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->distinct()
            ->pluck('ciudad_propiedad');

        // Si es una petición AJAX (Fetch), devolver solo el grid
        if ($request->ajax()) {
            return view('inquilino.partials.grid_propiedades', compact('alquileres'))->render();
        }

        return view('inquilino.gestionar_propiedades', [
            'nombreUsuario' => $nombreUsuario,
            'tieneFoto' => $tieneFoto,
            'fotoUsuario' => $fotoUsuario,
            'inicialUsuario' => $inicialUsuario,
            'esInquilino' => true,
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
                'tbl_contrato.estado_contrato as estado_contrato_pdf'
            )
            ->first();

        if (!$alquiler) {
            return redirect()->route('gestionar_propiedades')->with('error', 'No tienes un alquiler activo para esta propiedad.');
        }

        // Lógica de usuario consistente con Miembro
        $nombreUsuario = $usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '';
        $tieneFoto = !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';

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

        // 4. Próximo pago (basado en cuotas de alquiler)
        $resumenPago = $this->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);
        $estadoPagoActual = $resumenPago['estado_pago_actual'];
        $diasParaPago = $resumenPago['dias_para_pago'];
        $fechaProximoPago = $resumenPago['fecha_proximo_pago'];
        $numPagosAtrasados = $resumenPago['num_pagos_atrasados'];
        $totalDeuda = $resumenPago['total_deuda'];
        $cuotaPendienteId = $resumenPago['cuota_pendiente_id'];

        // 4. Incidencias (Todas las de la propiedad)
        $incidencias = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id)
            ->orderBy('creado_incidencia', 'desc')
            ->get();

        return view('inquilino.ver_propiedad', [
            'nombreUsuario'       => $nombreUsuario,
            'tieneFoto'           => $tieneFoto,
            'fotoUsuario'         => $fotoUsuario,
            'inicialUsuario'      => $inicialUsuario,
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
            'cuotaPendienteId'    => $cuotaPendienteId,
            'incidencias'         => $incidencias,
            'esInquilino'         => true,
            'pdfEjemplo'          => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
        ]);
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
            // 1. Crear la incidencia
            $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_reporta_fk' => $usuario->id_usuario,
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
            return redirect()->back()->with('success', 'Incidencia reportada correctamente. Se ha añadido al listado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al reportar la incidencia: ' . $e->getMessage());
        }
    }

    public function pagarCuotaAlquiler(int $cuotaId)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $userId = (int) ($usuario->id_usuario ?? 0);

        try {
            DB::transaction(function () use ($cuotaId, $userId) {
                $cuota = AlquilerCuota::query()
                    ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
                    ->where('tbl_alquiler_cuota.id_alquiler_cuota', $cuotaId)
                    ->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->where('tbl_alquiler.estado_alquiler', 'activo')
                    ->select('tbl_alquiler_cuota.*', 'tbl_alquiler.id_alquiler')
                    ->lockForUpdate()
                    ->first();

                if (!$cuota) {
                    throw new \Exception('La cuota no existe o no pertenece al inquilino.');
                }

                if ((string) $cuota->estado === 'pagado') {
                    throw new \Exception('Esta cuota ya está pagada');
                }

                Pago::create([
                    'id_pagador_fk' => $userId,
                    'id_alquiler_fk' => (int) $cuota->id_alquiler,
                    'id_alquiler_cuota_fk' => (int) $cuota->id_alquiler_cuota,
                    'tipo_pago' => 'alquiler',
                    'concepto_pago' => 'Cuota alquiler ' . Carbon::parse((string) $cuota->mes_cuota)->format('m/Y'),
                    'importe_pago' => (float) $cuota->importe_base,
                    'mes_pago' => Carbon::parse((string) $cuota->mes_cuota)->startOfMonth()->toDateString(),
                    'estado_pago' => 'pagado',
                    'referencia_pago' => 'ALQ-' . (int) $cuota->id_alquiler . '-' . now()->format('YmdHis'),
                    'fecha_confirmacion_pago' => now(),
                    'creado_pago' => now(),
                    'actualizado_pago' => now(),
                ]);

                AlquilerCuota::where('id_alquiler_cuota', (int) $cuota->id_alquiler_cuota)
                    ->update([
                        'estado' => 'pagado',
                        'pagado_en' => now(),
                        'updated_at' => now(),
                    ]);

                if (Schema::hasTable('tbl_gasto_cuota') && Schema::hasTable('tbl_gasto_cuota_detalle')) {
                    $detallesGasto = DB::table('tbl_gasto_cuota_detalle')
                        ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                        ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', (int) $cuota->id_alquiler)
                        ->where('tbl_gasto_cuota.mes_cuota', Carbon::parse((string) $cuota->mes_cuota)->startOfMonth()->toDateString())
                        ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $userId)
                        ->where('tbl_gasto_cuota_detalle.estado_detalle', '!=', 'pagado')
                        ->select(
                            'tbl_gasto_cuota_detalle.id_gasto_cuota_detalle',
                            'tbl_gasto_cuota_detalle.id_gasto_cuota_fk',
                            'tbl_gasto_cuota_detalle.importe_detalle'
                        )
                        ->lockForUpdate()
                        ->get();

                    foreach ($detallesGasto as $detalle) {
                        Pago::create([
                            'id_pagador_fk' => $userId,
                            'id_alquiler_fk' => (int) $cuota->id_alquiler,
                            'id_gasto_cuota_detalle_fk' => (int) $detalle->id_gasto_cuota_detalle,
                            'id_gasto_cuota_fk' => (int) $detalle->id_gasto_cuota_fk,
                            'tipo_pago' => 'gasto',
                            'concepto_pago' => 'Gasto servicios ' . Carbon::parse((string) $cuota->mes_cuota)->format('m/Y'),
                            'importe_pago' => (float) $detalle->importe_detalle,
                            'mes_pago' => Carbon::parse((string) $cuota->mes_cuota)->startOfMonth()->toDateString(),
                            'estado_pago' => 'pagado',
                            'referencia_pago' => 'GST-' . (int) $detalle->id_gasto_cuota_detalle . '-' . now()->format('YmdHis'),
                            'fecha_confirmacion_pago' => now(),
                            'creado_pago' => now(),
                            'actualizado_pago' => now(),
                        ]);

                        DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_detalle', (int) $detalle->id_gasto_cuota_detalle)
                            ->update([
                                'estado_detalle' => 'pagado',
                                'pagado_detalle' => now(),
                                'actualizado_detalle' => now(),
                            ]);

                        $totalDetalles = DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_fk', (int) $detalle->id_gasto_cuota_fk)
                            ->count();

                        $totalPagados = DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_fk', (int) $detalle->id_gasto_cuota_fk)
                            ->where('estado_detalle', 'pagado')
                            ->count();

                        $estadoCuota = 'pendiente';
                        if ($totalDetalles > 0 && $totalPagados === $totalDetalles) {
                            $estadoCuota = 'pagado';
                        } elseif ($totalPagados > 0) {
                            $estadoCuota = 'parcial';
                        }

                        DB::table('tbl_gasto_cuota')
                            ->where('id_gasto_cuota', (int) $detalle->id_gasto_cuota_fk)
                            ->update([
                                'estado_cuota' => $estadoCuota,
                                'pagado_cuota' => $estadoCuota === 'pagado' ? now() : null,
                                'actualizado_cuota' => now(),
                            ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Cuota pagada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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

            $inicioMesActual = $hoy->copy()->startOfMonth();
            $finMesActual = $hoy->copy()->endOfMonth();

            // Buscar cuota vigente (del mes calculado)
            $cuotasAlquiler = AlquilerCuota::query()
                ->where('id_alquiler_fk', $alquilerId)
                ->orderBy('mes_cuota', 'asc')
                ->get();

            // Encontrar cuota vigente: la que tenga mes_cuota coincidiendo con mes vigente
            $cuotaVigente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) use ($mesVigente) {
                return Carbon::parse((string) $cuota->mes_cuota)->format('Y-m') === $mesVigente->format('Y-m');
            });

            // Si no hay cuota vigente en ese mes, buscar pendiente/atrasada más antigua
            if (!$cuotaVigente) {
                $cuotaVigente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) {
                    return in_array($cuota->estado, ['pendiente', 'atrasado']);
                });
            }

            $cuotaReferencia = $cuotaVigente;
            $estadoPagoActual = $cuotaVigente && in_array($cuotaVigente->estado, ['pendiente', 'atrasado']) ? 'pendiente' : 'pagado';
            
            $diasParaPago = 0;
            $fechaProximoPago = $hoy->copy()->addMonth()->day($diaInicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

            if ($cuotaReferencia) {
                // Fecha de vencimiento = día de inicio del siguiente mes (exactamente 1 mes después del inicio)
                $fechaVencimiento = Carbon::parse((string) $cuotaReferencia->mes_cuota)
                    ->addMonth()
                    ->day($diaInicio)
                    ->startOfDay();

                $diasParaPago = Carbon::now()->diffInDays($fechaVencimiento, false);
                $diasParaPago = $diasParaPago < 0 ? 0 : (int) round($diasParaPago);
                $fechaProximoPago = $fechaVencimiento->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            }

            // Contar pagos atrasados
            $cuotasPendientes = $cuotasAlquiler->filter(function (AlquilerCuota $c) {
                return in_array($c->estado, ['pendiente', 'atrasado']);
            });

            $numPagosAtrasados = $cuotasPendientes->filter(function (AlquilerCuota $c) use ($mesVigente) {
                return Carbon::parse((string) $c->mes_cuota)->format('Y-m') < $mesVigente->format('Y-m');
            })->count();

            $totalDeuda = (float) $cuotasPendientes->sum('importe_base');

            return [
                'estado_pago_actual' => $estadoPagoActual,
                'dias_para_pago' => $diasParaPago,
                'fecha_proximo_pago' => $fechaProximoPago,
                'num_pagos_atrasados' => $numPagosAtrasados,
                'total_deuda' => $totalDeuda,
                'cuota_pendiente_id' => $cuotaVigente?->id_alquiler_cuota,
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

            return back()->with('success', '¡Incidencia cerrada correctamente! Gracias por confirmar la solución.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cerrar la incidencia: ' . $e->getMessage());
        }
    }
}
