<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
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

            if ($user->roles()->whereIn('slug_rol', ['miembro', 'inquilino', 'propietario'])->exists()) {
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

            // 5. Intentar el login (ya sabemos que las credenciales son correctas)
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
        return view('registro');
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
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'Formato: +34 600123456 ("+" + Prefijo + Espacio + 6 a 11 dígitos)',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // 2. Creación del usuario
        $usuario = Usuario::create([
            'nombre_usuario' => $request->nombre,
            'email_usuario' => $request->email,
            'telefono_usuario' => $request->telefono,
            'contrasena_usuario' => Hash::make($request->password),
            'activo_usuario' => true,
            'creado_usuario' => Carbon::now(),
            'actualizado_usuario' => Carbon::now(),
        ]);

        // 3. Asignación automática del rol "miembro"
        $rolMiembro = Rol::where('slug_rol', 'miembro')->first();
        if ($rolMiembro) {
            $usuario->roles()->attach($rolMiembro->id_rol, [
                'asignado_rol_usuario' => Carbon::now()
            ]);
        }

        // 4. Redirigir al inicio o dashboard
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
