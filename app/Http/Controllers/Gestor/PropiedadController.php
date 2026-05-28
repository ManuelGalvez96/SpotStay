<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Services\ActividadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PropiedadController extends Controller
{
    use GestorPermisosTrait;
    /**
     * Muestra el listado de propiedades asignadas al gestor autenticado.
     *
     * Construye una consulta principal con subconsultas (leftJoinSub) para
     * obtener contadores de alquileres activos, incidencias abiertas/críticas
     * y estados de pagos (pendiente, atrasado, pagado) por propiedad.
     *
     * Aplica filtros opcionales:
     *   - q: búsqueda libre por título, dirección o nombre del arrendador
     *   - estado: filtra por estado de la propiedad (publicada, alquilada, inactiva)
     *   - ciudad: filtra por ciudad (LIKE)
     *   - operativo: filtros especiales (criticas, sin_alquiler, estables)
     *   - sort/dir: columna y dirección de ordenación (con lista blanca)
     *
     * Calcula KPIs independientes con consultas directas:
     * total asignadas, publicadas, alquiladas, con críticas y sin alquiler.
     *
     * Retorna la vista 'gestor.propiedades' con paginación (10 por página).
     */
    public function index(Request $request)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        if ($gestorId <= 0) {
            abort(403);
        }

        $subAlquileresActivos = DB::table('tbl_alquiler')
            ->select('id_propiedad_fk', DB::raw('COUNT(*) as total_alquileres_activos'))
            ->where('estado_alquiler', 'activo')
            ->groupBy('id_propiedad_fk');

        $subIncidenciasActivas = DB::table('tbl_incidencia')
            ->select('id_propiedad_fk', DB::raw('COUNT(*) as total_incidencias_activas'))
            ->whereIn('estado_incidencia', ['abierta', 'en_proceso', 'esperando'])
            ->groupBy('id_propiedad_fk');

        $subIncidenciasCriticas = DB::table('tbl_incidencia')
            ->select('id_propiedad_fk', DB::raw('COUNT(*) as total_incidencias_criticas'))
            ->whereIn('estado_incidencia', ['abierta', 'en_proceso', 'esperando'])
            ->where('prioridad_incidencia', 'urgente')
            ->groupBy('id_propiedad_fk');

        $subPagosPendientes = DB::table('tbl_alquiler')
            ->join('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->select('tbl_alquiler.id_propiedad_fk', DB::raw('COUNT(*) as total_pagos_pendientes'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pendiente')
            ->groupBy('tbl_alquiler.id_propiedad_fk');

        $subPagosAtrasados = DB::table('tbl_alquiler')
            ->join('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->select('tbl_alquiler.id_propiedad_fk', DB::raw('COUNT(*) as total_pagos_atrasados'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'atrasado')
            ->groupBy('tbl_alquiler.id_propiedad_fk');

        $subPagosPagados = DB::table('tbl_alquiler')
            ->join('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->select('tbl_alquiler.id_propiedad_fk', DB::raw('COUNT(*) as total_pagos_pagados'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pagado')
            ->groupBy('tbl_alquiler.id_propiedad_fk');

        $baseQuery = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->leftJoinSub($subAlquileresActivos, 'alq_activos', function ($join) {
                $join->on('alq_activos.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subIncidenciasActivas, 'inc_activas', function ($join) {
                $join->on('inc_activas.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subIncidenciasCriticas, 'inc_criticas', function ($join) {
                $join->on('inc_criticas.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subPagosPendientes, 'pagos_pendientes', function ($join) {
                $join->on('pagos_pendientes.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subPagosAtrasados, 'pagos_atrasados', function ($join) {
                $join->on('pagos_atrasados.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subPagosPagados, 'pagos_pagados', function ($join) {
                $join->on('pagos_pagados.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->where('tbl_propiedad.id_gestor_fk', $gestorId);

        $query = clone $baseQuery;

        $q = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', '');
        $ciudad = trim((string) $request->query('ciudad', ''));
        $estadoPagos = (string) $request->query('estado_pagos', '');
        $sort = (string) $request->query('sort', 'creado_propiedad');
        $dir = strtolower((string) $request->query('dir', 'desc'));

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $q . '%')
                    ->orWhereRaw("CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad, tbl_propiedad.piso_propiedad, tbl_propiedad.puerta_propiedad) like ?", ['%' . $q . '%'])
                    ->orWhere('arrendador.nombre_usuario', 'like', '%' . $q . '%');
            });
        }

        if ($estado !== '') {
            $query->where('tbl_propiedad.estado_propiedad', $estado);
        }

        if ($ciudad !== '') {
            $query->where('tbl_propiedad.ciudad_propiedad', 'like', '%' . $ciudad . '%');
        }

        if ($estadoPagos === 'al_dia') {
            $query->whereRaw('COALESCE(pagos_pendientes.total_pagos_pendientes, 0) = 0')
                ->whereRaw('COALESCE(pagos_atrasados.total_pagos_atrasados, 0) = 0');
        }

        if ($estadoPagos === 'pendiente') {
            $query->whereRaw('COALESCE(pagos_pendientes.total_pagos_pendientes, 0) > 0');
        }

        if ($estadoPagos === 'atrasado') {
            $query->whereRaw('COALESCE(pagos_atrasados.total_pagos_atrasados, 0) > 0');
        }

        $proximoVencimiento = (int) $request->query('proximo_vencimiento', 0);

        if ($proximoVencimiento > 0) {
            $query->whereExists(function ($q) use ($proximoVencimiento) {
                $q->select(DB::raw(1))
                    ->from('tbl_alquiler')
                    ->whereColumn('tbl_alquiler.id_propiedad_fk', 'tbl_propiedad.id_propiedad')
                    ->where('tbl_alquiler.estado_alquiler', 'activo')
                    ->whereBetween('tbl_alquiler.fecha_fin_alquiler', [
                        Carbon::now()->startOfDay(),
                        Carbon::now()->addDays($proximoVencimiento)->endOfDay()
                    ]);
            });
        }

        $allowedSorts = [
            'titulo_propiedad' => 'tbl_propiedad.titulo_propiedad',
            'precio_propiedad' => 'tbl_propiedad.precio_propiedad',
            'creado_propiedad' => 'tbl_propiedad.creado_propiedad',
            'incidencias_activas' => DB::raw('COALESCE(inc_activas.total_incidencias_activas, 0)'),
            'alquileres_activos' => DB::raw('COALESCE(alq_activos.total_alquileres_activos, 0)'),
            'incidencias_criticas' => DB::raw('COALESCE(inc_criticas.total_incidencias_criticas, 0)'),
        ];

        $sortColumn = $allowedSorts[$sort] ?? $allowedSorts['creado_propiedad'];
        $sortDir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';

        // (removed temporary debug instrumentation)

        $propiedades = $query
            ->select(
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.codigo_postal_propiedad',
                'tbl_propiedad.estado_propiedad',
                'tbl_propiedad.precio_propiedad',
                'tbl_propiedad.creado_propiedad',
                'arrendador.nombre_usuario as nombre_arrendador',
                DB::raw('COALESCE(alq_activos.total_alquileres_activos, 0) as total_alquileres_activos'),
                DB::raw('COALESCE(inc_activas.total_incidencias_activas, 0) as total_incidencias_activas'),
                DB::raw('COALESCE(inc_criticas.total_incidencias_criticas, 0) as total_incidencias_criticas'),
                DB::raw('COALESCE(pagos_pendientes.total_pagos_pendientes, 0) as total_pagos_pendientes'),
                DB::raw('COALESCE(pagos_atrasados.total_pagos_atrasados, 0) as total_pagos_atrasados'),
                DB::raw('COALESCE(pagos_pagados.total_pagos_pagados, 0) as total_pagos_pagados')
            )
            ->orderBy($sortColumn, $sortDir)
            ->orderBy('tbl_propiedad.id_propiedad', 'desc')
            ->paginate(10)
            ->withQueryString();

        // KPIs con consultas directas y simples
        $totalAsignadas = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->count();

        $totalPublicadas = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->where('estado_propiedad', 'publicada')
            ->count();

        $totalAlquiladas = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->where('estado_propiedad', 'alquilada')
            ->count();

        $totalConCriticas = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->whereIn('id_propiedad', function ($query) {
                $query->select('id_propiedad_fk')
                    ->from('tbl_incidencia')
                    ->whereIn('estado_incidencia', ['abierta', 'en_proceso', 'esperando'])
                    ->where('prioridad_incidencia', 'urgente');
            })
            ->count();

        $totalSinAlquiler = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->whereNotIn('id_propiedad', function ($query) {
                $query->select('id_propiedad_fk')
                    ->from('tbl_alquiler')
                    ->where('estado_alquiler', 'activo');
            })
            ->count();

        $permisosPropiedades = [];
        foreach ($propiedades as $p) {
            $permisosPropiedades[$p->id_propiedad] = $this->getPermisosPropiedad($gestorId, (int) $p->id_propiedad);
        }

        return view('gestor.propiedades', compact(
            'propiedades',
            'totalAsignadas',
            'totalPublicadas',
            'totalAlquiladas',
            'totalConCriticas',
            'totalSinAlquiler',
            'permisosPropiedades',
            'q',
            'estado',
            'ciudad',
            'estadoPagos',
            'proximoVencimiento',
            'sort',
            'dir'
        ));
    }

    /**
     * Muestra el detalle completo de una propiedad asignada al gestor.
     *
     * Consulta la propiedad con datos del arrendador y del gestor.
     * Obtiene también:
     *   - Alquileres activos vinculados a la propiedad
     *   - Últimas 10 incidencias ordenadas por fecha
     *   - Totales de incidencias por estado (abierta, en_proceso, resuelta)
     *   - Datos de gastos: resumen, cuotas, pagos principales
     *
     * Retorna la vista 'gestor.propiedad' con toda la información consolidada.
     */
    public function show(int $id)
    {
        $gestor = Auth::user();
        $gestorId = $gestor?->id_usuario;

        $propiedad = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->join('tbl_usuario as gestor', 'gestor.id_usuario', '=', 'tbl_propiedad.id_gestor_fk')
            ->where('tbl_propiedad.id_propiedad', $id)
            ->where('tbl_propiedad.id_gestor_fk', $gestorId)
            ->select(
                'tbl_propiedad.*',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador',
                'gestor.nombre_usuario as nombre_gestor'
            )
            ->first();

        if (!$propiedad) {
            abort(404);
        }
        $gastosData = $this->obtenerDatosGastosPropiedad($propiedad, (int) $gestorId);
        $gastosHabilitados = $gastosData['gastosHabilitados'];
        $resumenGastos = $gastosData['resumenGastos'];
        $pagosPrincipales = $gastosData['pagosPrincipales'];
        $cuotasGasto = $gastosData['cuotasGasto'];
        $cuotasDetallePorId = $gastosData['cuotasDetallePorId'];

        $alquileresActivos = DB::table('tbl_alquiler')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')
            ->where('tbl_alquiler.id_propiedad_fk', $id)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_alquiler.id_inquilino_fk',
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino'
            )
            ->orderBy('tbl_alquiler.fecha_inicio_alquiler', 'desc')
            ->get();

        $incidenciasRecientes = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id)
            ->select(
                'id_incidencia',
                'titulo_incidencia',
                'estado_incidencia',
                'prioridad_incidencia',
                'creado_incidencia',
                'id_asignado_fk'
            )
            ->orderBy('creado_incidencia', 'desc')
            ->limit(10)
            ->get();

        $totalesIncidencia = [
            'abiertas' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'abierta')->count(),
            'en_proceso' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'en_proceso')->count(),
            'resueltas' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'resuelta')->count(),
        ];

        $permisos = $this->getPermisosPropiedad($gestorId, $id);

        return view('gestor.propiedad', compact(
            'propiedad',
            'alquileresActivos',
            'incidenciasRecientes',
            'totalesIncidencia',
            'gastosHabilitados',
            'resumenGastos',
            'pagosPrincipales',
            'cuotasGasto',
            'cuotasDetallePorId',
            'permisos'
        ));
    }

    /**
     * Vista dedicada a la gestión de gastos de una propiedad.
     *
     * Reutiliza obtenerDatosGastosPropiedad para cargar:
     *   - Gastos gestionables creados por este gestor
     *   - Resumen mensual de emitidos vs pagados (últimos 6 meses)
     *   - Cuotas y sus detalles desglosados por inquilino
     *
     * Retorna la vista 'gestor.propiedad-gastos' con todos los datos.
     */
    public function gastos(int $id)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        $propiedad = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->join('tbl_usuario as gestor', 'gestor.id_usuario', '=', 'tbl_propiedad.id_gestor_fk')
            ->where('tbl_propiedad.id_propiedad', $id)
            ->where('tbl_propiedad.id_gestor_fk', $gestorId)
            ->select(
                'tbl_propiedad.*',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador',
                'gestor.nombre_usuario as nombre_gestor'
            )
            ->first();

        if (!$propiedad) {
            abort(404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);

        if (!$permisos->gastos) {
            return $this->redirigirSinPermiso('gastos');
        }

        $gastosData = $this->obtenerDatosGastosPropiedad($propiedad, $gestorId);

        return view('gestor.propiedad-gastos', [
            'propiedad' => $propiedad,
            'gastosHabilitados' => $gastosData['gastosHabilitados'],
            'resumenGastos' => $gastosData['resumenGastos'],
            'pagosPrincipales' => $gastosData['pagosPrincipales'],
            'cuotasGasto' => $gastosData['cuotasGasto'],
            'cuotasDetallePorId' => $gastosData['cuotasDetallePorId'],
            'gastosGestionables' => $gastosData['gastosGestionables'],
            'resumenMensualGastos' => $gastosData['resumenMensualGastos'],
        ]);
    }

    /**
     * Crea un nuevo gasto (recibo) para una propiedad y genera sus cuotas.
     *
     * Valida los datos de entrada: categoría, concepto, importe estimado y fechas.
     * Verifica que existan alquileres activos para repartir el coste.
     *
     * En una transacción:
     *   1. Inserta el registro en tbl_gasto (periodicidad mensual, pagador: inquilino)
     *   2. Crea una cuota mensual para el mes de inicio con vencimiento a 1 mes
     *   3. Genera los detalles de cuota dividiendo el importe entre los inquilinos activos
     *
     * Retorna al formulario con mensaje de éxito.
     */
    public function storeGasto(Request $request, int $id)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        if (!Schema::hasTable('tbl_gasto') || !Schema::hasTable('tbl_gasto_cuota') || !Schema::hasTable('tbl_gasto_cuota_detalle')) {
            return redirect()->back()->with('error', 'La gestión de gastos todavía no está disponible. Ejecuta las migraciones pendientes.');
        }

        $propiedad = $this->getPropiedadDelGestor($id, $gestorId);
        if (!$propiedad) {
            abort(404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);
        if (!$permisos->gastos) {
            return $this->redirigirSinPermiso('gastos');
        }

        $validated = $request->validate([
            'categoria_gasto' => ['required', 'in:luz,agua,gas,internet,comunidad,otros'],
            'concepto_gasto' => ['nullable', 'string', 'max:200'],
            'importe_estimado' => ['required', 'numeric', 'min:0.01'],
            'fecha_inicio_gasto' => ['required', 'date'],
            'fecha_fin_gasto' => ['required', 'date', 'after_or_equal:fecha_inicio_gasto'],
        ]);

        $conceptoGasto = trim((string) ($validated['concepto_gasto'] ?? ''));
        $conceptoGasto = $conceptoGasto !== '' ? $conceptoGasto : null;
        $fechaInicioRecibo = Carbon::parse($validated['fecha_inicio_gasto']);
        $fechaFinRecibo = Carbon::parse($validated['fecha_fin_gasto']);

        $importeEstimado = round((float) $validated['importe_estimado'], 2);

        $inicio = $fechaInicioRecibo->copy()->startOfMonth();
        $fin = $fechaFinRecibo->copy()->startOfMonth();

        if ($inicio->greaterThan($fin)) {
            throw ValidationException::withMessages([
                'fecha_fin_gasto' => 'La fecha fin no puede ser anterior a la fecha inicio.',
            ]);
        }

        $alquileresActivos = $this->getAlquileresActivos($id);
        if ($alquileresActivos->isEmpty()) {
            throw ValidationException::withMessages([
                'categoria_gasto' => 'No hay inquilinos activos para repartir este recibo.',
            ]);
        }

        $categoriaGasto = (string) $validated['categoria_gasto'];

        DB::transaction(function () use ($id, $gestorId, $conceptoGasto, $categoriaGasto, $fechaInicioRecibo, $fechaFinRecibo, $inicio, $fin, $alquileresActivos, $importeEstimado) {
            $ahora = now();
            // If there is any active alquiler, link the gasto to the first one.
            // The application assumes one active alquiler per property; multiple tenants
            // should be represented within the same alquiler record.
            $idAlquilerFk = null;
            if (!$alquileresActivos->isEmpty()) {
                $idAlquilerFk = (int) $alquileresActivos->first()->id_alquiler;
            }

            $gastoId = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_alquiler_fk' => $idAlquilerFk,
                'id_gestor_fk' => $gestorId,
                'concepto_gasto' => $conceptoGasto,
                'categoria_gasto' => $categoriaGasto,
                'importe_estimado' => $importeEstimado,
                'ambito_gasto' => 'propiedad',
                'pagador_gasto' => 'inquilino',
                'periodicidad_gasto' => 'mensual',
                'fecha_inicio_gasto' => $fechaInicioRecibo->toDateString(),
                'fecha_fin_gasto' => $fechaFinRecibo->toDateString(),
                'estado_gasto' => 'activo',
                'creado_gasto' => $ahora,
                'actualizado_gasto' => $ahora,
            ]);

            // Use a fixed vencimiento: one month after the gestor adds the recibo
            $vencimientoFijo = Carbon::today()->addMonth();

            // Create a single cuota for the start month (mes inicial)
            $this->crearCuotaMensualConDetalles(
                $gastoId,
                $inicio->copy(),
                $importeEstimado,
                (int) $fechaInicioRecibo->day,
                'inquilino',
                $alquileresActivos,
                $vencimientoFijo
            );
        });

        $propTitulo = DB::table('tbl_propiedad')->where('id_propiedad', $id)->value('titulo_propiedad');
        if ($propTitulo) {
            (new ActividadService())->gastoCreado($gestorId, $id, $propTitulo, $categoriaGasto, $conceptoGasto ?? '', $importeEstimado);
        }

        return redirect()->back()->with('success', 'Recibo añadido correctamente y cuotas generadas.');
    }

    /**
     * Marca un detalle de cuota de gasto como pagado.
     *
     * Verifica que el detalle pertenezca a la propiedad del gestor autenticado.
     * Si el estado actual no es 'pagado', lo marca como tal con la fecha de pago.
     * Luego actualiza el estado de la cuota padre (pendiente, parcial, pagado, atrasado)
     * según el estado de todos sus detalles.
     *
     * Retorna al formulario con mensaje de éxito.
     */
    /**
     * Actualiza un gasto existente: modifica datos del recibo y regenera sus cuotas.
     *
     * Valida los nuevos datos (categoría, concepto, importe, fecha inicio).
     * En transacción:
     *   1. Actualiza los campos de tbl_gasto con los nuevos valores
     *   2. Elimina todas las cuotas y detalles existentes de este gasto
     *   3. Crea una nueva cuota mensual con los datos actualizados
     *
     * Retorna al formulario con mensaje de éxito.
     */
    public function updateGasto(Request $request, int $id, int $gastoId)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        if (!Schema::hasTable('tbl_gasto') || !Schema::hasTable('tbl_gasto_cuota') || !Schema::hasTable('tbl_gasto_cuota_detalle')) {
            return redirect()->back()->with('error', 'La gestión de gastos todavía no está disponible. Ejecuta las migraciones pendientes.');
        }

        $propiedad = $this->getPropiedadDelGestor($id, $gestorId);
        if (!$propiedad) {
            abort(404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);
        if (!$permisos->gastos) {
            return $this->redirigirSinPermiso('gastos');
        }

        $gasto = DB::table('tbl_gasto')
            ->where('id_gasto', $gastoId)
            ->where('id_propiedad_fk', $id)
            ->where('id_gestor_fk', $gestorId)
            ->first();

        if (!$gasto) {
            abort(404);
        }

        $tieneCuotasPagadas = DB::table('tbl_gasto_cuota')
            ->where('id_gasto_fk', $gastoId)
            ->where('estado_cuota', 'pagado')
            ->exists();

        if ($tieneCuotasPagadas) {
            return redirect()->back()->with('error', 'No puedes modificar un gasto que ya tiene pagos registrados.');
        }

        $validated = $request->validate([
            'categoria_gasto' => ['required', 'in:luz,agua,gas,internet,comunidad,otros'],
            'concepto_gasto' => ['nullable', 'string', 'max:200'],
            'importe_estimado' => ['required', 'numeric', 'min:0.01'],
            'fecha_inicio_gasto' => ['required', 'date'],
            'fecha_fin_gasto' => ['required', 'date', 'after_or_equal:fecha_inicio_gasto'],
        ]);

        $alquileresActivos = $this->getAlquileresActivos($id);
        if ($alquileresActivos->isEmpty()) {
            throw ValidationException::withMessages([
                'categoria_gasto' => 'No hay inquilinos activos para repartir este recibo.',
            ]);
        }

        $conceptoGasto = trim((string) ($validated['concepto_gasto'] ?? ''));
        $conceptoGasto = $conceptoGasto !== '' ? $conceptoGasto : null;
        $fechaInicioRecibo = Carbon::parse($validated['fecha_inicio_gasto']);
        $fechaFinRecibo = Carbon::parse($validated['fecha_fin_gasto']);
        $importeEstimado = round((float) $validated['importe_estimado'], 2);
        $mesCuota = $fechaInicioRecibo->copy()->startOfMonth();
        $vencimientoFijo = Carbon::today()->addMonth();

        DB::transaction(function () use ($id, $gestorId, $gastoId, $validated, $conceptoGasto, $fechaInicioRecibo, $fechaFinRecibo, $importeEstimado, $mesCuota, $vencimientoFijo, $alquileresActivos) {
            $idAlquilerFk = !$alquileresActivos->isEmpty()
                ? (int) $alquileresActivos->first()->id_alquiler
                : null;

            DB::table('tbl_gasto')
                ->where('id_gasto', $gastoId)
                ->update([
                    'id_alquiler_fk' => $idAlquilerFk,
                    'categoria_gasto' => (string) $validated['categoria_gasto'],
                    'concepto_gasto' => $conceptoGasto,
                    'importe_estimado' => $importeEstimado,
                    'fecha_inicio_gasto' => $fechaInicioRecibo->toDateString(),
                    'fecha_fin_gasto' => $fechaFinRecibo->toDateString(),
                    'actualizado_gasto' => now(),
                ]);

            $cuotaIds = DB::table('tbl_gasto_cuota')
                ->where('id_gasto_fk', $gastoId)
                ->pluck('id_gasto_cuota')
                ->all();

            if (!empty($cuotaIds)) {
                DB::table('tbl_gasto_cuota_detalle')
                    ->whereIn('id_gasto_cuota_fk', $cuotaIds)
                    ->delete();

                DB::table('tbl_gasto_cuota')
                    ->whereIn('id_gasto_cuota', $cuotaIds)
                    ->delete();
            }

            $this->crearCuotaMensualConDetalles(
                $gastoId,
                $mesCuota,
                $importeEstimado,
                (int) $fechaInicioRecibo->day,
                'inquilino',
                $alquileresActivos,
                $vencimientoFijo
            );
        });

        return redirect()->back()->with('success', 'Recibo actualizado correctamente.');
    }

    /**
     * Elimina un gasto y todas sus cuotas y detalles asociados.
     *
     * Verifica que el gasto pertenezca a la propiedad del gestor.
     * En transacción:
     *   1. Elimina todos los detalles de cuota (tbl_gasto_cuota_detalle)
     *   2. Elimina todas las cuotas (tbl_gasto_cuota)
     *   3. Elimina el registro del gasto (tbl_gasto)
     *
     * Retorna al formulario con mensaje de éxito.
     */
    public function destroyGasto(Request $request, int $id, int $gastoId)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        if (!Schema::hasTable('tbl_gasto') || !Schema::hasTable('tbl_gasto_cuota') || !Schema::hasTable('tbl_gasto_cuota_detalle')) {
            return redirect()->back()->with('error', 'La gestión de gastos todavía no está disponible. Ejecuta las migraciones pendientes.');
        }

        $propiedad = $this->getPropiedadDelGestor($id, $gestorId);
        if (!$propiedad) {
            abort(404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);
        if (!$permisos->gastos) {
            return $this->redirigirSinPermiso('gastos');
        }

        $gasto = DB::table('tbl_gasto')
            ->where('id_gasto', $gastoId)
            ->where('id_propiedad_fk', $id)
            ->where('id_gestor_fk', $gestorId)
            ->first();

        if (!$gasto) {
            abort(404);
        }

        $tieneCuotasPagadas = DB::table('tbl_gasto_cuota')
            ->where('id_gasto_fk', $gastoId)
            ->where('estado_cuota', 'pagado')
            ->exists();

        if ($tieneCuotasPagadas) {
            return redirect()->back()->with('error', 'No puedes eliminar un gasto que ya tiene pagos registrados.');
        }

        DB::transaction(function () use ($gastoId) {
            $cuotaIds = DB::table('tbl_gasto_cuota')
                ->where('id_gasto_fk', $gastoId)
                ->pluck('id_gasto_cuota')
                ->all();

            if (!empty($cuotaIds)) {
                DB::table('tbl_gasto_cuota_detalle')
                    ->whereIn('id_gasto_cuota_fk', $cuotaIds)
                    ->delete();

                DB::table('tbl_gasto_cuota')
                    ->whereIn('id_gasto_cuota', $cuotaIds)
                    ->delete();
            }

            DB::table('tbl_gasto')
                ->where('id_gasto', $gastoId)
                ->delete();
        });

        return redirect()->back()->with('success', 'Recibo eliminado correctamente.');
    }

    /**
     * Obtiene una propiedad solo si está asignada al gestor indicado.
     * Se usa como validación de acceso en las acciones CRUD de gastos.
     */
    private function getPropiedadDelGestor(int $propiedadId, int $gestorId): ?object
    {
        return DB::table('tbl_propiedad')
            ->where('id_propiedad', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->first();
    }

    /**
     * Obtiene los alquileres activos de una propiedad.
     * Retorna solo id_alquiler e id_inquilino_fk ordenados por ID.
     */
    private function getAlquileresActivos(int $propiedadId)
    {
        return DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('estado_alquiler', 'activo')
            ->select('id_alquiler', 'id_inquilino_fk')
            ->orderBy('id_alquiler')
            ->get();
    }

    /**
     * Genera automáticamente las cuotas mensuales pendientes de todos los gastos
     * activos de una propiedad, desde su fecha de inicio hasta el mes actual.
     *
     * Para cada gasto:
     *   1. Calcula el rango de meses entre fecha_inicio y fecha_fin (tope: hoy)
     *   2. Para cada mes, verifica si ya existe una cuota
     *   3. Si no existe, la crea con crearCuotaMensualConDetalles
     *
     * Se ejecuta antes de cargar datos de gastos para asegurar consistencia.
     */
    private function ensureCuotasMensualesGeneradas(int $propiedadId, int $gestorId): void
    {
        $gastos = DB::table('tbl_gasto')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->where('estado_gasto', 'activo')
            ->get();

        $alquileresActivos = $this->getAlquileresActivos($propiedadId);

        foreach ($gastos as $gasto) {
            $inicio = Carbon::parse($gasto->fecha_inicio_gasto)->startOfMonth();

            $existe = DB::table('tbl_gasto_cuota')
                ->where('id_gasto_fk', $gasto->id_gasto)
                ->where('mes_cuota', $inicio->toDateString())
                ->exists();

            if (!$existe) {
                $this->crearCuotaMensualConDetalles(
                    (int) $gasto->id_gasto,
                    $inicio->copy(),
                    (float) ($gasto->importe_estimado ?? 0),
                    (int) $gasto->dia_vencimiento,
                    (string) $gasto->pagador_gasto,
                    $alquileresActivos,
                    Carbon::today()->addMonth()
                );
            }
        }
    }

    /**
     * Crea una cuota mensual (tbl_gasto_cuota) y sus detalles por inquilino.
     *
     * Calcula el vencimiento: usa vencimientoFijo si se proporciona,
     * o sino el dia_vencimiento del mes (ajustado al último día si es mayor).
     *
     * Si pagador_gasto es 'arrendador', crea un solo detalle con el importe total.
     * Si es 'inquilino', divide el importe equitativamente entre los alquileres activos:
     *   - Calcula base = floor(importe / total) para evitar redondeos que sumen de más
     *   - El último inquilino recibe el remanente (importe - acumulado)
     *
     * Todos los detalles se crean con estado 'pendiente'.
     */
    private function crearCuotaMensualConDetalles(
        int $gastoId,
        Carbon $mes,
        float $importeTotal,
        int $diaVencimiento,
        string $pagadorGasto,
        $alquileresActivos,
        ?Carbon $vencimientoFijo = null
    ): void {
        $mesBase = $mes->copy()->startOfMonth();
        if ($vencimientoFijo !== null) {
            $vencimiento = $vencimientoFijo->copy();
        } else {
            $ultimoDia = (int) $mesBase->copy()->endOfMonth()->day;
            $dia = min($diaVencimiento, $ultimoDia);
            $vencimiento = $mesBase->copy()->day($dia);
        }

        $cuotaId = (int) DB::table('tbl_gasto_cuota')->insertGetId([
            'id_gasto_fk' => $gastoId,
            'mes_cuota' => $mesBase->toDateString(),
            'vencimiento_cuota' => $vencimiento->toDateString(),
            'importe_total_cuota' => round($importeTotal, 2),
            'estado_cuota' => 'pendiente',
            'pagado_cuota' => null,
            'creado_cuota' => now(),
            'actualizado_cuota' => now(),
        ]);

        if ($alquileresActivos->isEmpty()) {
            return;
        }

        if ($pagadorGasto === 'arrendador') {
            $alquilerRef = $alquileresActivos->first();
            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk' => $cuotaId,
                'id_alquiler_fk' => (int) $alquilerRef->id_alquiler,
                'id_pagador_fk' => (int) $alquilerRef->id_inquilino_fk,
                'importe_detalle' => round($importeTotal, 2),
                'estado_detalle' => 'pendiente',
                'pagado_detalle' => null,
                'creado_detalle' => now(),
                'actualizado_detalle' => now(),
            ]);

            return;
        }

        $totalAlquileres = max(1, (int) $alquileresActivos->count());
        $base = floor((($importeTotal / $totalAlquileres) * 100)) / 100;
        $acumulado = 0.0;

        foreach ($alquileresActivos->values() as $index => $alquiler) {
            $importeDetalle = $index === $totalAlquileres - 1
                ? round($importeTotal - $acumulado, 2)
                : round($base, 2);

            $acumulado += $importeDetalle;

            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk' => $cuotaId,
                'id_alquiler_fk' => (int) $alquiler->id_alquiler,
                'id_pagador_fk' => (int) $alquiler->id_inquilino_fk,
                'importe_detalle' => $importeDetalle,
                'estado_detalle' => 'pendiente',
                'pagado_detalle' => null,
                'creado_detalle' => now(),
                'actualizado_detalle' => now(),
            ]);
        }
    }

    /**
     * Actualiza el estado de una cuota según el estado de sus detalles.
     *
     * Lógica de estados:
     *   - 'pagado': todos los detalles están pagados
     *   - 'parcial': al menos un detalle pagado pero no todos
     *   - 'atrasado': ningún detalle pagado y el vencimiento ya pasó
     *   - 'pendiente': estado por defecto
     *
     * Si la cuota se marca como pagada, registra la fecha actual en pagado_cuota.
     */
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
            $pagadoCuota = now();
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
                'actualizado_cuota' => now(),
            ]);
    }

    /**
     * Normaliza un concepto de texto a una categoría reconocida.
     * Busca palabras clave en el texto (alquiler, luz, agua, gas, etc.)
     * y retorna la categoría correspondiente, o null si no coincide.
     */
    private function normalizarConceptoPrincipal(string $concepto): ?string
    {
        $texto = strtolower(trim($concepto));

        if ($texto === '') {
            return null;
        }

        if (str_contains($texto, 'alquiler') || str_contains($texto, 'renta')) {
            return 'alquiler';
        }
        if (str_contains($texto, 'luz') || str_contains($texto, 'electric')) {
            return 'luz';
        }
        if (str_contains($texto, 'agua')) {
            return 'agua';
        }
        if (str_contains($texto, 'gas')) {
            return 'gas';
        }
        if (str_contains($texto, 'internet') || str_contains($texto, 'wifi') || str_contains($texto, 'fibra')) {
            return 'internet';
        }
        if (str_contains($texto, 'comunidad')) {
            return 'comunidad';
        }

        return null;
    }

    /**
     * Valida que una categoría de gasto sea una de las permitidas.
     * Retorna la categoría normalizada o null si no es válida.
     */
    private function normalizarCategoriaGasto(string $categoria): ?string
    {
        $texto = strtolower(trim($categoria));

        return in_array($texto, ['luz', 'agua', 'gas', 'internet', 'comunidad', 'otros'], true)
            ? $texto
            : null;
    }

    /**
     * Obtiene y consolida todos los datos de gastos para una propiedad.
     *
     * Primero normaliza pagadores a 'inquilino' y genera cuotas pendientes.
     *
     * Calcula:
     *   - resumenGastos: totales del mes, pendientes, atrasados, pagados
     *   - pagosPrincipales: estado de cada categoría (alquiler, luz, agua, gas, etc.)
     *     con importe acumulado, estado y fecha del último pago
     *   - cuotasGasto: últimas 24 cuotas con datos del gasto padre
     *   - cuotasDetallePorId: detalles agrupados por cuota
     *   - gastosGestionables: gastos creados por este gestor (últimos 24)
     *   - resumenMensualGastos: emitidos vs pagados de los últimos 6 meses
     *     con porcentajes para renderizado de barras CSS
     *
     * Si las tablas de gastos no existen, retorna estructura vacía con gastosHabilitados=false.
     */
    private function obtenerDatosGastosPropiedad(object $propiedad, int $gestorId): array
    {
        $gastosHabilitados = Schema::hasTable('tbl_gasto')
            && Schema::hasTable('tbl_gasto_cuota')
            && Schema::hasTable('tbl_gasto_cuota_detalle');

        $resumenGastos = [
            'mensual_total' => 0,
            'pendientes_mes' => 0,
            'total_pendiente_importe' => 0,
            'atrasados' => 0,
            'pagados_mes' => 0,
        ];
        $pagosPrincipales = [
            'alquiler' => ['label' => 'Alquiler', 'importe' => (float) $propiedad->precio_propiedad, 'estado' => 'pendiente', 'detalle' => null, 'atrasados' => 0],
            'luz' => ['label' => 'Luz', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
            'agua' => ['label' => 'Agua', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
            'gas' => ['label' => 'Gas', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
            'internet' => ['label' => 'Internet', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
            'comunidad' => ['label' => 'Comunidad', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
            'otros' => ['label' => 'Otros', 'importe' => 0.0, 'estado' => 'sin_dato', 'detalle' => null, 'atrasados' => 0],
        ];
        $cuotasGasto = collect();
        $cuotasDetallePorId = collect();
        $gastosGestionables = collect();
        $resumenMensualGastos = collect();

        if ($gastosHabilitados) {
            $propiedadId = (int) $propiedad->id_propiedad;
            $this->normalizarPagadoresSoloInquilinos($propiedadId, $gestorId);
            $this->ensureCuotasMensualesGeneradas($propiedadId, $gestorId);

            $hoy = Carbon::today()->toDateString();
            $mesActual = Carbon::today()->startOfMonth()->toDateString();
            $inicioMes = Carbon::today()->startOfMonth()->toDateString();

            $resumenGastos = [
                'mensual_total' => (float) DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->sum('tbl_gasto_cuota.importe_total_cuota') + (float) $propiedad->precio_propiedad,
                'total_pendiente_importe' => ((float) DB::table('tbl_gasto_cuota_detalle')
                    ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->where('tbl_gasto_cuota_detalle.estado_detalle', '!=', 'pagado')
                    ->sum('tbl_gasto_cuota_detalle.importe_detalle')) + (float) $propiedad->precio_propiedad,
                'atrasados' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                    ->where('tbl_gasto_cuota.vencimiento_cuota', '<', $hoy)
                    ->count(),
                'pendientes_mes' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                    ->count(),
                'pagados_mes' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->where('tbl_gasto_cuota.estado_cuota', 'pagado')
                    ->count(),
            ];

            $cuotasPrincipales = DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->leftJoin('tbl_gasto_cuota_detalle as detalle_estado', function ($join) {
                    $join->on('detalle_estado.id_gasto_cuota_fk', '=', 'tbl_gasto_cuota.id_gasto_cuota')
                        ->where('detalle_estado.estado_detalle', '=', 'pagado');
                })
                ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                ->select(
                    'tbl_gasto.id_gasto',
                    'tbl_gasto.concepto_gasto',
                    'tbl_gasto.categoria_gasto',
                    'tbl_gasto_cuota.importe_total_cuota',
                    'tbl_gasto_cuota.estado_cuota',
                    'tbl_gasto_cuota.vencimiento_cuota',
                    'detalle_estado.actualizado_detalle as pagado_detalle_en'
                )
                ->orderBy('tbl_gasto_cuota.vencimiento_cuota', 'desc')
                ->get();

            foreach ($cuotasPrincipales as $cuotaPrincipal) {
                $clave = $this->normalizarCategoriaGasto((string) ($cuotaPrincipal->categoria_gasto ?? ''))
                    ?? $this->normalizarConceptoPrincipal((string) $cuotaPrincipal->concepto_gasto);
                if (!$clave || !array_key_exists($clave, $pagosPrincipales) || $clave === 'alquiler') {
                    continue;
                }

                $pagosPrincipales[$clave]['importe'] += (float) $cuotaPrincipal->importe_total_cuota;

                $estadoActual = (string) $pagosPrincipales[$clave]['estado'];
                $estadoCuota = (string) $cuotaPrincipal->estado_cuota;
                $fechaPagado = $cuotaPrincipal->pagado_detalle_en ?? $cuotaPrincipal->pagado_en ?? null;

                // Consider cuota as vencida/atrasada when vencimiento is past and estado is pending/partial
                $vencimiento = $cuotaPrincipal->vencimiento_cuota ?? null;
                $esVencida = false;
                if ($vencimiento) {
                    try {
                        $esVencida = Carbon::parse((string) $vencimiento)->lt(Carbon::today());
                    } catch (\Exception $e) {
                        $esVencida = false;
                    }
                }

                if ($estadoCuota === 'atrasado' || ($esVencida && in_array($estadoCuota, ['pendiente', 'parcial'], true))) {
                    $pagosPrincipales[$clave]['estado'] = 'atrasado';
                    $pagosPrincipales[$clave]['atrasados'] = ((int) $pagosPrincipales[$clave]['atrasados']) + 1;
                } elseif ($estadoCuota === 'pendiente' || $estadoCuota === 'parcial') {
                    if ($estadoActual !== 'atrasado') {
                        $pagosPrincipales[$clave]['estado'] = 'pendiente';
                    }
                } elseif ($estadoCuota === 'pagado') {
                    if ($estadoActual === 'sin_dato') {
                        $pagosPrincipales[$clave]['estado'] = 'pagado';
                    }
                    if ($fechaPagado) {
                        $fechaPagadoCarbon = Carbon::parse((string) $fechaPagado);
                        $actual = $pagosPrincipales[$clave]['detalle'];
                        if (!$actual || $fechaPagadoCarbon->greaterThan(Carbon::createFromFormat('d/m/Y', (string) $actual['fecha']))) {
                            $pagosPrincipales[$clave]['detalle'] = [
                                'texto' => 'Pagado',
                                'fecha' => $fechaPagadoCarbon->format('d/m/Y'),
                                'timestamp' => $fechaPagadoCarbon->getTimestamp(),
                            ];
                        }
                    }
                }
            }

            $alquileresIds = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $propiedadId)
                ->where('estado_alquiler', 'activo')
                ->pluck('id_alquiler')
                ->all();

            if (empty($alquileresIds)) {
                $pagosPrincipales['alquiler']['estado'] = 'sin_dato';
            } elseif (!Schema::hasTable('tbl_alquiler_cuota')) {
                $pagosPrincipales['alquiler']['estado'] = 'pendiente';
            } else {
                $cuotasMes = DB::table('tbl_alquiler_cuota')
                    ->whereIn('id_alquiler_fk', $alquileresIds)
                    ->where('mes_cuota', $mesActual)
                    ->select('id_alquiler_fk', 'estado')
                    ->get();

                $atrasadosPrevios = DB::table('tbl_alquiler_cuota')
                    ->whereIn('id_alquiler_fk', $alquileresIds)
                    ->where('mes_cuota', '<', $inicioMes)
                    ->where('estado', '!=', 'pagado')
                    ->exists();

                if ($atrasadosPrevios) {
                    $pagosPrincipales['alquiler']['estado'] = 'atrasado';
                } elseif ($cuotasMes->isEmpty()) {
                    $pagosPrincipales['alquiler']['estado'] = 'pendiente';
                } else {
                    $confirmados = $cuotasMes->where('estado', 'pagado')->pluck('id_alquiler_fk')->unique()->count();
                    $totalAlquileresActivos = count($alquileresIds);

                    if ($confirmados === $totalAlquileresActivos) {
                        $pagosPrincipales['alquiler']['estado'] = 'pagado';
                    } elseif ($confirmados > 0) {
                        $pagosPrincipales['alquiler']['estado'] = 'parcial';
                    } else {
                        $pagosPrincipales['alquiler']['estado'] = 'pendiente';
                    }
                }
            }

            foreach ($pagosPrincipales as $clave => $pagoPrincipal) {
                if ($clave === 'alquiler') {
                    continue;
                }

                if ((float) $pagoPrincipal['importe'] <= 0) {
                    $pagosPrincipales[$clave]['estado'] = 'sin_dato';
                }

                $pagosPrincipales[$clave]['importe'] = round((float) $pagosPrincipales[$clave]['importe'], 2);

                if ($pagosPrincipales[$clave]['estado'] === 'atrasado' && ((int) $pagosPrincipales[$clave]['atrasados']) > 1) {
                    $pagosPrincipales[$clave]['detalle'] = [
                        'texto' => 'Atrasado',
                        'fecha' => $pagosPrincipales[$clave]['atrasados'] . ' recibos pendientes',
                        'timestamp' => 0,
                    ];
                } elseif ($pagosPrincipales[$clave]['estado'] === 'atrasado' && !$pagosPrincipales[$clave]['detalle']) {
                    $pagosPrincipales[$clave]['detalle'] = [
                        'texto' => 'Atrasado',
                        'fecha' => 'Pendiente de regularizar',
                        'timestamp' => 0,
                    ];
                } elseif ($pagosPrincipales[$clave]['estado'] === 'pendiente') {
                    $pagosPrincipales[$clave]['detalle'] = [
                        'texto' => 'Pendiente',
                        'fecha' => '',
                        'timestamp' => 0,
                    ];
                }
            }

            $cuotasGasto = DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                ->select(
                    'tbl_gasto_cuota.id_gasto_cuota',
                    'tbl_gasto_cuota.id_gasto_fk',
                    'tbl_gasto_cuota.mes_cuota',
                    'tbl_gasto_cuota.vencimiento_cuota',
                    'tbl_gasto_cuota.importe_total_cuota',
                    'tbl_gasto_cuota.estado_cuota',
                    'tbl_gasto_cuota.pagado_cuota',
                    'tbl_gasto.concepto_gasto',
                    'tbl_gasto.categoria_gasto',
                    'tbl_gasto.pagador_gasto',
                    'tbl_gasto.ambito_gasto',
                    'tbl_gasto.id_alquiler_fk',
                    'tbl_gasto.fecha_inicio_gasto',
                    'tbl_gasto.fecha_fin_gasto'
                )
                ->orderByRaw("CASE WHEN tbl_gasto_cuota.estado_cuota = 'pagado' THEN 1 ELSE 0 END")
                ->orderBy('tbl_gasto_cuota.id_gasto_cuota', 'desc')
                ->limit(24)
                ->get();

            $cuotaIds = $cuotasGasto->pluck('id_gasto_cuota')->all();
            $detalles = collect();

            if (!empty($cuotaIds)) {
                $detalles = DB::table('tbl_gasto_cuota_detalle')
                    ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_gasto_cuota_detalle.id_pagador_fk')
                    ->whereIn('tbl_gasto_cuota_detalle.id_gasto_cuota_fk', $cuotaIds)
                    ->select(
                        'tbl_gasto_cuota_detalle.id_gasto_cuota_detalle',
                        'tbl_gasto_cuota_detalle.id_gasto_cuota_fk',
                        'tbl_gasto_cuota_detalle.id_pagador_fk',
                        'tbl_gasto_cuota_detalle.importe_detalle',
                        'tbl_gasto_cuota_detalle.estado_detalle',
                        'tbl_gasto_cuota_detalle.pagado_detalle',
                        'tbl_usuario.nombre_usuario'
                    )
                    ->orderBy('tbl_gasto_cuota_detalle.id_gasto_cuota_detalle')
                    ->get();
            }

            $cuotasDetallePorId = $detalles->groupBy('id_gasto_cuota_fk');

            $gastosGestionables = DB::table('tbl_gasto')
                ->where('id_propiedad_fk', $propiedadId)
                ->where('id_gestor_fk', $gestorId)
                ->select(
                    'id_gasto',
                    'concepto_gasto',
                    'categoria_gasto',
                    'importe_estimado',
                    'fecha_inicio_gasto',
                    'fecha_fin_gasto',
                    'estado_gasto',
                    'creado_gasto'
                )
                ->orderBy('creado_gasto', 'desc')
                ->limit(24)
                ->get();

            $inicioPeriodo = Carbon::today()->startOfMonth()->subMonths(5)->toDateString();

            $emitidosPorMes = DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                ->where('tbl_gasto_cuota.mes_cuota', '>=', $inicioPeriodo)
                ->selectRaw("DATE_FORMAT(tbl_gasto_cuota.mes_cuota, '%Y-%m-01') as mes, SUM(tbl_gasto_cuota.importe_total_cuota) as total")
                ->groupBy('mes')
                ->pluck('total', 'mes');

            $pagadosPorMes = DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                ->where('tbl_gasto_cuota.mes_cuota', '>=', $inicioPeriodo)
                ->where('tbl_gasto_cuota_detalle.estado_detalle', 'pagado')
                ->selectRaw("DATE_FORMAT(tbl_gasto_cuota.mes_cuota, '%Y-%m-01') as mes, SUM(tbl_gasto_cuota_detalle.importe_detalle) as total")
                ->groupBy('mes')
                ->pluck('total', 'mes');

            $meses = collect();
            $cursor = Carbon::today()->startOfMonth()->subMonths(5);
            for ($i = 0; $i < 6; $i++) {
                $keyMes = $cursor->format('Y-m-01');
                $meses->push([
                    'key' => $keyMes,
                    'label' => $cursor->translatedFormat('M y'),
                    'emitidos' => round((float) ($emitidosPorMes[$keyMes] ?? 0), 2),
                    'pagados' => round((float) ($pagadosPorMes[$keyMes] ?? 0), 2),
                ]);
                $cursor->addMonth();
            }

            $maxValor = max(1.0, (float) $meses->max(function ($item) {
                return max((float) $item['emitidos'], (float) $item['pagados']);
            }));

            $resumenMensualGastos = $meses->map(function ($item) use ($maxValor) {
                $emitidosPct = (int) round((((float) $item['emitidos']) / $maxValor) * 100);
                $pagadosPct = (int) round((((float) $item['pagados']) / $maxValor) * 100);
                return [
                    'label' => $item['label'],
                    'emitidos' => $item['emitidos'],
                    'pagados' => $item['pagados'],
                    'emitidos_pct' => max(0, min(100, $emitidosPct)),
                    'pagados_pct' => max(0, min(100, $pagadosPct)),
                ];
            });
        }

        return [
            'gastosHabilitados' => $gastosHabilitados,
            'resumenGastos' => $resumenGastos,
            'pagosPrincipales' => $pagosPrincipales,
            'cuotasGasto' => $cuotasGasto,
            'cuotasDetallePorId' => $cuotasDetallePorId,
            'gastosGestionables' => $gastosGestionables,
            'resumenMensualGastos' => $resumenMensualGastos,
        ];
    }

    /**
     * Normaliza todos los gastos de la propiedad para que el pagador sea 'inquilino'.
     *
     * Si existían gastos con pagador distinto (ej. 'arrendador'):
     *   1. Actualiza pagador_gasto a 'inquilino' en tbl_gasto
     *   2. Busca detalles de cuota donde el pagador era el gestor
     *   3. Elimina esos detalles y los redistribuye entre los inquilinos activos
     *   4. Resetea el estado de la cuota a 'pendiente'
     *
     * Cada operación de redistribución se ejecuta en su propia transacción.
     */
    private function normalizarPagadoresSoloInquilinos(int $propiedadId, int $gestorId): void
    {
        DB::table('tbl_gasto')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->where('pagador_gasto', '!=', 'inquilino')
            ->update([
                'pagador_gasto' => 'inquilino',
                'actualizado_gasto' => now(),
            ]);

        $alquileresActivos = $this->getAlquileresActivos($propiedadId);
        if ($alquileresActivos->isEmpty()) {
            return;
        }

        $cuotasConGestor = DB::table('tbl_gasto_cuota_detalle')
            ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
            ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
            ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
            ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $gestorId)
            ->select('tbl_gasto_cuota.id_gasto_cuota', 'tbl_gasto_cuota.importe_total_cuota')
            ->distinct()
            ->get();

        foreach ($cuotasConGestor as $cuota) {
            DB::transaction(function () use ($cuota, $alquileresActivos) {
                DB::table('tbl_gasto_cuota_detalle')
                    ->where('id_gasto_cuota_fk', $cuota->id_gasto_cuota)
                    ->delete();

                $totalAlquileres = max(1, (int) $alquileresActivos->count());
                $importeTotal = (float) $cuota->importe_total_cuota;
                $base = floor((($importeTotal / $totalAlquileres) * 100)) / 100;
                $acumulado = 0.0;

                foreach ($alquileresActivos->values() as $index => $alquiler) {
                    $importeDetalle = $index === $totalAlquileres - 1
                        ? round($importeTotal - $acumulado, 2)
                        : round($base, 2);

                    $acumulado += $importeDetalle;

                    DB::table('tbl_gasto_cuota_detalle')->insert([
                        'id_gasto_cuota_fk' => (int) $cuota->id_gasto_cuota,
                        'id_alquiler_fk' => (int) $alquiler->id_alquiler,
                        'id_pagador_fk' => (int) $alquiler->id_inquilino_fk,
                        'importe_detalle' => $importeDetalle,
                        'estado_detalle' => 'pendiente',
                        'pagado_detalle' => null,
                        'creado_detalle' => now(),
                        'actualizado_detalle' => now(),
                    ]);
                }

                DB::table('tbl_gasto_cuota')
                    ->where('id_gasto_cuota', (int) $cuota->id_gasto_cuota)
                    ->update([
                        'estado_cuota' => 'pendiente',
                        'pagado_cuota' => null,
                        'actualizado_cuota' => now(),
                    ]);
            });
        }
    }

    public function filtrarGastos(Request $request, int $id)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_gestor_fk', $gestorId)
            ->exists();

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);
        if (!$permisos->gastos) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para gestionar gastos en esta propiedad.'], 403);
        }

        $categoria = (string) $request->query('categoria', '');
        $estado = (string) $request->query('estado', '');
        $concepto = trim((string) $request->query('concepto', ''));
        $periodoDesde = (string) $request->query('periodo_desde', '');
        $periodoHasta = (string) $request->query('periodo_hasta', '');

        $query = DB::table('tbl_gasto_cuota')
            ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
            ->where('tbl_gasto.id_propiedad_fk', $id)
            ->select(
                'tbl_gasto_cuota.id_gasto_cuota',
                'tbl_gasto_cuota.id_gasto_fk',
                'tbl_gasto_cuota.mes_cuota',
                'tbl_gasto_cuota.vencimiento_cuota',
                'tbl_gasto_cuota.importe_total_cuota',
                'tbl_gasto_cuota.estado_cuota',
                'tbl_gasto_cuota.pagado_cuota',
                'tbl_gasto.concepto_gasto',
                'tbl_gasto.categoria_gasto',
                'tbl_gasto.pagador_gasto',
                'tbl_gasto.ambito_gasto',
                'tbl_gasto.id_alquiler_fk',
                'tbl_gasto.fecha_inicio_gasto',
                'tbl_gasto.fecha_fin_gasto'
            );

        if ($categoria !== '') {
            $query->where('tbl_gasto.categoria_gasto', $categoria);
        }

        if ($estado !== '') {
            $query->where('tbl_gasto_cuota.estado_cuota', $estado);
        }

        if ($concepto !== '') {
            $query->where('tbl_gasto.concepto_gasto', 'like', '%' . $concepto . '%');
        }

        if ($periodoDesde !== '') {
            $query->where('tbl_gasto.fecha_fin_gasto', '>=', $periodoDesde);
        }

        if ($periodoHasta !== '') {
            $query->where('tbl_gasto.fecha_inicio_gasto', '<=', $periodoHasta);
        }

        $cuotas = $query
            ->orderByRaw("CASE WHEN tbl_gasto_cuota.estado_cuota = 'pagado' THEN 1 ELSE 0 END")
            ->orderBy('tbl_gasto_cuota.id_gasto_cuota', 'desc')
            ->get();

        $cuotaIds = $cuotas->pluck('id_gasto_cuota')->all();
        $detalles = collect();

        if (!empty($cuotaIds)) {
            $detalles = DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_gasto_cuota_detalle.id_pagador_fk')
                ->whereIn('tbl_gasto_cuota_detalle.id_gasto_cuota_fk', $cuotaIds)
                ->select(
                    'tbl_gasto_cuota_detalle.id_gasto_cuota_detalle',
                    'tbl_gasto_cuota_detalle.id_gasto_cuota_fk',
                    'tbl_gasto_cuota_detalle.id_pagador_fk',
                    'tbl_gasto_cuota_detalle.importe_detalle',
                    'tbl_gasto_cuota_detalle.estado_detalle',
                    'tbl_gasto_cuota_detalle.pagado_detalle',
                    'tbl_usuario.nombre_usuario'
                )
                ->get();
        }

        return response()->json([
            'success' => true,
            'cuotas' => $cuotas,
            'detalles' => $detalles,
        ]);
    }

    public function getDatosEdicion(int $id)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_gestor_fk', $gestorId)
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'tipo_propiedad',
                'calle_propiedad',
                'numero_propiedad',
                'piso_propiedad',
                'puerta_propiedad',
                'ciudad_propiedad',
                'codigo_postal_propiedad',
                'descripcion_propiedad',
                'precio_propiedad',
                'estado_propiedad',
                'habitaciones_propiedad',
                'banos_propiedad',
                'metros_cuadrados_propiedad',
                'ascensor_propiedad',
                'amueblado_propiedad',
                'piscina_propiedad',
                'terraza_propiedad',
                'garaje_propiedad',
                'aire_acondicionado_propiedad',
                'calefaccion_propiedad',
                'trastero_propiedad',
                'adicional_propiedad'
            )
            ->first();

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);

        if (!$permisos->editar_propiedad) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar esta propiedad.'], 403);
        }

        return response()->json([
            'success' => true,
            'propiedad' => $propiedad,
            'permisos' => [
                'puede_editar_precio' => $permisos->gastos,
            ],
        ]);
    }

    public function actualizar(Request $request, int $id)
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_gestor_fk', $gestorId)
            ->first();

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        }

        $permisos = $this->getPermisosPropiedad($gestorId, $id);

        if (!$permisos->editar_propiedad) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para editar esta propiedad.'], 403);
        }

        $validated = $request->validate([
            'titulo_propiedad' => ['required', 'string', 'max:150'],
            'tipo_propiedad' => ['required', 'in:piso,casa,estudio,habitacion'],
            'calle_propiedad' => ['required', 'string', 'max:150'],
            'numero_propiedad' => ['required', 'string', 'max:20'],
            'piso_propiedad' => ['nullable', 'string', 'max:20'],
            'puerta_propiedad' => ['nullable', 'string', 'max:20'],
            'ciudad_propiedad' => ['required', 'string', 'max:100'],
            'codigo_postal_propiedad' => ['required', 'string', 'max:10'],
            'habitaciones_propiedad' => ['nullable', 'string', 'max:20'],
            'banos_propiedad' => ['nullable', 'integer', 'min:0'],
            'metros_cuadrados_propiedad' => ['nullable', 'integer', 'min:0'],
            'ascensor_propiedad' => ['nullable', 'boolean'],
            'amueblado_propiedad' => ['nullable', 'boolean'],
            'piscina_propiedad' => ['nullable', 'boolean'],
            'terraza_propiedad' => ['nullable', 'boolean'],
            'garaje_propiedad' => ['nullable', 'boolean'],
            'aire_acondicionado_propiedad' => ['nullable', 'boolean'],
            'calefaccion_propiedad' => ['nullable', 'boolean'],
            'trastero_propiedad' => ['nullable', 'boolean'],
            'adicional_propiedad' => ['nullable', 'string', 'max:255'],
            'precio_propiedad' => ['required', 'numeric', 'min:0'],
            'descripcion_propiedad' => ['nullable', 'string'],
        ]);

        $datosPropiedad = [
            'titulo_propiedad' => $validated['titulo_propiedad'],
            'tipo_propiedad' => $validated['tipo_propiedad'],
            'calle_propiedad' => $validated['calle_propiedad'],
            'numero_propiedad' => $validated['numero_propiedad'],
            'piso_propiedad' => $validated['piso_propiedad'] ?? null,
            'puerta_propiedad' => $validated['puerta_propiedad'] ?? null,
            'ciudad_propiedad' => $validated['ciudad_propiedad'],
            'codigo_postal_propiedad' => $validated['codigo_postal_propiedad'],
            'habitaciones_propiedad' => $validated['habitaciones_propiedad'] ?? null,
            'banos_propiedad' => $validated['banos_propiedad'] ?? null,
            'metros_cuadrados_propiedad' => $validated['metros_cuadrados_propiedad'] ?? null,
            'ascensor_propiedad' => (bool) ($validated['ascensor_propiedad'] ?? false),
            'amueblado_propiedad' => (bool) ($validated['amueblado_propiedad'] ?? false),
            'piscina_propiedad' => (bool) ($validated['piscina_propiedad'] ?? false),
            'terraza_propiedad' => (bool) ($validated['terraza_propiedad'] ?? false),
            'garaje_propiedad' => (bool) ($validated['garaje_propiedad'] ?? false),
            'aire_acondicionado_propiedad' => (bool) ($validated['aire_acondicionado_propiedad'] ?? false),
            'calefaccion_propiedad' => (bool) ($validated['calefaccion_propiedad'] ?? false),
            'trastero_propiedad' => (bool) ($validated['trastero_propiedad'] ?? false),
            'adicional_propiedad' => $validated['adicional_propiedad'] ?? null,
            'descripcion_propiedad' => $validated['descripcion_propiedad'] ?? null,
            'actualizado_propiedad' => now(),
        ];

        if ($permisos->gastos) {
            $datosPropiedad['precio_propiedad'] = $validated['precio_propiedad'];
        }

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update($datosPropiedad);

        $propiedadActualizada = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->select('id_propiedad', 'titulo_propiedad', 'precio_propiedad', 'estado_propiedad')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Propiedad actualizada correctamente.',
            'propiedad' => $propiedadActualizada,
        ]);
    }
}
