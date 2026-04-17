<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContratoController extends Controller
{
    public function inicio(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnas = $this->obtenerColumnasContrato();

        $arrendador = DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();

        $contratos = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'c.id_contrato',
                'a.id_alquiler',
                'p.titulo_propiedad',
                'p.direccion_propiedad',
                'inquilino.nombre_usuario as nombre_inquilino',
                DB::raw($this->seleccionarColumnaContrato($columnas['url_pdf'], 'url_pdf_contrato', "''")),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_arrendador'], 'firmado_arrendador', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['fecha_firma_arrendador'], 'fecha_firma_arrendador', 'NULL')),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_inquilino'], 'firmado_inquilino', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['fecha_firma_inquilino'], 'fecha_firma_inquilino', 'NULL')),
                DB::raw($this->seleccionarColumnaContrato($columnas['estado'], 'estado_contrato', "'pendiente'"))
            )
            ->orderByDesc('c.id_contrato')
            ->paginate(10);

        $total = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->count('c.id_contrato');

        $firmados = $this->contarFirmados($arrendadorId, $columnas);
        $pendientes = max(0, $total - $firmados);

        return view('arrendador.contratos', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'contratos' => $contratos,
            'totales' => [
                'total' => $total,
                'firmados' => $firmados,
                'pendientes' => $pendientes,
            ],
        ]);
    }

    public function firmarArrendador(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnas = $this->obtenerColumnasContrato();

        $contrato = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('c.id_contrato', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'c.id_contrato',
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_arrendador'], 'firmado_arrendador', '0')),
                DB::raw($this->seleccionarColumnaContrato($columnas['firmado_inquilino'], 'firmado_inquilino', '0'))
            )
            ->first();

        if (!$contrato) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro el contrato.',
            ], 404);
        }

        if ((bool) $contrato->firmado_arrendador) {
            return response()->json([
                'success' => true,
                'message' => 'El contrato ya estaba firmado por el arrendador.',
                'estado' => (bool) $contrato->firmado_inquilino ? 'firmado' : 'pendiente',
            ]);
        }

        $datosActualizar = [];

        if ($columnas['firmado_arrendador']) {
            $datosActualizar[$columnas['firmado_arrendador']] = true;
        }

        if ($columnas['fecha_firma_arrendador']) {
            $datosActualizar[$columnas['fecha_firma_arrendador']] = Carbon::now();
        }

        if ($columnas['ip_firma_arrendador']) {
            $datosActualizar[$columnas['ip_firma_arrendador']] = $request->ip();
        }

        $estadoNuevo = (bool) $contrato->firmado_inquilino ? 'firmado' : 'pendiente';

        if ($columnas['estado']) {
            $datosActualizar[$columnas['estado']] = $estadoNuevo;
        }

        if ($columnas['actualizado']) {
            $datosActualizar[$columnas['actualizado']] = Carbon::now();
        }

        DB::table('tbl_contrato')
            ->where('id_contrato', $id)
            ->update($datosActualizar);

        return response()->json([
            'success' => true,
            'message' => 'Contrato firmado por el arrendador.',
            'estado' => $estadoNuevo,
            'firmado_arrendador' => true,
        ]);
    }

    private function contarFirmados(int $arrendadorId, array $columnas): int
    {
        $consulta = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId);

        if ($columnas['estado']) {
            return (clone $consulta)
                ->where("c.{$columnas['estado']}", 'firmado')
                ->count('c.id_contrato');
        }

        if ($columnas['firmado_arrendador'] && $columnas['firmado_inquilino']) {
            return (clone $consulta)
                ->where("c.{$columnas['firmado_arrendador']}", true)
                ->where("c.{$columnas['firmado_inquilino']}", true)
                ->count('c.id_contrato');
        }

        return 0;
    }

    private function seleccionarColumnaContrato(?string $columna, string $alias, string $valorDefecto): string
    {
        if ($columna) {
            return "c.{$columna} as {$alias}";
        }

        return "{$valorDefecto} as {$alias}";
    }

    private function obtenerColumnasContrato(): array
    {
        return [
            'url_pdf' => $this->resolverColumnaContrato('url_pdf_contrato', 'url_contrato'),
            'firmado_arrendador' => $this->resolverColumnaContrato('firmado_arrendador', 'firmado_arrendador_contrato'),
            'fecha_firma_arrendador' => $this->resolverColumnaContrato('fecha_firma_arrendador', 'fecha_firma_arrendador_contrato'),
            'ip_firma_arrendador' => $this->resolverColumnaContrato('ip_firma_arrendador', 'ip_firma_arrendador_contrato'),
            'firmado_inquilino' => $this->resolverColumnaContrato('firmado_inquilino', 'firmado_inquilino_contrato'),
            'fecha_firma_inquilino' => $this->resolverColumnaContrato('fecha_firma_inquilino', 'fecha_firma_inquilino_contrato'),
            'estado' => $this->resolverColumnaContrato('estado_contrato', 'estado_contrato'),
            'actualizado' => $this->resolverColumnaContrato('actualizado_contrato', 'actualizado_contrato'),
        ];
    }

    private function resolverColumnaContrato(string $primaria, string $alterna): ?string
    {
        if (Schema::hasColumn('tbl_contrato', $primaria)) {
            return $primaria;
        }

        if (Schema::hasColumn('tbl_contrato', $alterna)) {
            return $alterna;
        }

        return null;
    }

    private function obtenerIdArrendador(Request $request): int
    {
        $arrendadorId = (int) $request->query('arrendador_id', $request->input('arrendador_id', 0));

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

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
