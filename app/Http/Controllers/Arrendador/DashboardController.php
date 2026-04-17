<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
        $mensajesRecientes = collect();
        $propiedadesActivasDetalle = collect();

        if ($arrendadorId !== null) {
            $arrendador = DB::table('tbl_usuario')
                ->select('id_usuario', 'nombre_usuario')
                ->where('id_usuario', $arrendadorId)
                ->first();

            $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();
            [$mensajeRemitenteColumna, $mensajeCuerpoColumna] = $this->obtenerColumnasMensaje();

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

            $solicitudesPendientes = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'pendiente')
                ->count();

            $ultimasSolicitudes = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->select(
                    'a.id_alquiler',
                    'p.titulo_propiedad',
                    'inquilino.nombre_usuario as nombre_solicitante',
                    'a.estado_alquiler',
                    'a.creado_alquiler'
                )
                ->orderBy('a.creado_alquiler', 'desc')
                ->limit(5)
                ->get();

            $mensajesRecientes = DB::table('tbl_mensaje as m')
                ->join('tbl_conversacion_usuario as cu', 'cu.id_conversacion_fk', '=', 'm.id_conversacion_fk')
                ->join('tbl_usuario as remitente', 'remitente.id_usuario', '=', "m.{$mensajeRemitenteColumna}")
                ->where('cu.id_usuario_fk', $arrendadorId)
                ->where("m.{$mensajeRemitenteColumna}", '!=', $arrendadorId)
                ->select(
                    'remitente.nombre_usuario',
                    DB::raw("m.{$mensajeCuerpoColumna} as cuerpo_mensaje"),
                    'm.creado_mensaje'
                )
                ->orderBy('m.creado_mensaje', 'desc')
                ->limit(5)
                ->get();

            $propiedadesActivasDetalle = DB::table('tbl_propiedad as p')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->whereIn('p.estado_propiedad', ['publicada', 'alquilada'])
                ->select(
                    'p.id_propiedad',
                    'p.titulo_propiedad',
                    'p.direccion_propiedad',
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
            'mensajesRecientes' => $mensajesRecientes,
            'propiedadesActivasDetalle' => $propiedadesActivasDetalle,
        ]);
    }

    private function obtenerIdArrendador(Request $request): ?int
    {
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
        if (Schema::hasColumn('tbl_propiedad', 'precio_propiedad')) {
            return 'precio_propiedad';
        }

        if (Schema::hasColumn('tbl_propiedad', 'precio_mensual_propiedad')) {
            return 'precio_mensual_propiedad';
        }

        return 'precio_propiedad';
    }

    private function obtenerColumnasMensaje(): array
    {
        $columnaRemitente = Schema::hasColumn('tbl_mensaje', 'id_remitente_fk')
            ? 'id_remitente_fk'
            : 'id_usuario_fk';

        $columnaCuerpo = Schema::hasColumn('tbl_mensaje', 'cuerpo_mensaje')
            ? 'cuerpo_mensaje'
            : 'contenido_mensaje';

        return [$columnaRemitente, $columnaCuerpo];
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
