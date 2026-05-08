<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Plan;
use Carbon\Carbon;


class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            /** @var Usuario $user */
            $user = Auth::user();

            if ($user->roles()->where('slug_rol', 'admin')->exists()) {
                return redirect('/admin/dashboard');
            }

            if ($user->roles()->where('slug_rol', 'arrendador')->exists()) {
                return redirect('/arrendador/dashboard');
            }

            if ($user->roles()->where('slug_rol', 'gestor')->exists()) {
                return redirect('/gestor/dashboard');
            }

            if ($user->roles()->whereIn('slug_rol', ['miembro', 'inquilino'])->exists()) {
                return redirect('/miembro/inicio');
            }
        }
        return view('login');
    }

    /**
     * Procesa la solicitud de inicio de sesión.
     */
    public function authenticate(Request $request)
    {
        // 1. Validar los datos de entrada
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un formato de correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // 2. Buscar usuario por email
        $usuario = Usuario::where('email_usuario', $credentials['email'])->first();

        // 3. Verificar si el usuario existe y la contraseña es correcta
        if ($usuario && Hash::check($credentials['password'], $usuario->contrasena_usuario)) {

            // 4. Si las credenciales son correctas, comprobar si la cuenta está activa
            if (!$usuario->activo_usuario) {
                return back()->withErrors([
                    'email' => 'Esta cuenta está desactivada.<br> Contacta al administrador.',
                ])->onlyInput('email');
            }

            // 5. Bloqueo de acceso si tiene solicitud de Arrendador pendiente
            $solicitud = \App\Models\SolicitudArrendador::where('id_usuario_fk', $usuario->id_usuario)->first();

            if ($solicitud) {
                if ($solicitud->estado_solicitud_arrendador === 'pendiente') {
                    return back()->withErrors([
                        'email' => 'La solicitud se ha enviado correctamente, espere a la respuesta.',
                    ])->onlyInput('email');
                }

                if ($solicitud->estado_solicitud_arrendador === 'rechazada') {
                    $motivo = $solicitud->notas_solicitud_arrendador ? ": " . $solicitud->notas_solicitud_arrendador : ".";
                    return back()->withErrors([
                        'email' => 'Tu solicitud de arrendador ha sido rechazada y el motivo es:' . $motivo,
                    ])->onlyInput('email');
                }
            }

            // 6. Intentar el login (ya sabemos que las credenciales son correctas)
            Auth::login($usuario);
            $request->session()->regenerate();

            /** @var Usuario $user */
            $user = Auth::user();

            $request->session()->regenerate();

            /** @var Usuario $user */
            $user = Auth::user();

            // Redirigir según el rol del usuario
            if ($user->roles()->where('slug_rol', 'admin')->exists()) {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->roles()->where('slug_rol', 'gestor')->exists()) {
                return redirect()->intended('/gestor/dashboard');
            }

            if ($user->roles()->where('slug_rol', 'arrendador')->exists()) {
                return redirect()->intended('/arrendador/dashboard');
            }

            if ($user->roles()->whereIn('slug_rol', ['miembro', 'inquilino'])->exists()) {
                return redirect()->intended('/miembro/inicio');
            }

            // Fallback por si no tiene roles asignados
            return redirect()->intended('/');
        }


        // 4. Si la autenticación falla, volver con error
        return back()->withErrors([
            'email' => 'El correo electrónico o la contraseña son incorrectos.',
        ])->onlyInput('email');
    }

    /**
     * Muestra el formulario de registro.
     */
    public function showRegister()
    {
        $planes = Plan::where('activo_plan', true)->get();
        return view('registro', compact('planes'));
    }

    /**
     * Gestiona la creación de un nuevo usuario.
     */
    public function register(Request $request)
    {
        // 1. Validación de los datos
        $request->validate([
            'nombre' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:tbl_usuario,email_usuario',
            'telefono' => 'required|string|max:20|regex:/^\+\d{1,4} \d{6,11}$/',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:miembro,arrendador',
            'plan_id' => 'required|exists:tbl_plan,id_plan',
            'dni' => 'required_if:rol,arrendador|nullable|string|max:20',
            'nif' => 'required_if:tipo_arrendador,empresa|nullable|string|max:20',
            'fecha_nacimiento' => 'required_if:rol,arrendador|nullable|date',
            'tipo_arrendador' => 'required_if:rol,arrendador|nullable|in:particular,empresa',
            'tipo_documento' => 'required_if:rol,arrendador|nullable|in:dni,nie',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'Formato: +34 600123456 (Prefijo + Espacio + 6 a 11 dígitos)',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol.required' => 'Debes seleccionar un rol.',
            'plan_id.required' => 'Debes seleccionar un plan mensual.',
            'dni.required_if' => 'El DNI es obligatorio para arrendadores.',
            'nif.required_if' => 'El NIF es obligatorio para empresas.',
            'fecha_nacimiento.required_if' => 'La fecha de nacimiento es obligatoria.',
        ]);

        // 1.5 Validación de Seguridad: Arrendadores no pueden elegir planes gratuitos
        $plan = Plan::find($request->plan_id);
        if ($request->rol === 'arrendador' && $plan->precio_plan <= 0) {
            return back()->withErrors(['plan_id' => 'Los arrendadores deben seleccionar un plan de pago.'])->withInput();
        }

        // 2. Creación del usuario
        $usuario = Usuario::create([
            'nombre_usuario' => $request->nombre,
            'email_usuario' => $request->email,
            'telefono_usuario' => $request->telefono,
            'contrasena_usuario' => Hash::make($request->password),
            'dni_usuario' => ($request->tipo_arrendador === 'empresa') ? $request->nif : $request->dni,
            'fecha_nacimiento_usuario' => $request->fecha_nacimiento,
            'tipo_arrendador_usuario' => $request->tipo_arrendador,
            'activo_usuario' => true,
            'creado_usuario' => Carbon::now(),
            'actualizado_usuario' => Carbon::now(),
        ]);

        // 3. Asignación del rol y creación de solicitud si aplica
        // 3. Asignación del rol
        $rolSeleccionado = Rol::where('slug_rol', $request->rol)->first();
        if ($rolSeleccionado) {
            $usuario->roles()->attach($rolSeleccionado->id_rol, ['asignado_rol_usuario' => Carbon::now()]);
        }

        // Si es Arrendador, creamos la solicitud (para auditoría de documentos)
        if ($request->rol === 'arrendador') {
            \App\Models\SolicitudArrendador::create([
                'id_usuario_fk' => $usuario->id_usuario,
                'telefono_solicitud' => $usuario->telefono_usuario,
                'fecha_nacimiento_solicitud' => $request->fecha_nacimiento,
                'tipo_documento_solicitud' => $request->tipo_documento,
                'numero_documento_solicitud' => ($request->tipo_arrendador === 'empresa') ? $request->nif : $request->dni,
                'tipo_arrendador_solicitud' => $request->tipo_arrendador,
                'estado_solicitud_arrendador' => 'pendiente',
                'creado_solicitud_arrendador' => Carbon::now(),
                'actualizado_solicitud_arrendador' => Carbon::now(),
            ]);
        }

        // 4. Registro de la suscripción (Plan elegido)
        $plan = Plan::find($request->plan_id);
        if ($plan) {
            \App\Models\Suscripcion::create([
                'id_usuario_fk' => $usuario->id_usuario,
                'id_plan_fk' => $plan->id_plan,
                'plan_suscripcion' => $plan->nombre_plan,
                'precio_pagado_suscripcion' => $plan->precio_plan,
                'max_propiedades_suscripcion' => $plan->max_propiedades_plan,
                'estado_suscripcion' => ($plan->precio_plan > 0) ? 'pendiente_pago' : 'activa',
                'inicio_suscripcion' => Carbon::now(),
                'creado_suscripcion' => Carbon::now(),
            ]);
        }

        // 4. Redirigir al login SIN loguear (Seguridad)
        if ($request->rol === 'arrendador') {
            return redirect('/login')->with('status', 'La solicitud se ha enviado correctamente, espere a la respuesta.');
        }

        return redirect('/login')->with('status', '¡Bienvenido a SpotStay! <br>Tu cuenta ha sido creada con éxito.');
    }

    /**
     * Comprueba si un correo electrónico está disponible.
     * (Usado por la validación JS en tiempo real)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        $existe = Usuario::where('email_usuario', $email)->exists();

        return response()->json([
            'disponible' => !$existe
        ]);
    }

    public function checkTelefono(Request $request)
    {
        $telefono = $request->query('telefono');
        $existe = Usuario::where('telefono_usuario', $telefono)->exists();

        return response()->json([
            'disponible' => !$existe
        ]);
    }

    /**
     * Cierra la sesión del usuario.


     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Sesión cerrada correctamente.');
    }
}
