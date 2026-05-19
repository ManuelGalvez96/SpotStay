<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracionCobrosController extends Controller
{
    /**
     * Muestra el formulario para configurar el IBAN y datos fiscales.
     */
    public function index()
    {
        $usuario = Auth::user();

        // Si ya tiene cuenta de Stripe o IBAN, y no viene forzado, podríamos dejarle pasar,
        // pero permitimos que entre para editar si lo desea.
        return view('arrendador.configurar_iban', compact('usuario'));
    }

    /**
     * Guarda los datos bancarios y fiscales del usuario.
     */
    public function store(Request $request)
    {
        // Limpiamos los espacios del IBAN antes de validar
        if ($request->has('iban')) {
            $request->merge([
                'iban' => str_replace(' ', '', $request->iban)
            ]);
        }

        $request->validate([
            'iban' => ['required', 'string', 'regex:/^[A-Z]{2}[0-9]{2}[0-9]{20}$/i'],
            'titular' => ['required', 'string', 'min:2', 'max:255'],
            'direccion_fiscal' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'iban.required' => 'El IBAN es obligatorio.',
            'iban.regex' => 'El IBAN debe tener 24 caracteres (ES + 22 números).',
            'titular.required' => 'El titular es obligatorio.',
            'direccion_fiscal.required' => 'La dirección fiscal es necesaria.',
        ]);

        try {
            $usuario = Auth::user();
            
            // Asignación manual para asegurar el guardado
            $usuario->iban_usuario = strtoupper($request->iban);
            $usuario->direccion_fiscal_usuario = $request->direccion_fiscal;
            
            // Si el usuario es nuevo arrendador, le generamos un ID manual temporal para Stripe Connect
            if (empty($usuario->stripe_account_id)) {
                $usuario->stripe_account_id = 'acct_manual_' . uniqid();
            }
            
            $usuario->save();

            return redirect('/arrendador/dashboard')->with('status', '¡Configuración completada con éxito!');
            
        } catch (\Exception $e) {
            // Si hay un error de base de datos, lo devolvemos a la vista para saber qué pasa
            return back()->withInput()->with('error', 'Error al guardar en base de datos: ' . $e->getMessage());
        }
    }
}
