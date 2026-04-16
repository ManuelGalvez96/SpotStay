<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = DB::table('tbl_usuario')
            ->leftJoin('tbl_rol_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_rol_usuario.id_usuario_fk')
            ->leftJoin('tbl_rol',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk')
            ->leftJoin(DB::raw('(SELECT id_arrendador_fk,
              COUNT(*) as total FROM tbl_propiedad
              GROUP BY id_arrendador_fk) as props'),
              'props.id_arrendador_fk', '=', 'tbl_usuario.id_usuario')
            ->select(
              'tbl_usuario.*',
              'tbl_rol.nombre_rol',
              'tbl_rol.slug_rol',
              'props.total as total_propiedades'
            )
            ->paginate(10);

        $totalUsuarios = DB::table('tbl_usuario')->count();
        $activos = DB::table('tbl_usuario')
            ->where('activo_usuario', true)->count();
        $inactivos = DB::table('tbl_usuario')
            ->where('activo_usuario', false)->count();
        $esteMes = DB::table('tbl_usuario')
            ->whereMonth('creado_usuario', Carbon::now()->month)
            ->whereYear('creado_usuario', Carbon::now()->year)
            ->count();

        return view('admin.usuarios', compact(
            'usuarios', 'totalUsuarios', 'activos', 'inactivos', 'esteMes'));
    }

    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_usuario')
            ->leftJoin('tbl_rol_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_rol_usuario.id_usuario_fk')
            ->leftJoin('tbl_rol',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk');

        if ($request->input('rol')) {
            $query->where('tbl_rol.slug_rol', $request->input('rol'));
        }

        if ($request->input('estado')) {
            $activo = $request->input('estado') === 'activo' ? true : false;
            $query->where('tbl_usuario.activo_usuario', $activo);
        }

        if ($request->input('q')) {
            $terminoBusqueda = '%' . $request->input('q') . '%';
            $query->where(function ($subQuery) use ($terminoBusqueda) {
                $subQuery->where('tbl_usuario.nombre_usuario', 'like', $terminoBusqueda)
                    ->orWhere('tbl_usuario.email_usuario', 'like', $terminoBusqueda);
            });
        }

        $usuarios = $query->select('tbl_usuario.*', 'tbl_rol.nombre_rol', 'tbl_rol.slug_rol')
            ->get();
        $total = $usuarios->count();

        return response()->json([
            'usuarios' => $usuarios,
            'total' => $total
        ]);
    }

    public function show($id)
    {
        $usuario = DB::table('tbl_usuario')
            ->leftJoin('tbl_rol_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_rol_usuario.id_usuario_fk')
            ->leftJoin('tbl_rol',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk')
            ->select('tbl_usuario.*', 'tbl_rol.nombre_rol')
            ->where('tbl_usuario.id_usuario', $id)
            ->first();

        return response()->json($usuario);
    }

    public function toggleEstado($id)
    {
        $usuarioActual = DB::table('tbl_usuario')
            ->where('id_usuario', $id)
            ->value('activo_usuario');

        $nuevoEstado = !$usuarioActual;

        DB::table('tbl_usuario')
            ->where('id_usuario', $id)
            ->update(['activo_usuario' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'activo' => $nuevoEstado
        ]);
    }

    public function exportar()
    {
        $usuarios = DB::table('tbl_usuario')
            ->leftJoin('tbl_rol_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_rol_usuario.id_usuario_fk')
            ->leftJoin('tbl_rol',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk')
            ->select('tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario',
                     'tbl_usuario.telefono_usuario', 'tbl_rol.nombre_rol',
                     'tbl_usuario.activo_usuario', 'tbl_usuario.creado_usuario')
            ->get();

        return response()->json($usuarios);
    }
}
