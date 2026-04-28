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
            DB::raw('MIN(tbl_fotos.ruta_foto) as ruta_foto'),
            DB::raw('MIN(tbl_alquiler.id_alquiler) as id_alquiler'),
            DB::raw('MIN(tbl_alquiler.estado_alquiler) as estado_alquiler'),
            DB::raw('MIN(CASE WHEN tbl_alquiler.id_inquilino_fk = ' . $userId . ' THEN tbl_alquiler.fecha_fin_alquiler END) as fecha_fin_alquiler'),
            DB::raw('(SELECT COUNT(*) FROM tbl_incidencia WHERE id_propiedad_fk = tbl_propiedad.id_propiedad AND estado_incidencia IN ("abierta", "en_proceso")) as total_incidencias_propiedad'),
            DB::raw('(SELECT COUNT(*) FROM tbl_alquiler_cuota c INNER JOIN tbl_alquiler a ON a.id_alquiler = c.id_alquiler_fk WHERE a.id_propiedad_fk = tbl_propiedad.id_propiedad AND a.id_inquilino_fk = ' . $userId . ' AND c.estado = "atrasado") as pago_atrasado'),
            DB::raw('(SELECT c.id_alquiler_cuota FROM tbl_alquiler_cuota c INNER JOIN tbl_alquiler a ON a.id_alquiler = c.id_alquiler_fk WHERE a.id_propiedad_fk = tbl_propiedad.id_propiedad AND a.id_inquilino_fk = ' . $userId . ' AND c.estado IN ("pendiente", "atrasado") ORDER BY c.mes_cuota ASC LIMIT 1) as cuota_pendiente_id'),
            DB::raw('(SELECT IFNULL(SUM(c.importe_base), 0) FROM tbl_alquiler_cuota c INNER JOIN tbl_alquiler a ON a.id_alquiler = c.id_alquiler_fk WHERE a.id_propiedad_fk = tbl_propiedad.id_propiedad AND a.id_inquilino_fk = ' . $userId . ' AND c.estado IN ("pendiente", "atrasado")) as total_deuda')
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

            $alquiler->haExpirado = false;
            $alquiler->diasExpirado = null;

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
                'tbl_contrato.url_pdf_contrato',
                'tbl_contrato.estado_contrato as estado_contrato_pdf'
            )
            ->first();

        if (!$alquiler) {
            return redirect()->route('gestionar_propiedades')->with('error', 'No tienes un alquiler activo para esta propiedad.');
        }

        $esInquilino = (int) $alquiler->id_inquilino_fk === (int) $userId;
        $esArrendador = (int) $alquiler->id_arrendador_fk === (int) $userId;

        // Lógica de usuario consistente con Miembro
        $nombreUsuario = $usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '';
        $tieneFoto = !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';

        // 2. Fotos de la propiedad
        $fotos = DB::table('tbl_fotos')
            ->where('id_propiedad_fk', $id)
            ->get();

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
        $pagosPendientes = AlquilerCuota::query()
            ->where('id_alquiler_fk', $alquiler->id_alquiler)
            ->whereIn('estado', ['pendiente', 'atrasado'])
            ->orderBy('mes_cuota', 'asc')
            ->get();

        $inicioMesActual = Carbon::now()->startOfMonth()->toDateString();
        $numPagosAtrasados = $pagosPendientes->where('mes_cuota', '<', $inicioMesActual)->count();

        $totalDeuda = 0;
        $proximoPago = $pagosPendientes->first();

        if ($proximoPago && $proximoPago->mes_cuota) {
            $fechaPago = Carbon::parse($proximoPago->mes_cuota)->day(1);
            $diasParaPago = Carbon::now()->diffInDays($fechaPago, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : round($diasParaPago);
            $fechaProximoPago = $fechaPago->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            
            // Verificamos si el pago pendiente es de este mes (o anterior) o de un mes futuro
            $mesActualStr = Carbon::now()->format('Y-m');
            $mesPagoStr = $fechaPago->format('Y-m');
            
            if ($mesPagoStr > $mesActualStr) {
                // El recibo pendiente es para el futuro, así que el mes actual está pagado
                $estadoPagoActual = 'pagado';
                $totalDeuda = 0; // Si el mes actual está pagado, no hay deuda "acumulada" exigible hoy
            } else {
                // El recibo pendiente es de este mes o de meses pasados (deuda acumulada)
                $estadoPagoActual = 'pendiente';
                // Sumamos todos los pagos pendientes hasta hoy
                $totalDeuda = $pagosPendientes->where('mes_cuota', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'))->sum('importe_base');
            }
        } else {
            $totalDeuda = 0;
            $diasParaPago = 0;
            $fechaProximoPago = Carbon::now()->addMonth()->day(1)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            $estadoPagoActual = 'pagado';
        }

        // Cuota pendiente ID (para los formularios de pago de la vista)
        $cuotaPendienteId = $proximoPago ? (int) $proximoPago->id_alquiler_cuota : null;

        // Foto principal de la propiedad
        $fotoPrincipal = $fotos->first() ? asset('storage/' . $fotos->first()->ruta_foto) : null;

        // Incidencias de la propiedad
        $incidencias = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id)
            ->orderBy('creado_incidencia', 'desc')
            ->get();

        // Historial de pagos
        $historialPagos = collect(DB::table('tbl_pago')
            ->where('id_pagador_fk', $usuario->id_usuario)
            ->where('id_alquiler_fk', (int) $alquiler->id_alquiler)
            ->orderByDesc('creado_pago')
            ->get());

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
            'esInquilino'         => $esInquilino,
            'esArrendador'        => $esArrendador,
            'historialPagos'      => $historialPagos,
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

        $esInquilinoDeLaPropiedad = DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $id)
            ->where('id_inquilino_fk', $usuario->id_usuario)
            ->where('estado_alquiler', 'activo')
            ->exists();

        if (!$esInquilinoDeLaPropiedad) {
            return redirect()->back()->with('error', 'Solo el inquilino activo de la propiedad puede crear incidencias.');
        }

        DB::beginTransaction();
        try {
            $propiedad = DB::table('tbl_propiedad')
                ->where('id_propiedad', $id)
                ->select('id_gestor_fk', 'id_arrendador_fk')
                ->first();

            if (!$propiedad) {
                DB::rollBack();
                return redirect()->back()->with('error', 'La propiedad asociada no existe.');
            }

            $idAsignado = $propiedad->id_gestor_fk ?: $propiedad->id_arrendador_fk;

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
                'comentario_historial' => 'Incidencia reportada por el inquilino.',
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

    public function decidirPagoIncidencia(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $request->validate([
            'responsable_pago' => 'required|in:arrendador,inquilino',
        ]);

        $incidencia = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->where('tbl_incidencia.id_incidencia', $id)
            ->select('tbl_incidencia.*', 'tbl_propiedad.id_arrendador_fk')
            ->first();

        if (!$incidencia) {
            return back()->with('error', 'Incidencia no encontrada.');
        }

        if ((int) $incidencia->id_arrendador_fk !== (int) $usuario->id_usuario) {
            return back()->with('error', 'Solo el arrendador puede decidir quién paga el presupuesto.');
        }

        if ($incidencia->estado_incidencia !== 'esperando_decision') {
            return back()->with('error', 'Esta incidencia no está pendiente de decisión del arrendador.');
        }

        $responsablePago = $request->responsable_pago;

        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update([
                'responsable_pago_incidencia' => $responsablePago,
                'estado_incidencia' => 'esperando_pago',
                'esperando_de_incidencia' => $responsablePago,
                'actualizado_incidencia' => now(),
            ]);

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $usuario->id_usuario,
            'comentario_historial' => 'El arrendador decide que el presupuesto lo pagará: ' . $responsablePago . '.',
            'cambio_estado_historial' => 'esperando_pago',
            'creado_historial' => now(),
            'actualizado_historial' => now(),
        ]);

        return back()->with('success', 'Decisión registrada. La incidencia queda pendiente de pago.');
    }

    public function pagarPresupuestoIncidencia($id)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $incidencia = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->where('tbl_incidencia.id_incidencia', $id)
            ->select('tbl_incidencia.*', 'tbl_propiedad.id_arrendador_fk')
            ->first();

        if (!$incidencia) {
            return back()->with('error', 'Incidencia no encontrada.');
        }

        if ($incidencia->estado_incidencia !== 'esperando_pago') {
            return back()->with('error', 'Esta incidencia no está pendiente de pago.');
        }

        $esArrendador = (int) $incidencia->id_arrendador_fk === (int) $usuario->id_usuario;
        $esInquilinoReporta = (int) $incidencia->id_reporta_fk === (int) $usuario->id_usuario;
        $responsable = (string) ($incidencia->responsable_pago_incidencia ?? '');

        $puedePagar = ($responsable === 'arrendador' && $esArrendador)
            || ($responsable === 'inquilino' && $esInquilinoReporta);

        if (!$puedePagar) {
            return back()->with('error', 'No eres la persona responsable del pago de este presupuesto.');
        }

        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update([
                'pagado_presupuesto_incidencia' => true,
                'pagado_incidencia' => now(),
                'estado_incidencia' => 'resuelta',
                'esperando_de_incidencia' => null,
                'resuelto_incidencia' => now(),
                'actualizado_incidencia' => now(),
            ]);

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $usuario->id_usuario,
            'comentario_historial' => 'Presupuesto pagado por ' . $responsable . '. Incidencia marcada como resuelta.',
            'cambio_estado_historial' => 'resuelta',
            'creado_historial' => now(),
            'actualizado_historial' => now(),
        ]);

        return back()->with('success', 'Pago registrado. La incidencia ha pasado a resuelta.');
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
                // 1. Verificar que la cuota base pertenece al inquilino
                $cuotaBase = AlquilerCuota::query()
                    ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
                    ->where('tbl_alquiler_cuota.id_alquiler_cuota', $cuotaId)
                    ->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->where('tbl_alquiler.estado_alquiler', 'activo')
                    ->select('tbl_alquiler_cuota.*', 'tbl_alquiler.id_alquiler')
                    ->lockForUpdate()
                    ->first();

                if (!$cuotaBase) {
                    throw new \Exception('La cuota no existe o no pertenece al inquilino.');
                }

                // 2. Mes vigente
                $hoy = now();
                $mesVigente = Carbon::create($hoy->year, $hoy->month, 1);

                // 3. Buscar TODAS las cuotas pendientes/atrasadas hasta el mes vigente (inclusive)
                $cuotasAPagar = AlquilerCuota::where('id_alquiler_fk', (int) $cuotaBase->id_alquiler_fk)
                    ->whereIn('estado', ['pendiente', 'atrasado'])
                    ->lockForUpdate()
                    ->get()
                    ->filter(function (AlquilerCuota $c) use ($mesVigente) {
                        return Carbon::parse((string) $c->mes_cuota)->format('Y-m') <= $mesVigente->format('Y-m');
                    })
                    ->sortBy('mes_cuota');

                if ($cuotasAPagar->isEmpty()) {
                    throw new \Exception('No hay cuotas pendientes para pagar.');
                }

                // 4. Procesar cada cuota individualmente
                foreach ($cuotasAPagar as $cuota) {
                    if ((string) $cuota->estado === 'pagado') {
                        continue;
                    }

                    Pago::create([
                        'id_pagador_fk'          => $userId,
                        'id_alquiler_fk'          => (int) $cuota->id_alquiler_fk,
                        'id_alquiler_cuota_fk'    => (int) $cuota->id_alquiler_cuota,
                        'tipo_pago'               => 'alquiler',
                        'concepto_pago'           => 'Cuota alquiler ' . Carbon::parse((string) $cuota->mes_cuota)->format('m/Y'),
                        'importe_pago'            => (float) $cuota->importe_base,
                        'mes_pago'                => Carbon::parse((string) $cuota->mes_cuota)->startOfMonth()->toDateString(),
                        'estado_pago'             => 'pagado',
                        'referencia_pago'         => 'ALQ-' . (int) $cuota->id_alquiler_fk . '-' . now()->format('YmdHis') . '-' . $cuota->id_alquiler_cuota,
                        'fecha_confirmacion_pago' => now(),
                        'creado_pago'             => now(),
                        'actualizado_pago'        => now(),
                    ]);

                    AlquilerCuota::where('id_alquiler_cuota', (int) $cuota->id_alquiler_cuota)
                        ->update([
                            'estado'     => 'pagado',
                            'pagado_en'  => now(),
                            'updated_at' => now(),
                        ]);

                    if (Schema::hasTable('tbl_gasto_cuota') && Schema::hasTable('tbl_gasto_cuota_detalle')) {
                        $detallesGasto = DB::table('tbl_gasto_cuota_detalle')
                            ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                            ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', (int) $cuota->id_alquiler_fk)
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
                                'id_pagador_fk'             => $userId,
                                'id_alquiler_fk'            => (int) $cuota->id_alquiler_fk,
                                'id_gasto_cuota_detalle_fk' => (int) $detalle->id_gasto_cuota_detalle,
                                'id_gasto_cuota_fk'         => (int) $detalle->id_gasto_cuota_fk,
                                'tipo_pago'                 => 'gasto',
                                'concepto_pago'             => 'Gasto servicios ' . Carbon::parse((string) $cuota->mes_cuota)->format('m/Y'),
                                'importe_pago'              => (float) $detalle->importe_detalle,
                                'mes_pago'                  => Carbon::parse((string) $cuota->mes_cuota)->startOfMonth()->toDateString(),
                                'estado_pago'               => 'pagado',
                                'referencia_pago'           => 'GST-' . (int) $detalle->id_gasto_cuota_detalle . '-' . now()->format('YmdHis'),
                                'fecha_confirmacion_pago'   => now(),
                                'creado_pago'               => now(),
                                'actualizado_pago'          => now(),
                            ]);

                            DB::table('tbl_gasto_cuota_detalle')
                                ->where('id_gasto_cuota_detalle', (int) $detalle->id_gasto_cuota_detalle)
                                ->update([
                                    'estado_detalle'      => 'pagado',
                                    'pagado_detalle'      => now(),
                                    'actualizado_detalle' => now(),
                                ]);

                            $totalDetalles = DB::table('tbl_gasto_cuota_detalle')->where('id_gasto_cuota_fk', (int) $detalle->id_gasto_cuota_fk)->count();
                            $totalPagados  = DB::table('tbl_gasto_cuota_detalle')->where('id_gasto_cuota_fk', (int) $detalle->id_gasto_cuota_fk)->where('estado_detalle', 'pagado')->count();
                            $estadoCuota   = $totalDetalles > 0 && $totalPagados === $totalDetalles ? 'pagado' : ($totalPagados > 0 ? 'parcial' : 'pendiente');

                            DB::table('tbl_gasto_cuota')
                                ->where('id_gasto_cuota', (int) $detalle->id_gasto_cuota_fk)
                                ->update([
                                    'estado_cuota'    => $estadoCuota,
                                    'pagado_cuota'    => $estadoCuota === 'pagado' ? now() : null,
                                    'actualizado_cuota' => now(),
                                ]);
                        }
                    }
                }
            });

            return redirect()->back()->with('success', 'Pago realizado correctamente.');
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

    /**
     * Permite al inquilino cerrar una incidencia que él mismo ha reportado.
     */
    public function cerrarIncidencia($id)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $userId = (int) $usuario->id_usuario;

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

        if ($incidencia->estado_incidencia !== 'resuelta') {
            return back()->with('error', 'Solo puedes confirmar y cerrar incidencias en estado resuelta.');
        }

        try {
            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'estado_incidencia' => 'cerrada',
                    'cerrado_incidencia' => now(),
                    'actualizado_incidencia' => now()
                ]);

            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $id,
                'id_usuario_fk' => $userId,
                'comentario_historial' => 'El inquilino confirma la resolución y cierra la incidencia.',
                'cambio_estado_historial' => 'cerrada',
                'creado_historial' => now(),
                'actualizado_historial' => now(),
            ]);

            return back()->with('success', 'Incidencia cerrada correctamente. Gracias por confirmar la resolución.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cerrar la incidencia: ' . $e->getMessage());
        }
    }
}
