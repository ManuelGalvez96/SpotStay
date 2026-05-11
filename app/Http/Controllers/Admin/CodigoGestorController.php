<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioGestor;
use App\Models\CodigoGestor;
use App\Services\CodigoGestorService;
use Illuminate\Http\Request;

class CodigoGestorController extends Controller
{
    /**
     * Mostrar la lista de códigos de gestores
     */
    public function index()
    {
        $codigos = CodigoGestor::with('gestor.usuario')
            ->orderBy('creado_codigo_gestor', 'desc')
            ->paginate(20);
        
        return view('admin.codigos-gestores', compact('codigos'));
    }
    
    /**
     * Generar un nuevo código para un gestor específico
     */
    public function generar(Request $request)
    {
        $request->validate([
            'id_gestor' => 'required|exists:tbl_usuario_gestor,id_usuario_gestor',
        ]);
        
        try {
            $codigoGestor = CodigoGestorService::crearCodigoParaGestor($request->id_gestor);
            
            return response()->json([
                'success' => true,
                'message' => 'Código de gestor generado correctamente.',
                'data' => [
                    'codigo' => $codigoGestor->codigo_gestor,
                    'estado' => $codigoGestor->estado_codigo_gestor,
                    'creado' => $codigoGestor->creado_codigo_gestor->format('d/m/Y H:i'),
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
     * Cancelar un código de gestor
     */
    public function cancelar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|exists:tbl_codigo_gestor,codigo_gestor',
        ]);
        
        try {
            $resultado = CodigoGestorService::cancelarCodigo($request->codigo);
            
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
        
        $esValido = CodigoGestorService::validarCodigo($request->codigo);
        
        if ($esValido) {
            $gestor = CodigoGestorService::obtenerGestorPorCodigo($request->codigo);
            return response()->json([
                'valido' => true,
                'gestor' => $gestor,
            ]);
        }
        
        return response()->json([
            'valido' => false,
            'mensaje' => 'Código no válido o expirado.',
        ]);
    }
    
    /**
     * Obtener información de un código
     */
    public function show(int $id)
    {
        $codigoGestor = CodigoGestor::with('gestor.usuario')
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $codigoGestor,
        ]);
    }
}
