<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function inicio(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = null;
        $propiedadesActivas = 0;
        $inquilinosActivos = 0;
        $ingresosEsteMes = 0;
        $solicitudesPendientes = 0;
        $ultimasSolicitudes = collect();
        $propiedadesActivasDetalle = collect();
        $ingresosTotales = 0;
        $propiedadesPorEstado = [];
        $pagosPendientes = 0;
        $incidenciasPendientes = 0;
        $tasaOcupacion = 0;
        $totalPropiedades = 0;

        if ($arrendadorId !== null) {
            $arrendador = DB::table('tbl_usuario')
                ->select('id_usuario', 'nombre_usuario')
                ->where('id_usuario', $arrendadorId)
                ->first();

            $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();
            $selectDireccionPropiedad = $this->obtenerSelectDireccionPropiedad('p');

            $propiedadesActivas = DB::table('tbl_propiedad')
                ->where('id_arrendador_fk', $arrendadorId)
                ->whereIn('estado_propiedad', ['publicada', 'alquilada'])
                ->count();

            $inquilinosActivos = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->distinct()
                ->count('a.id_inquilino_fk');

            $inicioMes = Carbon::now()->startOfMonth()->toDateString();
            $finMes = Carbon::now()->endOfMonth()->toDateString();

            $ingresosEsteMes = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->whereBetween(DB::raw('DATE(COALESCE(a.aprobado_alquiler, a.creado_alquiler))'), [$inicioMes, $finMes])
                ->sum("p.{$columnaPrecio}");

            // Ingresos totales de alquileres activos
            $ingresosTotales = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->sum("p.{$columnaPrecio}");

            // Desglose de propiedades por estado
            $propiedadesPorEstado = DB::table('tbl_propiedad')
                ->where('id_arrendador_fk', $arrendadorId)
                ->select('estado_propiedad', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('estado_propiedad')
                ->get()
                ->keyBy('estado_propiedad');

            // Total de propiedades
            $totalPropiedades = DB::table('tbl_propiedad')
                ->where('id_arrendador_fk', $arrendadorId)
                ->count();

            // Tasa de ocupación (propiedades alquiladas / total de propiedades)
            $propiedadesAlquiladas = $propiedadesPorEstado->get('alquilada')?->cantidad ?? 0;
            $tasaOcupacion = $totalPropiedades > 0 ? round(($propiedadesAlquiladas / $totalPropiedades) * 100, 2) : 0;

            // Pagos pendientes del arrendador
            $pagosPendientes = DB::table('tbl_pago as pa')
                ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'pa.id_alquiler_fk')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('pa.estado_pago', 'pendiente')
                ->sum('pa.importe_pago');

            // Incidencias pendientes
            $incidenciasPendientes = DB::table('tbl_incidencia as i')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'i.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('i.estado_incidencia', 'abierta')
                ->count();

            $solicitudesPendientes = DB::table('tbl_solicitud_alquiler as s')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('s.estado_solicitud_alquiler', 'pendiente')
                ->count();

            $ultimasSolicitudes = DB::table('tbl_solicitud_alquiler as s')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
                ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 's.id_usuario_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->select(
                    's.id_solicitud_alquiler as id_alquiler',
                    'p.titulo_propiedad',
                    'inquilino.nombre_usuario as nombre_solicitante',
                    's.estado_solicitud_alquiler as estado_alquiler',
                    's.creado_solicitud_alquiler as creado_alquiler'
                )
                ->orderBy('s.creado_solicitud_alquiler', 'desc')
                ->limit(5)
                ->get();

            $propiedadesActivasDetalle = DB::table('tbl_propiedad as p')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->whereIn('p.estado_propiedad', ['publicada', 'alquilada'])
                ->select(
                    'p.id_propiedad',
                    'p.titulo_propiedad',
                    DB::raw($selectDireccionPropiedad),
                    'p.ciudad_propiedad',
                    DB::raw("p.{$columnaPrecio} as precio_propiedad"),
                    'p.estado_propiedad',
                    DB::raw("(
                        SELECT u.nombre_usuario
                        FROM tbl_alquiler a2
                        JOIN tbl_usuario u ON u.id_usuario = a2.id_inquilino_fk
                        WHERE a2.id_propiedad_fk = p.id_propiedad
                          AND a2.estado_alquiler = 'activo'
                        ORDER BY a2.aprobado_alquiler DESC, a2.creado_alquiler DESC
                        LIMIT 1
                    ) as nombre_inquilino_actual")
                )
                ->orderBy('p.creado_propiedad', 'desc')
                ->limit(10)
                ->get();
        }

        return view('arrendador.dashboard', [
            'arrendador' => $arrendador,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'propiedadesActivas' => $propiedadesActivas,
            'inquilinosActivos' => $inquilinosActivos,
            'ingresosEsteMes' => $ingresosEsteMes,
            'solicitudesPendientes' => $solicitudesPendientes,
            'ultimasSolicitudes' => $ultimasSolicitudes,
            'propiedadesActivasDetalle' => $propiedadesActivasDetalle,
            'ingresosTotales' => $ingresosTotales,
            'propiedadesPorEstado' => $propiedadesPorEstado,
            'pagosPendientes' => $pagosPendientes,
            'incidenciasPendientes' => $incidenciasPendientes,
            'tasaOcupacion' => $tasaOcupacion,
            'totalPropiedades' => $totalPropiedades,
        ]);
    }

    private function obtenerIdArrendador(Request $request): ?int
    {
        /** @var Usuario|null $usuarioAutenticado */
        if (Auth::check()) {
            $usuarioAutenticado = Auth::user();

            if ($usuarioAutenticado instanceof Usuario && $usuarioAutenticado->roles()->where('slug_rol', 'arrendador')->exists()) {
                return (int) ($usuarioAutenticado->id_usuario ?? $usuarioAutenticado->id ?? 0);
            }
        }

        $arrendadorIdEnConsulta = (int) $request->query('arrendador_id', 0);
        if ($arrendadorIdEnConsulta > 0) {
            return $arrendadorIdEnConsulta;
        }

        // Prioriza usuarios con actividad real como arrendador en propiedades/alquileres.
        $arrendadorConActividad = DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->leftJoin('tbl_alquiler as a', function ($union) {
                $union->on('a.id_propiedad_fk', '=', 'p.id_propiedad')
                    ->where('a.estado_alquiler', '=', 'activo');
            })
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select(
                'u.id_usuario',
                DB::raw('COUNT(DISTINCT p.id_propiedad) as total_propiedades'),
                DB::raw('COUNT(DISTINCT a.id_inquilino_fk) as total_inquilinos_activos')
            )
            ->orderByDesc('total_inquilinos_activos')
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

        return $arrendadorDesdePropiedad ? (int) $arrendadorDesdePropiedad : null;
    }

    private function obtenerColumnaPrecioPropiedad(): string
    {
        return 'precio_propiedad';
    }

    private function obtenerSelectDireccionPropiedad(string $aliasTabla = 'p'): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return "{$aliasTabla}.direccion_propiedad as direccion_propiedad";
        }

        $partesDireccion = [];
        foreach (['calle_propiedad', 'numero_propiedad', 'piso_propiedad', 'puerta_propiedad'] as $columna) {
            if (Schema::hasColumn('tbl_propiedad', $columna)) {
                $partesDireccion[] = "NULLIF(TRIM({$aliasTabla}.{$columna}), '')";
            }
        }

        if (empty($partesDireccion)) {
            return "'' as direccion_propiedad";
        }

        return 'TRIM(CONCAT_WS(\' \' , ' . implode(', ', $partesDireccion) . ')) as direccion_propiedad';
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
