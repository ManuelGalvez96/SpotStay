<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PropiedadController extends Controller
{
    public function index(Request $request)
    {
        $gestor = Auth::user();
        $gestorId = $gestor?->id_usuario;

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
            ->where('tbl_propiedad.id_gestor_fk', $gestorId)
            ->where('tbl_propiedad.estado_propiedad', '!=', 'borrador');

        $query = clone $baseQuery;

        $q = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', '');
        $ciudad = trim((string) $request->query('ciudad', ''));
        $operativo = (string) $request->query('operativo', '');
        $sort = (string) $request->query('sort', 'creado_propiedad');
        $dir = strtolower((string) $request->query('dir', 'desc'));

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $q . '%')
                    ->orWhere('tbl_propiedad.direccion_propiedad', 'like', '%' . $q . '%')
                    ->orWhere('arrendador.nombre_usuario', 'like', '%' . $q . '%');
            });
        }

        if ($estado !== '') {
            $query->where('tbl_propiedad.estado_propiedad', $estado);
        }

        if ($ciudad !== '') {
            $query->where('tbl_propiedad.ciudad_propiedad', 'like', '%' . $ciudad . '%');
        }

        if ($operativo === 'criticas') {
            $query->whereRaw('COALESCE(inc_criticas.total_incidencias_criticas, 0) > 0');
        }

        if ($operativo === 'sin_alquiler') {
            $query->whereRaw('COALESCE(alq_activos.total_alquileres_activos, 0) = 0');
        }

        if ($operativo === 'estables') {
            $query->whereRaw('COALESCE(inc_activas.total_incidencias_activas, 0) = 0')
                ->whereRaw('COALESCE(alq_activos.total_alquileres_activos, 0) > 0');
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

        $propiedades = $query
            ->select(
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.direccion_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.codigo_postal_propiedad',
                'tbl_propiedad.estado_propiedad',
                'tbl_propiedad.precio_propiedad',
                'tbl_propiedad.creado_propiedad',
                'arrendador.nombre_usuario as nombre_arrendador',
                DB::raw('COALESCE(alq_activos.total_alquileres_activos, 0) as total_alquileres_activos'),
                DB::raw('COALESCE(inc_activas.total_incidencias_activas, 0) as total_incidencias_activas'),
                DB::raw('COALESCE(inc_criticas.total_incidencias_criticas, 0) as total_incidencias_criticas')
            )
            ->orderBy($sortColumn, $sortDir)
            ->orderBy('tbl_propiedad.id_propiedad', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalAsignadas = (clone $baseQuery)->count();
        $totalPublicadas = (clone $baseQuery)->where('tbl_propiedad.estado_propiedad', 'publicada')->count();
        $totalAlquiladas = (clone $baseQuery)->where('tbl_propiedad.estado_propiedad', 'alquilada')->count();
        $totalConCriticas = (clone $baseQuery)
            ->whereRaw('COALESCE(inc_criticas.total_incidencias_criticas, 0) > 0')
            ->count();
        $totalSinAlquiler = (clone $baseQuery)
            ->whereRaw('COALESCE(alq_activos.total_alquileres_activos, 0) = 0')
            ->count();

        return view('gestor.propiedades', compact(
            'propiedades',
            'totalAsignadas',
            'totalPublicadas',
            'totalAlquiladas',
            'totalConCriticas',
            'totalSinAlquiler',
            'q',
            'estado',
            'ciudad',
            'operativo',
            'sort',
            'dir'
        ));
    }

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
                'creado_incidencia'
            )
            ->orderBy('creado_incidencia', 'desc')
            ->limit(10)
            ->get();

        $totalesIncidencia = [
            'abiertas' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'abierta')->count(),
            'en_proceso' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'en_proceso')->count(),
            'resueltas' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->where('estado_incidencia', 'resuelta')->count(),
        ];

        return view('gestor.propiedad', compact(
            'propiedad',
            'alquileresActivos',
            'incidenciasRecientes',
            'totalesIncidencia',
            'gastosHabilitados',
            'resumenGastos',
            'pagosPrincipales',
            'cuotasGasto',
            'cuotasDetallePorId'
        ));
    }

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
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador',
                'gestor.nombre_usuario as nombre_gestor'
            )
            ->first();

        if (!$propiedad) {
            abort(404);
        }

        $gastosData = $this->obtenerDatosGastosPropiedad($propiedad, $gestorId);

        return view('gestor.propiedad-gastos', [
            'propiedad' => $propiedad,
            'gastosHabilitados' => $gastosData['gastosHabilitados'],
            'resumenGastos' => $gastosData['resumenGastos'],
            'pagosPrincipales' => $gastosData['pagosPrincipales'],
            'cuotasGasto' => $gastosData['cuotasGasto'],
            'cuotasDetallePorId' => $gastosData['cuotasDetallePorId'],
        ]);
    }

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

        $validated = $request->validate([
            'concepto_gasto' => ['required', 'string', 'max:200'],
            'categoria_gasto' => ['nullable', 'string', 'max:50'],
            'importe_gasto' => ['required', 'numeric', 'min:0.01'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:28'],
            'fecha_inicio_gasto' => ['required', 'date'],
        ]);

        $inicio = Carbon::parse($validated['fecha_inicio_gasto'])->startOfMonth();
        $hoy = Carbon::today()->startOfMonth();

        if ($inicio->greaterThan($hoy)) {
            throw ValidationException::withMessages([
                'fecha_inicio_gasto' => 'La fecha de inicio no puede ser posterior al mes actual.',
            ]);
        }

        $inquilinos = $this->getInquilinosActivosIds($id);
        if ($inquilinos->isEmpty()) {
            throw ValidationException::withMessages([
                'concepto_gasto' => 'No hay inquilinos activos para repartir este gasto.',
            ]);
        }

        DB::transaction(function () use ($id, $gestorId, $validated, $inicio, $hoy, $inquilinos) {
            $ahora = now();
            $gastoId = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_gestor_fk' => $gestorId,
                'concepto_gasto' => trim($validated['concepto_gasto']),
                'categoria_gasto' => trim((string) ($validated['categoria_gasto'] ?? '')) !== '' ? trim((string) $validated['categoria_gasto']) : null,
                'importe_gasto' => round((float) $validated['importe_gasto'], 2),
                'pagador_gasto' => 'inquilinos',
                'periodicidad_gasto' => 'mensual',
                'dia_vencimiento' => (int) $validated['dia_vencimiento'],
                'fecha_inicio_gasto' => $inicio->toDateString(),
                'fecha_fin_gasto' => null,
                'estado_gasto' => 'activo',
                'creado_gasto' => $ahora,
                'actualizado_gasto' => $ahora,
            ]);

            $cursor = $inicio->copy();
            while ($cursor->lessThanOrEqualTo($hoy)) {
                $this->crearCuotaMensualConDetalles(
                    $gastoId,
                    $cursor->copy(),
                    round((float) $validated['importe_gasto'], 2),
                    (int) $validated['dia_vencimiento'],
                    'inquilinos',
                    $gestorId,
                    $inquilinos
                );
                $cursor->addMonth();
            }
        });

        return redirect()->back()->with('success', 'Gasto creado correctamente y cuotas mensuales generadas.');
    }

    public function marcarPagoGasto(Request $request, int $id, int $cuotaId, int $detalleId)
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

        DB::transaction(function () use ($id, $cuotaId, $detalleId) {
            $detalle = DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto.id_propiedad_fk', $id)
                ->where('tbl_gasto_cuota_detalle.id_gasto_cuota_detalle', $detalleId)
                ->where('tbl_gasto_cuota_detalle.id_gasto_cuota_fk', $cuotaId)
                ->select('tbl_gasto_cuota_detalle.id_gasto_cuota_detalle', 'tbl_gasto_cuota_detalle.estado_detalle')
                ->first();

            if (!$detalle) {
                abort(404);
            }

            if ($detalle->estado_detalle !== 'pagado') {
                DB::table('tbl_gasto_cuota_detalle')
                    ->where('id_gasto_cuota_detalle', $detalleId)
                    ->update([
                        'estado_detalle' => 'pagado',
                        'pagado_detalle' => now(),
                        'actualizado_detalle' => now(),
                    ]);
            }

            $this->actualizarEstadoCuota($cuotaId);
        });

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }

    private function getPropiedadDelGestor(int $propiedadId, int $gestorId): ?object
    {
        return DB::table('tbl_propiedad')
            ->where('id_propiedad', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->first();
    }

    private function getInquilinosActivosIds(int $propiedadId)
    {
        return DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('estado_alquiler', 'activo')
            ->distinct()
            ->pluck('id_inquilino_fk');
    }

    private function ensureCuotasMensualesGeneradas(int $propiedadId, int $gestorId): void
    {
        $hoy = Carbon::today()->startOfMonth();

        $gastos = DB::table('tbl_gasto')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->where('estado_gasto', 'activo')
            ->get();

        $inquilinos = $this->getInquilinosActivosIds($propiedadId);

        foreach ($gastos as $gasto) {
            $inicio = Carbon::parse($gasto->fecha_inicio_gasto)->startOfMonth();
            $fin = $gasto->fecha_fin_gasto
                ? Carbon::parse($gasto->fecha_fin_gasto)->startOfMonth()
                : $hoy;

            if ($fin->greaterThan($hoy)) {
                $fin = $hoy->copy();
            }

            if ($inicio->greaterThan($fin)) {
                continue;
            }

            $cursor = $inicio->copy();
            while ($cursor->lessThanOrEqualTo($fin)) {
                $existe = DB::table('tbl_gasto_cuota')
                    ->where('id_gasto_fk', $gasto->id_gasto)
                    ->where('mes_cuota', $cursor->toDateString())
                    ->exists();

                if (!$existe) {
                    $this->crearCuotaMensualConDetalles(
                        (int) $gasto->id_gasto,
                        $cursor->copy(),
                        (float) $gasto->importe_gasto,
                        (int) $gasto->dia_vencimiento,
                        (string) $gasto->pagador_gasto,
                        $gestorId,
                        $inquilinos
                    );
                }

                $cursor->addMonth();
            }
        }
    }

    private function sincronizarGastosBaseDePropiedad(object $propiedad, int $gestorId): void
    {
        $gastosRaw = $propiedad->gastos_propiedad ?? null;
        if (!$gastosRaw) {
            return;
        }

        $gastos = json_decode((string) $gastosRaw, true);
        if (!is_array($gastos) || empty($gastos)) {
            return;
        }

        $inicioBase = $propiedad->creado_propiedad
            ? Carbon::parse((string) $propiedad->creado_propiedad)->startOfMonth()->toDateString()
            : Carbon::today()->startOfMonth()->toDateString();

        foreach ($gastos as $concepto => $importe) {
            $importeNormalizado = (float) $importe;
            if ($importeNormalizado <= 0) {
                continue;
            }

            $conceptoTexto = trim((string) $concepto);
            if ($conceptoTexto === '') {
                continue;
            }

            $conceptoCanonico = strtolower($conceptoTexto);

            $existe = DB::table('tbl_gasto')
                ->where('id_propiedad_fk', (int) $propiedad->id_propiedad)
                ->whereRaw('LOWER(concepto_gasto) = ?', [$conceptoCanonico])
                ->exists();

            if ($existe) {
                continue;
            }

            DB::table('tbl_gasto')->insert([
                'id_propiedad_fk' => (int) $propiedad->id_propiedad,
                'id_gestor_fk' => $gestorId,
                'concepto_gasto' => ucfirst($conceptoTexto),
                'categoria_gasto' => 'base_propiedad',
                'importe_gasto' => round($importeNormalizado, 2),
                'pagador_gasto' => 'inquilinos',
                'periodicidad_gasto' => 'mensual',
                'dia_vencimiento' => 5,
                'fecha_inicio_gasto' => $inicioBase,
                'fecha_fin_gasto' => null,
                'estado_gasto' => 'activo',
                'creado_gasto' => now(),
                'actualizado_gasto' => now(),
            ]);
        }
    }

    private function crearCuotaMensualConDetalles(
        int $gastoId,
        Carbon $mes,
        float $importeTotal,
        int $diaVencimiento,
        string $pagadorGasto,
        int $gestorId,
        $inquilinos
    ): void {
        $mesBase = $mes->copy()->startOfMonth();
        $ultimoDia = (int) $mesBase->copy()->endOfMonth()->day;
        $dia = min($diaVencimiento, $ultimoDia);
        $vencimiento = $mesBase->copy()->day($dia);

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

        if ($pagadorGasto === 'gestor' || $inquilinos->isEmpty()) {
            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk' => $cuotaId,
                'id_pagador_fk' => $gestorId,
                'importe_detalle' => round($importeTotal, 2),
                'estado_detalle' => 'pendiente',
                'pagado_detalle' => null,
                'creado_detalle' => now(),
                'actualizado_detalle' => now(),
            ]);

            return;
        }

        $totalInquilinos = max(1, (int) $inquilinos->count());
        $base = floor((($importeTotal / $totalInquilinos) * 100)) / 100;
        $acumulado = 0.0;

        foreach ($inquilinos->values() as $index => $idInquilino) {
            $importeDetalle = $index === $totalInquilinos - 1
                ? round($importeTotal - $acumulado, 2)
                : round($base, 2);

            $acumulado += $importeDetalle;

            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk' => $cuotaId,
                'id_pagador_fk' => (int) $idInquilino,
                'importe_detalle' => $importeDetalle,
                'estado_detalle' => 'pendiente',
                'pagado_detalle' => null,
                'creado_detalle' => now(),
                'actualizado_detalle' => now(),
            ]);
        }
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
            'alquiler' => ['label' => 'Alquiler', 'importe' => (float) $propiedad->precio_propiedad, 'estado' => 'pendiente'],
            'luz' => ['label' => 'Luz', 'importe' => 0.0, 'estado' => 'sin_dato'],
            'agua' => ['label' => 'Agua', 'importe' => 0.0, 'estado' => 'sin_dato'],
            'gas' => ['label' => 'Gas', 'importe' => 0.0, 'estado' => 'sin_dato'],
            'internet' => ['label' => 'Internet', 'importe' => 0.0, 'estado' => 'sin_dato'],
            'comunidad' => ['label' => 'Comunidad', 'importe' => 0.0, 'estado' => 'sin_dato'],
        ];
        $cuotasGasto = collect();
        $cuotasDetallePorId = collect();

        if ($gastosHabilitados) {
            $propiedadId = (int) $propiedad->id_propiedad;
            $this->sincronizarGastosBaseDePropiedad($propiedad, $gestorId);
            $this->normalizarPagadoresSoloInquilinos($propiedadId, $gestorId);
            $this->ensureCuotasMensualesGeneradas($propiedadId, $gestorId);

            $hoy = Carbon::today()->toDateString();
            $mesActual = Carbon::today()->startOfMonth()->toDateString();
            $inicioMes = Carbon::today()->startOfMonth()->toDateString();
            $finMes = Carbon::today()->endOfMonth()->toDateString();

            $resumenGastos = [
                'mensual_total' => (float) DB::table('tbl_gasto')
                    ->where('id_propiedad_fk', $propiedadId)
                    ->where('estado_gasto', 'activo')
                    ->sum('importe_gasto') + (float) $propiedad->precio_propiedad,
                'pendientes_mes' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->where('tbl_gasto_cuota.mes_cuota', $mesActual)
                    ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                    ->count(),
                'total_pendiente_importe' => (float) DB::table('tbl_gasto_cuota_detalle')
                    ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->where('tbl_gasto_cuota_detalle.estado_detalle', '!=', 'pagado')
                    ->sum('tbl_gasto_cuota_detalle.importe_detalle'),
                'atrasados' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                    ->where('tbl_gasto_cuota.vencimiento_cuota', '<', $hoy)
                    ->count(),
                'pagados_mes' => DB::table('tbl_gasto_cuota')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                    ->where('tbl_gasto_cuota.mes_cuota', $mesActual)
                    ->where('tbl_gasto_cuota.estado_cuota', 'pagado')
                    ->count(),
            ];

            $cuotasPrincipalesMes = DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                ->where('tbl_gasto.id_propiedad_fk', $propiedadId)
                ->where('tbl_gasto_cuota.mes_cuota', $mesActual)
                ->select(
                    'tbl_gasto.concepto_gasto',
                    'tbl_gasto_cuota.importe_total_cuota',
                    'tbl_gasto_cuota.estado_cuota'
                )
                ->get();

            foreach ($cuotasPrincipalesMes as $cuotaPrincipal) {
                $clave = $this->normalizarConceptoPrincipal((string) $cuotaPrincipal->concepto_gasto);
                if (!$clave || !array_key_exists($clave, $pagosPrincipales) || $clave === 'alquiler') {
                    continue;
                }

                $pagosPrincipales[$clave]['importe'] += (float) $cuotaPrincipal->importe_total_cuota;

                $estadoActual = (string) $pagosPrincipales[$clave]['estado'];
                $estadoCuota = (string) $cuotaPrincipal->estado_cuota;

                if ($estadoActual === 'sin_dato') {
                    $pagosPrincipales[$clave]['estado'] = $estadoCuota;
                } elseif ($estadoActual !== 'atrasado' && $estadoCuota === 'atrasado') {
                    $pagosPrincipales[$clave]['estado'] = 'atrasado';
                } elseif ($estadoActual === 'pagado' && in_array($estadoCuota, ['pendiente', 'parcial'], true)) {
                    $pagosPrincipales[$clave]['estado'] = $estadoCuota;
                }
            }

            $alquileresIds = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $propiedadId)
                ->where('estado_alquiler', 'activo')
                ->pluck('id_alquiler')
                ->all();

            if (empty($alquileresIds)) {
                $pagosPrincipales['alquiler']['estado'] = 'sin_dato';
            } else {
                $pagosMes = DB::table('tbl_pago')
                    ->whereIn('id_alquiler_fk', $alquileresIds)
                    ->where('tipo_pago', 'mensualidad')
                    ->whereBetween('mes_pago', [$inicioMes, $finMes])
                    ->select('id_alquiler_fk', 'estado_pago')
                    ->get();

                $atrasadosPrevios = DB::table('tbl_pago')
                    ->whereIn('id_alquiler_fk', $alquileresIds)
                    ->where('tipo_pago', 'mensualidad')
                    ->where('mes_pago', '<', $inicioMes)
                    ->where('estado_pago', '!=', 'confirmado')
                    ->exists();

                if ($atrasadosPrevios) {
                    $pagosPrincipales['alquiler']['estado'] = 'atrasado';
                } elseif ($pagosMes->isEmpty()) {
                    $pagosPrincipales['alquiler']['estado'] = 'pendiente';
                } else {
                    $confirmados = $pagosMes->where('estado_pago', 'confirmado')->pluck('id_alquiler_fk')->unique()->count();
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

                if ((float) $pagoPrincipal['importe'] <= 0 && $pagoPrincipal['estado'] !== 'sin_dato') {
                    $pagosPrincipales[$clave]['estado'] = 'sin_dato';
                }

                $pagosPrincipales[$clave]['importe'] = round((float) $pagosPrincipales[$clave]['importe'], 2);
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
                    'tbl_gasto.pagador_gasto'
                )
                ->orderBy('tbl_gasto_cuota.mes_cuota', 'desc')
                ->orderBy('tbl_gasto_cuota.vencimiento_cuota', 'asc')
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
        }

        return [
            'gastosHabilitados' => $gastosHabilitados,
            'resumenGastos' => $resumenGastos,
            'pagosPrincipales' => $pagosPrincipales,
            'cuotasGasto' => $cuotasGasto,
            'cuotasDetallePorId' => $cuotasDetallePorId,
        ];
    }

    private function normalizarPagadoresSoloInquilinos(int $propiedadId, int $gestorId): void
    {
        DB::table('tbl_gasto')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->where('pagador_gasto', '!=', 'inquilinos')
            ->update([
                'pagador_gasto' => 'inquilinos',
                'actualizado_gasto' => now(),
            ]);

        $inquilinos = $this->getInquilinosActivosIds($propiedadId);
        if ($inquilinos->isEmpty()) {
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
            DB::transaction(function () use ($cuota, $inquilinos) {
                DB::table('tbl_gasto_cuota_detalle')
                    ->where('id_gasto_cuota_fk', $cuota->id_gasto_cuota)
                    ->delete();

                $totalInquilinos = max(1, (int) $inquilinos->count());
                $importeTotal = (float) $cuota->importe_total_cuota;
                $base = floor((($importeTotal / $totalInquilinos) * 100)) / 100;
                $acumulado = 0.0;

                foreach ($inquilinos->values() as $index => $idInquilino) {
                    $importeDetalle = $index === $totalInquilinos - 1
                        ? round($importeTotal - $acumulado, 2)
                        : round($base, 2);

                    $acumulado += $importeDetalle;

                    DB::table('tbl_gasto_cuota_detalle')->insert([
                        'id_gasto_cuota_fk' => (int) $cuota->id_gasto_cuota,
                        'id_pagador_fk' => (int) $idInquilino,
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
}
