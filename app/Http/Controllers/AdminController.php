<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Mostrar el dashboard de administración
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Aprobar un alquiler
     */
    public function aprobar($id)
    {
        try {
            // Aquí iría tu lógica para aprobar el alquiler
            // $alquiler = Alquiler::findOrFail($id);
            // $alquiler->update(['estado' => 'aprobado']);

            return response()->json([
                'success' => true,
                'message' => 'Alquiler aprobado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un alquiler
     */
    public function rechazar($id)
    {
        try {
            // Aquí iría tu lógica para rechazar el alquiler
            // $alquiler = Alquiler::findOrFail($id);
            // $alquiler->update(['estado' => 'rechazado']);

            return response()->json([
                'success' => true,
                'message' => 'Alquiler rechazado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
