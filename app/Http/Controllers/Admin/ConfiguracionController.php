<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\ActividadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $usuariosActivos = DB::table('tbl_usuario as u')
            ->leftJoin('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
            ->leftJoin('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('activo_usuario', true)
            ->groupBy('u.id_usuario', 'u.nombre_usuario', 'u.email_usuario')
            ->select(
                'u.id_usuario',
                'u.nombre_usuario',
                'u.email_usuario',
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT r.slug_rol ORDER BY r.slug_rol SEPARATOR ','), '') as roles_usuario")
            )
            ->orderBy('nombre_usuario')
            ->get();

        $rolesDisponibles = DB::table('tbl_rol')
            ->select('slug_rol', 'nombre_rol')
            ->orderBy('nombre_rol')
            ->get();

        return view('admin.configuracion', compact('usuariosActivos', 'rolesDisponibles'));
    }

    public function crearNotificacion(Request $request)
    {
        $rolesValidos = DB::table('tbl_rol')->pluck('slug_rol')->all();

        $datos = $request->validate([
            'destino' => ['required', Rule::in(array_merge(['todos'], $rolesValidos))],
            'alcance_destino' => ['required', Rule::in(['todos', 'usuario'])],
            'usuario_destino' => ['nullable', 'integer', 'exists:tbl_usuario,id_usuario'],
            'titulo_notificacion' => ['required', 'string', 'max:200'],
            'mensaje_notificacion' => ['required', 'string', 'max:1000'],
            'url_notificacion' => ['nullable', 'string', 'max:500'],
        ]);

        $usuariosIds = collect();

        if ($datos['alcance_destino'] === 'todos') {
            if ($datos['destino'] === 'todos') {
                $usuariosIds = DB::table('tbl_usuario')->where('activo_usuario', true)->pluck('id_usuario');
            } else {
                $usuariosIds = DB::table('tbl_usuario as u')
                    ->join('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
                    ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
                    ->where('u.activo_usuario', true)
                    ->where('r.slug_rol', $datos['destino'])
                    ->pluck('u.id_usuario');
            }
        } else {
            if (empty($datos['usuario_destino'])) {
                return back()->with('error', 'Debes elegir un usuario.');
            }

            $usuarioDestino = DB::table('tbl_usuario as u')
                ->leftJoin('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
                ->leftJoin('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
                ->where('u.id_usuario', (int) $datos['usuario_destino'])
                ->where('u.activo_usuario', true)
                ->select('u.id_usuario', DB::raw("COALESCE(GROUP_CONCAT(DISTINCT r.slug_rol SEPARATOR ','), '') as roles_usuario"))
                ->groupBy('u.id_usuario')
                ->first();

            if (!$usuarioDestino) {
                return back()->with('error', 'El usuario seleccionado no existe o no está activo.');
            }

            if ($datos['destino'] !== 'todos') {
                $rolesUsuario = array_filter(explode(',', (string) $usuarioDestino->roles_usuario));

                if (!in_array($datos['destino'], $rolesUsuario, true)) {
                    return back()->with('error', 'El usuario seleccionado no pertenece al rol elegido.');
                }
            }

            $usuariosIds = collect([(int) $usuarioDestino->id_usuario]);
        }

        $actividadService = new ActividadService();
        foreach ($usuariosIds->unique() as $usuarioId) {
            $actividadService->avisoImportante(
                (int) $usuarioId,
                $datos['titulo_notificacion'],
                $datos['mensaje_notificacion'],
                $datos['url_notificacion'] ?: null
            );
        }

        return back()->with('mensaje_exito_plan', 'Notificación importante enviada correctamente.');
    }

    public function planes()
    {
        $planes = Plan::orderBy('id_plan')->get();

        return view('admin.planes', compact('planes'));
    }

    public function crearPlan(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre_plan' => ['required', 'string', 'max:50'],
            'slug_plan' => ['required', 'string', 'max:30', 'unique:tbl_plan,slug_plan'],
            'rol_destino' => ['required', Rule::in(['miembro', 'arrendador', 'inquilino', 'gestor'])],
            'precio_plan' => ['required', 'numeric', 'min:0'],
            'max_propiedades_plan' => ['required', 'integer', 'min:0', 'max:255'],
            'descripcion_plan' => ['nullable', 'string'],
            'activo_plan' => ['nullable', 'boolean'],
        ]);

        Plan::create([
            'nombre_plan' => $datosValidados['nombre_plan'],
            'slug_plan' => $datosValidados['slug_plan'],
            'rol_destino' => $datosValidados['rol_destino'],
            'precio_plan' => $datosValidados['precio_plan'],
            'max_propiedades_plan' => $datosValidados['max_propiedades_plan'],
            'descripcion_plan' => $datosValidados['descripcion_plan'] ?? null,
            'activo_plan' => $request->boolean('activo_plan'),
            'creado_plan' => Carbon::now(),
            'actualizado_plan' => Carbon::now(),
        ]);

        return redirect()->route('admin.planes')->with('mensaje_exito_plan', 'Plan creado correctamente.');
    }

    public function actualizarPlan(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $datosValidados = $request->validate([
            'nombre_plan' => ['required', 'string', 'max:50'],
            'slug_plan' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tbl_plan', 'slug_plan')->ignore($plan->id_plan, 'id_plan'),
            ],
            'rol_destino' => ['required', Rule::in(['miembro', 'arrendador', 'inquilino', 'gestor'])],
            'precio_plan' => ['required', 'numeric', 'min:0'],
            'max_propiedades_plan' => ['required', 'integer', 'min:0', 'max:255'],
            'descripcion_plan' => ['nullable', 'string'],
            'activo_plan' => ['nullable', 'boolean'],
        ]);

        $plan->nombre_plan = $datosValidados['nombre_plan'];
        $plan->slug_plan = $datosValidados['slug_plan'];
        $plan->rol_destino = $datosValidados['rol_destino'];
        $plan->precio_plan = $datosValidados['precio_plan'];
        $plan->max_propiedades_plan = $datosValidados['max_propiedades_plan'];
        $plan->descripcion_plan = $datosValidados['descripcion_plan'] ?? null;
        $plan->activo_plan = $request->boolean('activo_plan');
        $plan->actualizado_plan = Carbon::now();
        $plan->save();

        return redirect()->route('admin.planes')->with('mensaje_exito_plan', 'Plan actualizado correctamente.');
    }

    public function eliminarPlan(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        // No permitir eliminar planes activos
        if ($plan->activo_plan) {
            return redirect()->route('admin.planes')->with('mensaje_error_plan', 'No se puede eliminar un plan activo. Desactívalo primero.');
        }

        // No permitir eliminar si existen suscripciones asociadas
        if ($plan->suscripciones()->exists()) {
            return redirect()->route('admin.planes')->with('mensaje_error_plan', 'No se puede eliminar el plan porque hay usuarios suscritos a él.');
        }

        $plan->delete();

        return redirect()->route('admin.planes')->with('mensaje_exito_plan', 'Plan eliminado correctamente.');
    }
}