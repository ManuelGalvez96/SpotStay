<?php

namespace App\Http\Controllers\inquilino;

use App\Http\Controllers\Controller;
use App\Services\InquilinoFinanceService;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquilinoGastosController extends Controller
{
    protected $financeService;

    public function __construct(InquilinoFinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Muestra la vista principal de Gastos y Pagos.
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $propiedadId = $request->query('propiedad_id');

        $tipoGasto = $request->query('tipo_gasto');
        $nombreGasto = $request->query('nombre_gasto');

        // Obtener lista de propiedades para el filtro
        $propiedades = \App\Models\Alquiler::where('id_inquilino_fk', $usuario->id_usuario)
            ->where('estado_alquiler', 'activo')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->select('tbl_propiedad.id_propiedad', 'tbl_propiedad.titulo_propiedad')
            ->get();

        // Obtenemos los datos unificados del servicio (con filtro opcional)
        $resumen = $this->financeService->obtenerResumenCompletoGastos($usuario->id_usuario, $propiedadId, $tipoGasto, $nombreGasto);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'total_pendiente' => $resumen['total_pendiente'],
                'total_atrasado' => $resumen['total_atrasado'],
                'pendientes' => $resumen['items'],
                'propiedad_seleccionada' => $propiedadId,
            ]);
        }

        return view('inquilino.gastos', [
            'total_pendiente' => $resumen['total_pendiente'],
            'total_atrasado' => $resumen['total_atrasado'],
            'pendientes' => $resumen['items'],
            'historial' => [],
            'propiedades' => $propiedades,
            'propiedad_seleccionada' => $propiedadId
        ]);
    }
}
