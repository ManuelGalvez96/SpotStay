<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Services\ActividadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $gestorId = (int) (Auth::user()?->id_usuario ?? 0);

        $tipos = array_keys(ActividadService::tiposActividad());

        $baseQuery = DB::table('tbl_notificacion as n')
            ->where('n.id_usuario_fk', $gestorId)
            ->whereIn('n.tipo_notificacion', $tipos);

        $propiedadId = $request->integer('propiedad');
        if ($propiedadId) {
            $baseQuery
                ->leftJoin('tbl_incidencia as i', function ($join) {
                    $join->on('n.tipo_entidad_notificacion', '=', DB::raw("'incidencia'"))
                         ->on('n.id_entidad_notificacion', '=', 'i.id_incidencia');
                })
                ->leftJoin('tbl_alquiler as a', function ($join) {
                    $join->on('n.tipo_entidad_notificacion', '=', DB::raw("'alquiler'"))
                         ->on('n.id_entidad_notificacion', '=', 'a.id_alquiler');
                })
                ->where(function ($q) use ($propiedadId) {
                    $q->where(function ($sq) use ($propiedadId) {
                        $sq->where('n.tipo_entidad_notificacion', 'propiedad')
                           ->where('n.id_entidad_notificacion', $propiedadId);
                    })->orWhere(function ($sq) use ($propiedadId) {
                        $sq->where('n.tipo_entidad_notificacion', 'incidencia')
                           ->where('i.id_propiedad_fk', $propiedadId);
                    })->orWhere(function ($sq) use ($propiedadId) {
                        $sq->where('n.tipo_entidad_notificacion', 'alquiler')
                           ->where('a.id_propiedad_fk', $propiedadId);
                    });
                });
        }

        if ($buscar = $request->query('buscar')) {
            $baseQuery->where(function ($q) use ($buscar) {
                $q->where('n.titulo_notificacion', 'like', "%{$buscar}%")
                  ->orWhere('n.mensaje_notificacion', 'like', "%{$buscar}%");
            });
        }

        if ($desde = $request->query('desde')) {
            $baseQuery->whereDate('n.creado_notificacion', '>=', $desde);
        }

        if ($hasta = $request->query('hasta')) {
            $baseQuery->whereDate('n.creado_notificacion', '<=', $hasta);
        }

        $mainQuery = clone $baseQuery;

        $tipo = $request->query('tipo');
        if ($tipo && in_array($tipo, $tipos, true)) {
            $mainQuery->where('n.tipo_notificacion', $tipo);
        }

        $orden = $request->query('orden', 'mas_nuevos');
        $orderDir = $orden === 'mas_antiguos' ? 'asc' : 'desc';

        $actividades = $mainQuery
            ->select(
                'n.id_notificacion',
                'n.tipo_notificacion',
                'n.titulo_notificacion',
                'n.mensaje_notificacion',
                'n.url_notificacion',
                'n.icono_notificacion',
                'n.color_notificacion',
                'n.tipo_entidad_notificacion',
                'n.id_entidad_notificacion',
                'n.creado_notificacion'
            )
            ->orderBy('n.creado_notificacion', $orderDir)
            ->paginate(20)
            ->withQueryString();

        $conteos = $baseQuery
            ->select('n.tipo_notificacion', DB::raw('COUNT(*) as total'))
            ->groupBy('n.tipo_notificacion')
            ->pluck('total', 'n.tipo_notificacion');

        $propiedades = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->select('id_propiedad', 'titulo_propiedad')
            ->orderBy('titulo_propiedad')
            ->get();

        $tiposInfo = ActividadService::tiposActividad();

        $grupos = [
            'Incidencias' => ['nueva_incidencia', 'incidencia_actualizada', 'presupuesto_creado'],
            'Pagos y recibos' => ['pago_realizado', 'pago_atrasado', 'gasto_creado', 'gasto_atrasado'],
            'Propiedades' => ['propiedad_estado', 'alquiler_creado', 'alquiler_aprobado', 'alquiler_pendiente'],
            'Otros' => ['contrato_firmado', 'mensaje_nuevo'],
        ];

        $filtrosActivos = collect(array_filter([
            'tipo' => $tipo,
            'propiedad' => $propiedadId,
            'buscar' => $request->query('buscar'),
            'desde' => $request->query('desde'),
            'hasta' => $request->query('hasta'),
            'orden' => $orden !== 'mas_nuevos' ? $orden : null,
        ]));

        return view('gestor.actividad', compact(
            'actividades',
            'conteos',
            'tiposInfo',
            'tipo',
            'propiedades',
            'propiedadId',
            'grupos',
            'filtrosActivos',
            'orden'
        ));
    }

    public function marcarLeida(Request $request, int $id)
    {
        $gestorId = (int) (Auth::user()?->id_usuario ?? 0);

        $updated = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)
            ->update(['leida_notificacion' => true, 'leida_en_notificacion' => now()]);

        return response()->json(['ok' => (bool) $updated]);
    }

    public function eliminar(Request $request, int $id)
    {
        $gestorId = (int) (Auth::user()?->id_usuario ?? 0);

        $deleted = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)
            ->delete();

        return response()->json(['ok' => (bool) $deleted]);
    }
}
