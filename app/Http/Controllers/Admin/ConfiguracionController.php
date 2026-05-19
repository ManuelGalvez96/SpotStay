<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfiguracionController extends Controller
{
    public function index()
    {
        return view('admin.configuracion');
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