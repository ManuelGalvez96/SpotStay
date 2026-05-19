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

        $query = DB::table('tbl_notificacion')
            ->where('id_usuario_fk', $gestorId)
            ->whereIn('tipo_notificacion', $tipos);

        $tipo = $request->query('tipo');
        if ($tipo && in_array($tipo, $tipos, true)) {
            $query->where('tipo_notificacion', $tipo);
        }

        $actividades = $query
            ->select(
                'id_notificacion',
                'tipo_notificacion',
                'titulo_notificacion',
                'mensaje_notificacion',
                'url_notificacion',
                'icono_notificacion',
                'color_notificacion',
                'tipo_entidad_notificacion',
                'id_entidad_notificacion',
                'creado_notificacion'
            )
            ->orderBy('creado_notificacion', 'desc')
            ->paginate(20)
            ->withQueryString();

        $conteos = DB::table('tbl_notificacion')
            ->where('id_usuario_fk', $gestorId)
            ->whereIn('tipo_notificacion', $tipos)
            ->selectRaw('tipo_notificacion, COUNT(*) as total')
            ->groupBy('tipo_notificacion')
            ->pluck('total', 'tipo_notificacion');

        $tiposInfo = ActividadService::tiposActividad();

        return view('gestor.actividad', compact('actividades', 'conteos', 'tiposInfo', 'tipo'));
    }
}
