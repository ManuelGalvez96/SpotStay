<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PrecioGastoController extends Controller
{
    public function index(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        $arrendador = DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();

        $propiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'direccion_propiedad',
                'ciudad_propiedad',
                'estado_propiedad',
                'gastos_propiedad',
                DB::raw("{$columnaPrecio} as precio_propiedad")
            )
            ->orderByDesc('creado_propiedad')
            ->paginate(10);

        $totalPropiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->count();

        $precioMedio = (float) DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->avg($columnaPrecio);

        return view('arrendador.precios-gastos', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'propiedades' => $propiedades,
            'totalPropiedades' => $totalPropiedades,
            'precioMedio' => $precioMedio,
        ]);
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $datos = $request->validate([
            'precio_propiedad' => ['required', 'numeric', 'min:0'],
            'gastos_propiedad' => ['nullable', 'string', 'max:4000'],
        ]);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_arrendador_fk', $arrendadorId)
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la propiedad.',
            ], 404);
        }

        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update([
                $columnaPrecio => (float) $datos['precio_propiedad'],
                'gastos_propiedad' => $this->normalizarGastos($datos['gastos_propiedad'] ?? null),
                'actualizado_propiedad' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Precio y gastos actualizados.',
        ]);
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

        $texto = trim($gastos);
        $decodificado = json_decode($texto, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decodificado);
        }

        return $texto;
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
