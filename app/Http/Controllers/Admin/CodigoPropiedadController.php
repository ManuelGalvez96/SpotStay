<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodigoPropiedad;
use App\Models\Propiedad;
use App\Services\CodigoPropiedadService;
use Illuminate\Http\Request;

class CodigoPropiedadController extends Controller
{
    /**
     * Mostrar la lista de códigos de propiedades
     */
    public function index()
    {
        $codigos = CodigoPropiedad::with(['propiedad', 'arrendador.usuario', 'inquilino.usuario'])
            ->orderBy('creado_codigo_propiedad', 'desc')
            ->paginate(20);
        
        return view('admin.codigos-propiedades', compact('codigos'));
    }
    
    /**
     * Generar un nuevo código para una propiedad
     */
    public function generar(Request $request)
    {
        $request->validate([
            'id_propiedad' => 'required|exists:tbl_propiedad,id_propiedad',
            'id_arrendador' => 'required|exists:tbl_usuario_arrendador,id_usuario_arrendador',
            'dias_validez' => 'nullable|integer|min:1|max:365',
        ]);
        
        try {
            $diasValidez = $request->dias_validez ?? 30;
            $codigoPropiedad = CodigoPropiedadService::crearCodigoParaPropiedad(
                $request->id_propiedad,
                $request->id_arrendador,
                $diasValidez
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Código de propiedad generado correctamente.',
                'data' => [
                    'codigo' => $codigoPropiedad->codigo_propiedad,
                    'estado' => $codigoPropiedad->estado_codigo_propiedad,
                    'expira' => $codigoPropiedad->expira_codigo_propiedad->format('d/m/Y H:i'),
                    'creado' => $codigoPropiedad->creado_codigo_propiedad->format('d/m/Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Cancelar un código de propiedad
     */
    public function cancelar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|exists:tbl_codigo_propiedad,codigo_propiedad',
        ]);
        
        try {
            $resultado = CodigoPropiedadService::cancelarCodigo($request->codigo);
            
            if ($resultado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Código cancelado correctamente.',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo cancelar el código.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Validar si un código es válido
     */
    public function validar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
        ]);
        
        $esValido = CodigoPropiedadService::validarCodigo($request->codigo);
        
        if ($esValido) {
            // Incrementar intentos
            CodigoPropiedadService::incrementarIntentos($request->codigo);
            
            $propiedad = CodigoPropiedadService::obtenerPropiedadPorCodigo($request->codigo);
            
            return response()->json([
                'valido' => true,
                'propiedad' => $propiedad,
            ]);
        }
        
        return response()->json([
            'valido' => false,
            'mensaje' => 'Código no válido, expirado o ya utilizado.',
        ]);
    }
    
    /**
     * Registrar el uso de un código (inquilino lo utiliza)
     */
    public function registrarUso(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'id_inquilino' => 'required|integer',
        ]);
        
        try {
            $resultado = CodigoPropiedadService::registrarUso(
                $request->codigo,
                $request->id_inquilino
            );
            
            if ($resultado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Código utilizado correctamente. Acceso a propiedad concedido.',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar el uso del código.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Obtener información de un código
     */
    public function show(int $id)
    {
        $codigoPropiedad = CodigoPropiedad::with(['propiedad', 'arrendador.usuario', 'inquilino.usuario'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $codigoPropiedad,
        ]);
    }
    
    /**
     * Obtener códigos activos de una propiedad
     */
    public function obtenerCodigosDePropiedad(int $idPropiedad)
    {
        $codigos = CodigoPropiedadService::obtenerCodigosActivosDePropiedad($idPropiedad);
        
        return response()->json([
            'success' => true,
            'total' => $codigos->count(),
            'codigos' => $codigos,
        ]);
    }
}
