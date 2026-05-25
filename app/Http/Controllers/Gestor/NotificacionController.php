<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    public function marcarLeida(Request $request, int $id)
    {
        $gestorId = (int) (Auth::user()?->id_usuario ?? 0);

        $updated = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)
            ->update(['leida_notificacion' => true, 'leida_en_notificacion' => now()]);

        return response()->json(['ok' => (bool) $updated]);
    }

    public function eliminar(Request $request, int $id)
    {
        $gestorId = (int) (Auth::user()?->id_usuario ?? 0);

        $deleted = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)
            ->delete();

        return response()->json(['ok' => (bool) $deleted]);
    }
}
