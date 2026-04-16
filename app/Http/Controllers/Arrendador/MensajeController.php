<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MensajeController extends Controller
{
    public function index(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = DB::table('tbl_usuario')
            ->select('id_usuario', 'nombre_usuario')
            ->where('id_usuario', $arrendadorId)
            ->first();

        [$columnaRemitente, $columnaCuerpo] = $this->obtenerColumnasMensaje();

        $conversaciones = DB::table('tbl_conversacion_usuario as cu_arr')
            ->join('tbl_conversacion_usuario as cu_otro', function ($join) use ($arrendadorId) {
                $join->on('cu_otro.id_conversacion_fk', '=', 'cu_arr.id_conversacion_fk')
                    ->where('cu_otro.id_usuario_fk', '!=', $arrendadorId);
            })
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 'cu_otro.id_usuario_fk')
            ->leftJoin('tbl_mensaje as m', 'm.id_conversacion_fk', '=', 'cu_arr.id_conversacion_fk')
            ->where('cu_arr.id_usuario_fk', $arrendadorId)
            ->groupBy(
                'cu_arr.id_conversacion_fk',
                'u.id_usuario',
                'u.nombre_usuario',
                'u.email_usuario',
                'u.telefono_usuario'
            )
            ->select(
                'cu_arr.id_conversacion_fk as id_conversacion',
                'u.id_usuario as id_inquilino',
                'u.nombre_usuario as nombre_inquilino',
                'u.email_usuario as email_inquilino',
                'u.telefono_usuario as telefono_inquilino',
                DB::raw('MAX(m.creado_mensaje) as fecha_ultimo_mensaje'),
                DB::raw("MAX(m.{$columnaCuerpo}) as resumen_ultimo_mensaje")
            )
            ->orderByDesc(DB::raw('MAX(m.creado_mensaje)'))
            ->orderBy('u.nombre_usuario')
            ->get();

        return view('arrendador.mensajes', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'conversaciones' => $conversaciones,
        ]);
    }

    public function mostrar(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        if (!$this->arrendadorParticipaEnConversacion($arrendadorId, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta conversacion.',
            ], 403);
        }

        [$columnaRemitente, $columnaCuerpo] = $this->obtenerColumnasMensaje();

        $inquilino = DB::table('tbl_conversacion_usuario as cu')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 'cu.id_usuario_fk')
            ->where('cu.id_conversacion_fk', $id)
            ->where('cu.id_usuario_fk', '!=', $arrendadorId)
            ->select('u.id_usuario', 'u.nombre_usuario', 'u.email_usuario', 'u.telefono_usuario')
            ->first();

        $mensajes = DB::table('tbl_mensaje as m')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', "m.{$columnaRemitente}")
            ->where('m.id_conversacion_fk', $id)
            ->select(
                'm.id_mensaje',
                DB::raw("m.{$columnaRemitente} as id_remitente"),
                'u.nombre_usuario as nombre_remitente',
                DB::raw("m.{$columnaCuerpo} as cuerpo_mensaje"),
                'm.creado_mensaje'
            )
            ->orderBy('m.creado_mensaje', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'conversacion' => [
                'id_conversacion' => $id,
                'inquilino' => $inquilino,
                'mensajes' => $mensajes,
            ],
        ]);
    }

    public function enviar(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        if (!$this->arrendadorParticipaEnConversacion($arrendadorId, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta conversacion.',
            ], 403);
        }

        $datos = $request->validate([
            'texto' => ['required', 'string', 'max:2000'],
        ]);

        [$columnaRemitente, $columnaCuerpo] = $this->obtenerColumnasMensaje();

        $ahora = Carbon::now();

        $insertar = [
            'id_conversacion_fk' => $id,
            $columnaRemitente => $arrendadorId,
            $columnaCuerpo => trim($datos['texto']),
            'creado_mensaje' => $ahora,
            'actualizado_mensaje' => $ahora,
        ];

        if (Schema::hasColumn('tbl_mensaje', 'leido_mensaje')) {
            $insertar['leido_mensaje'] = false;
        }

        DB::table('tbl_mensaje')->insert($insertar);

        $arrendador = DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->value('nombre_usuario');

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado.',
            'mensaje' => [
                'id_remitente' => $arrendadorId,
                'nombre_remitente' => $arrendador,
                'cuerpo_mensaje' => trim($datos['texto']),
                'creado_mensaje' => $ahora->toDateTimeString(),
            ],
        ]);
    }

    private function arrendadorParticipaEnConversacion(int $arrendadorId, int $conversacionId): bool
    {
        return DB::table('tbl_conversacion_usuario')
            ->where('id_conversacion_fk', $conversacionId)
            ->where('id_usuario_fk', $arrendadorId)
            ->exists();
    }

    private function obtenerIdArrendador(Request $request): int
    {
        $arrendadorId = (int) $request->query('arrendador_id', 0);

        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        $arrendadorConActividad = DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->leftJoin('tbl_alquiler as a', function ($join) {
                $join->on('a.id_propiedad_fk', '=', 'p.id_propiedad')
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

        return $arrendadorConActividad ? (int) $arrendadorConActividad : 0;
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
