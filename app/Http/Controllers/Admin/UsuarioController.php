<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuarioController extends Controller
{
    private function obtenerPlanesSuscripcion()
    {
        return DB::table('tbl_plan')
            ->select('id_plan', 'nombre_plan', 'slug_plan', 'activo_plan')
            ->orderBy('nombre_plan')
            ->get();
    }

    private function obtenerSuscripcionActualUsuario($idUsuario)
    {
        return DB::table('tbl_suscripcion as sus')
            ->leftJoin('tbl_plan as plan', 'plan.id_plan', '=', 'sus.id_plan_fk')
            ->where('sus.id_usuario_fk', $idUsuario)
            ->orderByDesc('sus.id_suscripcion')
            ->select(
                'sus.id_suscripcion',
                'sus.id_usuario_fk',
                'sus.id_plan_fk',
                'sus.plan_suscripcion',
                'sus.precio_pagado_suscripcion',
                'sus.max_propiedades_suscripcion',
                'sus.inicio_suscripcion',
                'sus.fin_suscripcion',
                'sus.estado_suscripcion',
                'plan.nombre_plan as suscripcion_nombre'
            )
            ->first();
    }

    private function enriquecerUsuariosConSuscripcion($usuarios)
    {
        $idsUsuarios = $usuarios->pluck('id_usuario')->filter()->values()->all();

        if (empty($idsUsuarios)) {
            return $usuarios;
        }

        $suscripciones = DB::table('tbl_suscripcion as sus')
            ->leftJoin('tbl_plan as plan', 'plan.id_plan', '=', 'sus.id_plan_fk')
            ->whereIn('sus.id_usuario_fk', $idsUsuarios)
            ->whereRaw('sus.id_suscripcion = (SELECT MAX(s2.id_suscripcion) FROM tbl_suscripcion s2 WHERE s2.id_usuario_fk = sus.id_usuario_fk)')
            ->select(
                'sus.id_usuario_fk',
                'sus.id_plan_fk',
                'sus.plan_suscripcion',
                'sus.estado_suscripcion',
                'plan.nombre_plan as suscripcion_nombre'
            )
            ->get()
            ->keyBy('id_usuario_fk');

        return $usuarios->map(function ($usuario) use ($suscripciones) {
            $suscripcion = $suscripciones->get($usuario->id_usuario);
            $idPlanFk = $suscripcion?->id_plan_fk;
            $planSuscripcion = $suscripcion?->plan_suscripcion;
            $nombreSuscripcion = $suscripcion?->suscripcion_nombre;

            $usuario->id_plan_fk = $idPlanFk;
            $usuario->plan_suscripcion = $planSuscripcion;
            $usuario->suscripcion_estado = $suscripcion?->estado_suscripcion;
            $usuario->suscripcion_nombre = $nombreSuscripcion;
            $usuario->suscripcion_label = $nombreSuscripcion
                ?? ($planSuscripcion ? ucfirst($planSuscripcion) : 'Sin suscripción');

            return $usuario;
        });
    }

    private function datosSuscripcionDesdePlan($idPlan, $idUsuario)
    {
        $plan = DB::table('tbl_plan')
            ->where('id_plan', $idPlan)
            ->first();

        if (!$plan) {
            return null;
        }

        return [
            'id_usuario_fk' => $idUsuario,
            'plan_suscripcion' => $plan->slug_plan,
            'id_plan_fk' => $plan->id_plan,
            'max_propiedades_suscripcion' => $plan->max_propiedades_plan,
            'precio_pagado_suscripcion' => $plan->precio_plan,
            'inicio_suscripcion' => Carbon::now()->toDateString(),
            'fin_suscripcion' => null,
            'estado_suscripcion' => 'activa',
            'creado_suscripcion' => Carbon::now(),
            'actualizado_suscripcion' => Carbon::now(),
        ];
    }

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

                $usuarios->setCollection($this->enriquecerUsuariosConSuscripcion($usuarios->getCollection()));

                $planesSuscripcion = $this->obtenerPlanesSuscripcion();

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
            'usuarios', 'planesSuscripcion', 'totalUsuarios', 'activos', 'inactivos', 'esteMes'));
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

        $usuariosPaginados->setCollection($this->enriquecerUsuariosConSuscripcion($usuariosPaginados->getCollection()));
        
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
                'suscripcionLabel' => $u->suscripcion_label ?? 'Sin suscripción',
                'id_plan_fk' => $u->id_plan_fk ?? null,
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
                Log::error('Error obteniendo propiedades: ' . $e->getMessage());
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
                Log::error('Error obteniendo alquileres: ' . $e->getMessage());
            }

            $suscripcion = $this->obtenerSuscripcionActualUsuario($id);
            $suscripcionNombre = $suscripcion->suscripcion_nombre
                ?? ($suscripcion->plan_suscripcion ?? 'Sin suscripción');

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
                'suscripcion' => $suscripcionNombre,
                'id_plan_fk' => $suscripcion->id_plan_fk ?? null,
                'plan_suscripcion' => $suscripcion->plan_suscripcion ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Error en UsuarioController@show: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function toggleEstado($id)
    {
        $usuario = DB::table('tbl_usuario')
            ->where('id_usuario', $id)
            ->first();

        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        $nuevoEstado = !$usuario->activo_usuario;

        // Si intentan desactivar al usuario, comprobar si tiene alquileres activos o propiedades publicadas
        if (!$nuevoEstado) {
            // Verificar si es inquilino con alquileres activos
            $tieneAlquilerActivoInquilino = DB::table('tbl_alquiler')
                ->where('id_inquilino_fk', $id)
                ->where('estado_alquiler', 'activo')
                ->exists();

            // Verificar si es arrendador con alquileres activos O propiedades publicadas
            $tieneAlquilerActivoArrendador = DB::table('tbl_alquiler')
                ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
                ->where('tbl_propiedad.id_arrendador_fk', $id)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->exists();

            $tienePropiedadesPublicadas = DB::table('tbl_propiedad')
                ->where('id_arrendador_fk', $id)
                ->whereIn('estado_propiedad', ['publicada', 'alquilada'])
                ->exists();

            if ($tieneAlquilerActivoInquilino || $tieneAlquilerActivoArrendador || $tienePropiedadesPublicadas) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar el usuario porque tiene contratos activos o propiedades publicadas.'
                ]);
            }
        }

        DB::table('tbl_usuario')
            ->where('id_usuario', $id)
            ->update(['activo_usuario' => $nuevoEstado, 'actualizado_usuario' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'activo' => $nuevoEstado
        ]);
    }

    public function getKpisUsuarios()
    {
        $totalUsuarios = DB::table('tbl_usuario')->count();
        $activos = DB::table('tbl_usuario')
            ->where('activo_usuario', true)->count();
        $inactivos = DB::table('tbl_usuario')
            ->where('activo_usuario', false)->count();
        $esteMes = DB::table('tbl_usuario')
            ->whereMonth('creado_usuario', Carbon::now()->month)
            ->whereYear('creado_usuario', Carbon::now()->year)
            ->count();

        return response()->json([
            'totalUsuarios' => $totalUsuarios,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'esteMes' => $esteMes
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
            'suscripcion_plan' => 'nullable|integer|exists:tbl_plan,id_plan',
            'password' => 'required|string|min:6'
        ]);

        try {
            DB::beginTransaction();

            $usuarioId = DB::table('tbl_usuario')->insertGetId([
                'nombre_usuario' => $validated['nombre'],
                'email_usuario' => $validated['email'],
                'telefono_usuario' => $validated['telefono'] ?? '',
                'contrasena_usuario' => Hash::make($validated['password']),
                'activo_usuario' => true,
                'creado_usuario' => Carbon::now(),
                'actualizado_usuario' => Carbon::now()
            ]);

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

            if (!empty($validated['suscripcion_plan'])) {
                $datosSuscripcion = $this->datosSuscripcionDesdePlan($validated['suscripcion_plan'], $usuarioId);

                if ($datosSuscripcion) {
                    DB::table('tbl_suscripcion')->insert($datosSuscripcion);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'id' => $usuarioId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
            'rol' => 'required|string|exists:tbl_rol,slug_rol',
            'suscripcion_plan' => 'nullable|integer|exists:tbl_plan,id_plan'
        ];
        
        // Password es opcional en edición, pero si se proporciona debe tener mínimo 6 caracteres
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }
        
        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

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

            $rolId = DB::table('tbl_rol')
                ->where('slug_rol', $validated['rol'])
                ->value('id_rol');

            if ($rolId) {
                DB::table('tbl_rol_usuario')
                    ->where('id_usuario_fk', $id)
                    ->update(['id_rol_fk' => $rolId]);
            }

            if (!empty($validated['suscripcion_plan'])) {
                $datosSuscripcion = $this->datosSuscripcionDesdePlan($validated['suscripcion_plan'], $id);

                if ($datosSuscripcion) {
                    $suscripcionActual = DB::table('tbl_suscripcion')
                        ->where('id_usuario_fk', $id)
                        ->orderByDesc('id_suscripcion')
                        ->first();

                    if ($suscripcionActual) {
                        DB::table('tbl_suscripcion')
                            ->where('id_suscripcion', $suscripcionActual->id_suscripcion)
                            ->update([
                                'plan_suscripcion' => $datosSuscripcion['plan_suscripcion'],
                                'id_plan_fk' => $datosSuscripcion['id_plan_fk'],
                                'max_propiedades_suscripcion' => $datosSuscripcion['max_propiedades_suscripcion'],
                                'precio_pagado_suscripcion' => $datosSuscripcion['precio_pagado_suscripcion'],
                                'inicio_suscripcion' => $datosSuscripcion['inicio_suscripcion'],
                                'fin_suscripcion' => $datosSuscripcion['fin_suscripcion'],
                                'estado_suscripcion' => $datosSuscripcion['estado_suscripcion'],
                                'actualizado_suscripcion' => Carbon::now(),
                            ]);
                    } else {
                        DB::table('tbl_suscripcion')->insert($datosSuscripcion);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
