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

        // Obtener lista de propiedades para el filtro
        $propiedades = \App\Models\Alquiler::where('id_inquilino_fk', $usuario->id_usuario)
            ->where('estado_alquiler', 'activo')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->select('tbl_propiedad.id_propiedad', 'tbl_propiedad.titulo_propiedad')
            ->get();

        // Obtenemos los datos unificados del servicio (con filtro opcional)
        $resumen = $this->financeService->obtenerResumenCompletoGastos($usuario->id_usuario, $propiedadId);
        
        // Historial de pagos mejorado con URL de factura
        $queryHistorial = Pago::where('id_pagador_fk', $usuario->id_usuario)
            ->leftJoin('tbl_documento', function($join) {
                $join->on('tbl_documento.id_entidad_documento', '=', 'tbl_pago.id_pago')
                     ->where('tbl_documento.tipo_entidad_documento', '=', 'pago')
                     ->where('tbl_documento.tipo_documento', '=', 'factura');
            })
            ->select('tbl_pago.*', 'tbl_documento.url_documento as factura_url')
            ->orderBy('tbl_pago.creado_pago', 'desc');

        if ($propiedadId) {
            $queryHistorial->where('tbl_pago.id_alquiler_fk', function($q) use ($propiedadId) {
                $q->select('id_alquiler')->from('tbl_alquiler')
                  ->where('id_propiedad_fk', $propiedadId)
                  ->limit(1);
            });
        }

        $historial = $queryHistorial->get();

        return view('inquilino.gastos', [
            'total_pendiente' => $resumen['total_pendiente'],
            'total_atrasado' => $resumen['total_atrasado'],
            'pendientes' => $resumen['items'],
            'historial' => $historial,
            'propiedades' => $propiedades,
            'propiedad_seleccionada' => $propiedadId
        ]);
    }
}
