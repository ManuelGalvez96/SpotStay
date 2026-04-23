<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
              'tbl_rol_usuario.id_rol_fk')
            ->leftJoin(DB::raw('(SELECT id_arrendador_fk, COUNT(*) as total FROM tbl_propiedad GROUP BY id_arrendador_fk) as props'),
              'props.id_arrendador_fk', '=', 'tbl_usuario.id_usuario');

        if ($request->input('rol')) {
            $query->where('tbl_rol.slug_rol', $request->input('rol'));
        }

        if ($request->input('estado')) {
            $activo = $request->input('estado') === 'activo' ? 1 : 0;
            $query->where('tbl_usuario.activo_usuario', $activo);
        }

        if ($request->input('q')) {
            $q = '%' . $request->input('q') . '%';
            $query->where(function ($builder) use ($q) {
                $builder->where('tbl_usuario.nombre_usuario', 'like', $q)
                  ->orWhere('tbl_usuario.email_usuario', 'like', $q);
            });
        }

        $usuariosPaginados = $query->select('tbl_usuario.*', 'tbl_rol.nombre_rol', 'tbl_rol.slug_rol', 'props.total as total_propiedades')
            ->paginate(10);
        
        // Procesar los datos para el frontend
        $usuarios = $usuariosPaginados->map(function($u) {
            $nombre = $u->nombre_usuario ?? 'Usuario';
            $partes = explode(' ', $nombre);
            $avatarText = strtoupper(substr($partes[0], 0, 1)) . 
                         strtoupper(substr($partes[1] ?? '', 0, 1));
            
            return [
                'id' => $u->id_usuario,
                'id_usuario' => $u->id_usuario,
                'nombre' => $nombre,
                'email' => $u->email_usuario,
                'telefono' => $u->telefono_usuario ?? '',
                'rol' => strtolower($u->slug_rol ?? 'usuario'),
                'rolLabel' => $u->nombre_rol ?? 'Sin rol',
                'estado' => $u->activo_usuario ? 'activo' : 'inactivo',
                'propiedades' => $u->total_propiedades ?? 0,
                'fechaRegistro' => $u->creado_usuario ? substr($u->creado_usuario, 0, 10) : 'N/A',
                'avatarText' => $avatarText,
                'avatarColor' => '#B8CCE4'
            ];
        });

        return response()->json([
            'usuarios' => $usuarios,
            'total' => $usuariosPaginados->total(),
            'currentPage' => $usuariosPaginados->currentPage(),
            'totalPages' => $usuariosPaginados->lastPage(),
            'perPage' => $usuariosPaginados->perPage(),
            'from' => $usuariosPaginados->firstItem(),
            'to' => $usuariosPaginados->lastItem()
        ]);
    }

    public function show($id)
    {
        try {
            $usuario = DB::table('tbl_usuario')
                ->leftJoin('tbl_rol_usuario',
                  'tbl_usuario.id_usuario', '=',
                  'tbl_rol_usuario.id_usuario_fk')
                ->leftJoin('tbl_rol',
                  'tbl_rol.id_rol', '=',
                  'tbl_rol_usuario.id_rol_fk')
                ->select('tbl_usuario.*', 'tbl_rol.nombre_rol', 'tbl_rol.slug_rol')
                ->where('tbl_usuario.id_usuario', $id)
                ->first();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Obtener propiedades del usuario (si es arrendador)
            $propiedadesFormato = [];
            try {
                $propiedades = DB::table('tbl_propiedad')
                    ->where('id_arrendador_fk', $id)
                    ->get();
                
                foreach ($propiedades as $p) {
                    $direccion = '';
                    if (isset($p->calle_propiedad)) {
                        $direccion = $p->calle_propiedad;
                        if (isset($p->numero_propiedad)) {
                            $direccion .= ', ' . $p->numero_propiedad;
                        }
                        if (isset($p->piso_propiedad) && !empty($p->piso_propiedad)) {
                            $direccion .= ', ' . $p->piso_propiedad;
                        }
                        if (isset($p->ciudad_propiedad)) {
                            $direccion .= ' - ' . $p->ciudad_propiedad;
                        }
                    }
                    
                    $propiedadesFormato[] = [
                        'direccion_propiedad' => $direccion,
                        'estado_propiedad' => $p->estado_propiedad ?? 'borrador',
                        'precio_propiedad' => (int)($p->precio_propiedad ?? 0)
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error obteniendo propiedades: ' . $e->getMessage());
            }

            // Obtener total de alquileres
            $totalAlquileres = 0;
            try {
                $totalAlquileres = DB::table('tbl_alquiler')
                    ->where(function($q) use ($id) {
                        $q->where('id_arrendador_fk', $id)
                          ->orWhere('id_inquilino_fk', $id);
                    })
                    ->count();
            } catch (\Exception $e) {
                \Log::error('Error obteniendo alquileres: ' . $e->getMessage());
            }

            // Obtener suscripción
            $suscripcionNombre = 'Estándar';
            try {
                $suscripcion = DB::table('tbl_suscripcion')
                    ->where('id_usuario_fk', $id)
                    ->first();
                if ($suscripcion && isset($suscripcion->nombre_suscripcion)) {
                    $suscripcionNombre = $suscripcion->nombre_suscripcion;
                }
            } catch (\Exception $e) {
                \Log::error('Error obteniendo suscripción: ' . $e->getMessage());
            }

            return response()->json([
                'id_usuario' => $usuario->id_usuario,
                'nombre_usuario' => $usuario->nombre_usuario,
                'email_usuario' => $usuario->email_usuario,
                'telefono_usuario' => $usuario->telefono_usuario ?? 'N/A',
                'creado_usuario' => $usuario->creado_usuario,
                'activo_usuario' => $usuario->activo_usuario ?? false,
                'nombre_rol' => $usuario->nombre_rol ?? 'Sin rol',
                'slug_rol' => $usuario->slug_rol ?? null,
                'total_propiedades' => count($propiedadesFormato),
                'propiedades' => $propiedadesFormato,
                'total_alquileres' => $totalAlquileres,
                'suscripcion' => $suscripcionNombre
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en UsuarioController@show: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
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

    public function crear(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_usuario,email_usuario',
            'telefono' => 'nullable|string|max:20',
            'rol' => 'required|string|exists:tbl_rol,slug_rol',
            'password' => 'required|string|min:6'
        ]);

        try {
            // Crear usuario
            $usuarioId = DB::table('tbl_usuario')->insertGetId([
                'nombre_usuario' => $validated['nombre'],
                'email_usuario' => $validated['email'],
                'telefono_usuario' => $validated['telefono'] ?? '',
                'contrasena_usuario' => Hash::make($validated['password']),
                'activo_usuario' => true,
                'creado_usuario' => Carbon::now(),
                'actualizado_usuario' => Carbon::now()
            ]);

            // Asignar rol
            $rolId = DB::table('tbl_rol')
                ->where('slug_rol', $validated['rol'])
                ->value('id_rol');

            if ($rolId) {
                DB::table('tbl_rol_usuario')->insert([
                    'id_usuario_fk' => $usuarioId,
                    'id_rol_fk' => $rolId,
                    'asignado_rol_usuario' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'id' => $usuarioId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editar(Request $request, $id)
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_usuario,email_usuario,' . $id . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'rol' => 'required|string|exists:tbl_rol,slug_rol'
        ];
        
        // Password es opcional en edición, pero si se proporciona debe tener mínimo 6 caracteres
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }
        
        $validated = $request->validate($rules);

        try {
            // Actualizar usuario
            $updateData = [
                'nombre_usuario' => $validated['nombre'],
                'email_usuario' => $validated['email'],
                'telefono_usuario' => $validated['telefono'] ?? '',
                'actualizado_usuario' => Carbon::now()
            ];

            if (isset($validated['password']) && $validated['password']) {
                $updateData['contrasena_usuario'] = Hash::make($validated['password']);
            }

            DB::table('tbl_usuario')
                ->where('id_usuario', $id)
                ->update($updateData);

            // Actualizar rol
            $rolId = DB::table('tbl_rol')
                ->where('slug_rol', $validated['rol'])
                ->value('id_rol');

            if ($rolId) {
                DB::table('tbl_rol_usuario')
                    ->where('id_usuario_fk', $id)
                    ->update(['id_rol_fk' => $rolId]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
