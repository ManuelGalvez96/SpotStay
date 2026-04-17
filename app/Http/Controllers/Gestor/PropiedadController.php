<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->whereIn('prioridad_incidencia', ['alta', 'urgente'])
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
            ->where('tbl_propiedad.id_gestor_fk', $gestorId);

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
        $totalBorrador = (clone $baseQuery)->where('tbl_propiedad.estado_propiedad', 'borrador')->count();
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
            'totalBorrador',
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
            'totalesIncidencia'
        ));
    }
}
