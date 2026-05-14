<?php

namespace App\Http\Controllers\inquilino;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use App\Services\InquilinoFinanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InquilinoController extends Controller
{
    protected $financeService;

    public function __construct(InquilinoFinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function gestionarPropiedades(Request $request)
    {
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $userId = $usuario->id_usuario;
        $this->financeService->actualizarCuotasAtrasadas($userId);

        // Auto-finalizar contratos que expiraron hace más de 7 días
        DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'activo')
            ->whereNotNull('fecha_fin_alquiler')
            ->where('fecha_fin_alquiler', '<', now()->subDays(7))
            ->update(['estado_alquiler' => 'finalizado', 'actualizado_alquiler' => now()]);

        $tieneAlquiler = DB::table('tbl_alquiler')->where('id_inquilino_fk', $userId)->where('estado_alquiler', 'activo')->exists() ||
            DB::table('tbl_alquiler')->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')->where('tbl_propiedad.id_arrendador_fk', $userId)->where('tbl_alquiler.estado_alquiler', 'activo')->exists();

        if (!$tieneAlquiler) {
            return redirect($usuario->roles()->where('slug_rol', 'admin')->exists() ? '/admin/dashboard' : '/miembro/inicio')->with('error', 'Acceso restringido.');
        }

        $totalContratos = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($q) {
                $q->whereNull('tbl_alquiler.fecha_fin_alquiler')
                    ->orWhere('tbl_alquiler.fecha_fin_alquiler', '>=', now()->subDays(7));
            })
            ->where(fn($q) => $q->where('tbl_alquiler.id_inquilino_fk', $userId)->orWhere('tbl_propiedad.id_arrendador_fk', $userId))
            ->count(DB::raw('DISTINCT tbl_propiedad.id_propiedad'));

        $proximoPago = AlquilerCuota::query()
            ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
            ->where('tbl_alquiler.id_inquilino_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($q) {
                $q->whereNull('tbl_alquiler.fecha_fin_alquiler')
                    ->orWhere('tbl_alquiler.fecha_fin_alquiler', '>=', now()->subDays(7));
            })
            ->whereIn('tbl_alquiler_cuota.estado', ['pendiente', 'atrasado'])
            ->orderBy('tbl_alquiler_cuota.mes_cuota', 'asc')
            ->first();
        $diasParaPago = $proximoPago ? max(0, round(Carbon::now()->diffInDays(Carbon::parse($proximoPago->mes_cuota)->day(1), false))) : round(Carbon::now()->diffInDays(Carbon::now()->addMonth()->day(1)));

        $totalIncidencias = DB::table('tbl_incidencia')->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')->leftJoin('tbl_alquiler', fn($j) => $j->on('tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')->where('tbl_alquiler.id_inquilino_fk', $userId)->where('tbl_alquiler.estado_alquiler', 'activo'))->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])->where(fn($q) => $q->where('tbl_propiedad.id_arrendador_fk', $userId)->orWhereNotNull('tbl_alquiler.id_alquiler'))->count(DB::raw('DISTINCT tbl_incidencia.id_incidencia'));

        $query = DB::table('tbl_propiedad')
            ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->leftJoin('tbl_fotos', fn($j) => $j->on('tbl_fotos.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')->whereRaw('tbl_fotos.id_foto = (select min(id_foto) from tbl_fotos where id_propiedad_fk = tbl_propiedad.id_propiedad)'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($q) {
                $q->whereNull('tbl_alquiler.fecha_fin_alquiler')
                    ->orWhere('tbl_alquiler.fecha_fin_alquiler', '>=', now()->subDays(7));
            })
            ->where(fn($q) => $q->where('tbl_alquiler.id_inquilino_fk', $userId)->orWhere('tbl_propiedad.id_arrendador_fk', $userId));

        if ($request->filled('q')) $query->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $request->q . '%');
        if ($request->filled('ciudad')) $query->where('tbl_propiedad.ciudad_propiedad', $request->ciudad);

        $alquileres = $query->select('tbl_propiedad.*', DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"), DB::raw('MIN(tbl_fotos.ruta_foto) as ruta_foto'), DB::raw('MIN(tbl_alquiler.id_alquiler) as id_alquiler'), DB::raw('MIN(tbl_alquiler.estado_alquiler) as estado_alquiler'), DB::raw('MIN(tbl_alquiler.fecha_inicio_alquiler) as fecha_inicio_alquiler'), DB::raw('MIN(CASE WHEN tbl_alquiler.id_inquilino_fk = ' . $userId . ' THEN tbl_alquiler.fecha_fin_alquiler END) as fecha_fin_alquiler'))->groupBy('tbl_propiedad.id_propiedad')->get();

        foreach ($alquileres as $alquiler) {
            $resumen = $this->financeService->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);
            $alquiler->estado_pago_actual = $resumen['estado_pago_actual'];
            $alquiler->dias_para_pago = $resumen['dias_para_pago'];
            $alquiler->fecha_proximo_pago = $resumen['fecha_proximo_pago'];
            $alquiler->pago_atrasado = $resumen['num_pagos_atrasados'];

            // Dividir deuda entre compañeros de piso
            $numInquilinos = max(1, DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $alquiler->id_propiedad)
                ->where('estado_alquiler', 'activo')
                ->count());
            $alquiler->total_deuda_individual = $resumen['total_deuda'];
            $alquiler->num_companeros = $numInquilinos;
            if ($numInquilinos > 1) {
                $alquiler->total_deuda_individual /= $numInquilinos;
            }

            $alquiler->num_gastos_pendientes = Schema::hasTable('tbl_gasto_cuota_detalle') ? DB::table('tbl_gasto_cuota_detalle')->where('id_alquiler_fk', $alquiler->id_alquiler)->where('id_pagador_fk', $userId)->whereIn('estado_detalle', ['pendiente', 'atrasado'])->count() : 0;

            $alquiler->total_incidencias_propiedad = DB::table('tbl_incidencia')->where('id_propiedad_fk', $alquiler->id_propiedad)->whereIn('estado_incidencia', ['abierta', 'en_proceso'])->count();

            $alquiler->nombres_companeros = DB::table('tbl_alquiler')->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')->where('tbl_alquiler.id_propiedad_fk', $alquiler->id_propiedad)->where('tbl_alquiler.estado_alquiler', 'activo')->where('tbl_alquiler.id_inquilino_fk', '<>', $userId)->pluck('tbl_usuario.nombre_usuario')->toArray();

            $alquiler->banner_foto_url = $alquiler->ruta_foto ? asset('public/img/' . $alquiler->ruta_foto) : null;

            if (!empty($alquiler->fecha_fin_alquiler)) {
                $fin = Carbon::parse($alquiler->fecha_fin_alquiler)->startOfDay();
                $alquiler->diasFinContrato = (int) Carbon::today()->diffInDays($fin, false);
                $alquiler->mostrarAlertaFin = $alquiler->diasFinContrato <= 30;
                $alquiler->haExpirado = Carbon::today()->gt($fin);
                $alquiler->diasExpirado = $alquiler->haExpirado ? abs($alquiler->diasFinContrato) : 0;
            } else {
                $alquiler->mostrarAlertaFin = false;
                $alquiler->haExpirado = false;
                $alquiler->diasExpirado = 0;
                $alquiler->diasFinContrato = null;
            }
        }

        if ($request->ajax()) return view('inquilino.partials.grid_propiedades', compact('alquileres'))->render();

        return view('inquilino.gestionar_propiedades', [
            'totalContratos' => $totalContratos,
            'diasParaPago' => $diasParaPago,
            'totalIncidencias' => $totalIncidencias,
            'alquileres' => $alquileres,
            'ciudades' => $alquileres->pluck('ciudad_propiedad')->filter()->unique()->sort()->values()
        ]);
    }

    public function verPropiedad($id)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');
        $userId = $usuario->id_usuario;
        $this->financeService->actualizarCuotasAtrasadas($userId);

        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->leftJoin('tbl_contrato', 'tbl_contrato.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->leftJoin('tbl_usuario as propietario', 'propietario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->where('tbl_alquiler.id_propiedad_fk', $id)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(fn($q) => $q->where('tbl_alquiler.id_inquilino_fk', $userId)->orWhere('tbl_propiedad.id_arrendador_fk', $userId))
            ->select('tbl_alquiler.*', 'tbl_propiedad.*', DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"), 'tbl_contrato.url_pdf_contrato', 'propietario.nombre_usuario as nombre_propietario')
            ->first();

        if (!$alquiler) return redirect()->route('gestionar_propiedades');

        // Lógica de contrato (Alerta fin de contrato)
        $proximaFinalizacion = false;
        $diasParaFinContrato = null;
        $fechaFinContrato = null;
        $esIndefinido = false;
        $diasRestantesMes = null;
        $hoy = Carbon::today();

        if (!empty($alquiler->fecha_fin_alquiler)) {
            $finContrato = Carbon::parse($alquiler->fecha_fin_alquiler)->startOfDay();
            if ($finContrato->format('Y-m-d') === $hoy->format('Y-m-d')) {
                $proximaFinalizacion = true;
                $diasParaFinContrato = 0;
                $fechaFinContrato = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            } elseif ($finContrato->gt($hoy)) {
                $diasParaFinContrato = (int) $hoy->diffInDays($finContrato);
                if ($diasParaFinContrato <= 30) {
                    $proximaFinalizacion = true;
                    $fechaFinContrato = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                }
            } else {
                $proximaFinalizacion = true;
                $diasParaFinContrato = -1 * (int) $finContrato->diffInDays($hoy);
                $fechaFinContrato = $finContrato->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
            }
        } else {
            $esIndefinido = true;
            $diasRestantesMes = (int) $hoy->diffInDays($hoy->copy()->endOfMonth()->startOfDay());
        }

        $resumen = $this->financeService->obtenerResumenPagoAlquiler((int) $alquiler->id_alquiler, $alquiler->fecha_inicio_alquiler);

        // Gastos de suministros
        $totalGastosPendientes = 0;
        $numGastosPendientes = 0;
        $conceptosGastos = "";
        $listaGastos = collect();

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
            $conceptosGastos = implode(", ", $listaGastos->pluck('concepto_gasto')->unique()->toArray());
        }

        $numInquilinos = max(1, DB::table('tbl_alquiler')->where('id_propiedad_fk', $alquiler->id_propiedad_fk)->where('estado_alquiler', 'activo')->count());
        $totalDeuda = $resumen['total_deuda'];

        if ($numInquilinos > 1) {
            $totalDeuda /= $numInquilinos;
            $totalGastosPendientes /= $numInquilinos;
        }

        $fotos = DB::table('tbl_fotos')->where('id_propiedad_fk', $id)->get();
        $fotoPrincipal = $fotos->isNotEmpty() ? asset('public/img/' . $fotos->first()->ruta_foto) : null;

        return view('inquilino.ver_propiedad', [
            'alquiler' => $alquiler,
            'fotos' => $fotos->map(fn($f) => (object)['url_foto' => asset('public/img/' . $f->ruta_foto)]),
            'fotoPrincipal' => $fotoPrincipal,
            'proximaFinalizacion' => $proximaFinalizacion,
            'diasParaFinContrato' => $diasParaFinContrato,
            'fechaFinContrato' => $fechaFinContrato,
            'esIndefinido' => $esIndefinido,
            'numPagosAtrasados' => $resumen['num_pagos_atrasados'],
            'totalDeuda' => $totalDeuda,
            'totalGastosPendientes' => $totalGastosPendientes,
            'numGastosPendientes' => $numGastosPendientes,
            'companeros' => DB::table('tbl_alquiler')->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')->where('tbl_alquiler.id_propiedad_fk', $id)->where('tbl_alquiler.estado_alquiler', 'activo')->where('tbl_alquiler.id_inquilino_fk', '<>', $userId)->pluck('tbl_usuario.nombre_usuario')->toArray(),
            'incidencias' => DB::table('tbl_incidencia')->where('id_propiedad_fk', $id)->orderBy('creado_incidencia', 'desc')->get(),
            'esInquilino' => true,
            'pdfEjemplo' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
        ]);
    }

    public function obtenerEstadoContrato($id)
    {
        $alquiler = DB::table('tbl_alquiler')->where('id_alquiler', $id)->first();
        if (!$alquiler) return response()->json(['error' => 'No encontrado'], 404);
        $fechaFin = $alquiler->fecha_fin_alquiler ? Carbon::parse($alquiler->fecha_fin_alquiler)->endOfDay() : null;
        $expirado = $fechaFin && Carbon::now()->gt($fechaFin);
        return response()->json([
            'es_indefinido' => empty($fechaFin),
            'expirado' => $expirado,
            'dias_exceso' => $expirado ? (int) $fechaFin->diffInDays(Carbon::now()) : 0,
            'semana_excedida' => $expirado && $fechaFin->diffInDays(Carbon::now()) >= 7
        ]);
    }
}
