<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PropiedadController extends Controller
{
    public function inicio(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $propiedadIdEditar = (int) $request->query('editar', 0);

        $arrendador = $this->obtenerArrendadorBase($arrendadorId);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        $propiedades = $this->consultarPropiedades($arrendadorId, $columnaPrecio)->paginate(10);
        $propiedadEditando = $propiedadIdEditar > 0
            ? $this->consultarPropiedadEditable($arrendadorId, $propiedadIdEditar, $columnaPrecio)
            : null;

        $totales = $this->obtenerTotales($arrendadorId);

        return view('arrendador.propiedades', [
            'arrendador' => $arrendador,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'propiedades' => $propiedades,
            'propiedadEditando' => $propiedadEditando,
            'totales' => $totales,
            'arrendadorId' => $arrendadorId,
        ]);
    }

    public function guardar(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $propiedadId = (int) $request->input('id_propiedad', 0);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        $datos = $request->validate([
            'titulo_propiedad' => ['required', 'string', 'max:150'],
            'direccion_propiedad' => ['required', 'string', 'max:255'],
            'ciudad_propiedad' => ['required', 'string', 'max:100'],
            'codigo_postal_propiedad' => ['required', 'string', 'max:10'],
            'latitud_propiedad' => ['nullable', 'numeric'],
            'longitud_propiedad' => ['nullable', 'numeric'],
            'descripcion_propiedad' => ['nullable', 'string'],
            'precio_propiedad' => ['required', 'numeric', 'min:0'],
            'gastos_propiedad' => ['nullable', 'string'],
            'estado_propiedad' => ['required', 'in:borrador,publicada,inactiva,alquilada'],
        ]);

        $gastos = $this->normalizarGastos($datos['gastos_propiedad'] ?? null);

        $datosPropiedad = [
            'id_arrendador_fk' => $arrendadorId,
            'id_gestor_fk' => $arrendadorId,
            'titulo_propiedad' => $datos['titulo_propiedad'],
            'direccion_propiedad' => $datos['direccion_propiedad'],
            'ciudad_propiedad' => $datos['ciudad_propiedad'],
            'codigo_postal_propiedad' => $datos['codigo_postal_propiedad'],
            'latitud_propiedad' => $datos['latitud_propiedad'] ?? null,
            'longitud_propiedad' => $datos['longitud_propiedad'] ?? null,
            'descripcion_propiedad' => $datos['descripcion_propiedad'] ?? null,
            $columnaPrecio => $datos['precio_propiedad'],
            'gastos_propiedad' => $gastos,
            'estado_propiedad' => $datos['estado_propiedad'],
            'actualizado_propiedad' => Carbon::now(),
        ];

        if ($propiedadId > 0) {
            $existe = DB::table('tbl_propiedad')
                ->where('id_propiedad', $propiedadId)
                ->where('id_arrendador_fk', $arrendadorId)
                ->exists();

            if (!$existe) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontró la propiedad para editar.',
                    ], 404);
                }

                return redirect()
                    ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                    ->with('error', 'No se encontró la propiedad para editar.');
            }

            DB::table('tbl_propiedad')
                ->where('id_propiedad', $propiedadId)
                ->where('id_arrendador_fk', $arrendadorId)
                ->update($datosPropiedad);
        } else {
            $datosPropiedad['creado_propiedad'] = Carbon::now();
            DB::table('tbl_propiedad')->insert($datosPropiedad);
        }

        $mensaje = $propiedadId > 0 ? 'Propiedad actualizada correctamente.' : 'Propiedad creada correctamente.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'arrendador_id' => $arrendadorId,
            ]);
        }

        return redirect()
            ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
            ->with('success', $mensaje);
    }

    public function alternarEstado(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_arrendador_fk', $arrendadorId)
            ->first();

        if (!$propiedad) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la propiedad.',
                ], 404);
            }

            return redirect()
                ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                ->with('error', 'No se encontró la propiedad.');
        }

        $nuevoEstado = $propiedad->estado_propiedad === 'publicada' ? 'inactiva' : 'publicada';

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update([
                'estado_propiedad' => $nuevoEstado,
                'actualizado_propiedad' => Carbon::now(),
            ]);

        $mensaje = $nuevoEstado === 'publicada' ? 'Propiedad publicada.' : 'Propiedad inactivada.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'estado' => $nuevoEstado,
            ]);
        }

        return redirect()
            ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
            ->with('success', $mensaje);
    }

    public function mostrar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $propiedad = DB::table('tbl_propiedad as p')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'p.id_arrendador_fk')
            ->where('p.id_propiedad', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('p.*', 'arrendador.nombre_usuario as nombre_arrendador', 'arrendador.email_usuario as email_arrendador')
            ->first();

        if (!$propiedad) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        $alquilerActivo = DB::table('tbl_alquiler as a')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('a.id_propiedad_fk', $id)
            ->where('a.estado_alquiler', 'activo')
            ->select('a.*', 'inquilino.nombre_usuario as nombre_inquilino', 'inquilino.email_usuario as email_inquilino')
            ->first();

        return response()->json([
            'propiedad' => $propiedad,
            'alquiler_activo' => $alquilerActivo,
        ]);
    }

    private function consultarPropiedades(int $arrendadorId, string $columnaPrecio)
    {
        return DB::table('tbl_propiedad as p')
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk, COUNT(*) as total_inquilinos FROM tbl_alquiler WHERE estado_alquiler = "activo" GROUP BY id_propiedad_fk) as alq'), 'alq.id_propiedad_fk', '=', 'p.id_propiedad')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.id_propiedad',
                'p.titulo_propiedad',
                'p.direccion_propiedad',
                'p.ciudad_propiedad',
                'p.codigo_postal_propiedad',
                'p.estado_propiedad',
                DB::raw("p.{$columnaPrecio} as precio_propiedad"),
                'alq.total_inquilinos',
                'p.creado_propiedad'
            )
            ->orderByDesc('p.creado_propiedad');
    }

    private function consultarPropiedadEditable(int $arrendadorId, int $propiedadId, string $columnaPrecio)
    {
        return DB::table('tbl_propiedad')
            ->where('id_propiedad', $propiedadId)
            ->where('id_arrendador_fk', $arrendadorId)
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'direccion_propiedad',
                'ciudad_propiedad',
                'codigo_postal_propiedad',
                'latitud_propiedad',
                'longitud_propiedad',
                'descripcion_propiedad',
                DB::raw("{$columnaPrecio} as precio_propiedad"),
                'gastos_propiedad',
                'estado_propiedad'
            )
            ->first();
    }

    private function obtenerTotales(int $arrendadorId): array
    {
        $totalPropiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->count();

        $publicadas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'publicada')
            ->count();

        $alquiladas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'alquilada')
            ->count();

        $inactivas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'inactiva')
            ->count();

        return compact('totalPropiedades', 'publicadas', 'alquiladas', 'inactivas');
    }

    private function obtenerIdArrendador(Request $request): int
    {
        $arrendadorId = (int) $request->query('arrendador_id', 0);

        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        return (int) DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select('u.id_usuario', DB::raw('COUNT(*) as total_propiedades'))
            ->orderByDesc('total_propiedades')
            ->orderBy('u.id_usuario')
            ->value('u.id_usuario');
    }

    private function obtenerArrendadorBase(int $arrendadorId)
    {
        return DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();
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

    private function normalizarGastos(?string $gastos): ?string
    {
        if ($gastos === null || trim($gastos) === '') {
            return null;
        }

        $decodificado = json_decode($gastos, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decodificado);
        }

        return $gastos;
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
