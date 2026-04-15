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
            return redirect('/admin/dashboard');
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

        // 2. Intentar autenticar al usuario
        // Nota: Laravel usará 'email_usuario' para buscar el registro y 
        // comparará la contraseña usando el método getAuthPassword() que definimos en el modelo.
        if (Auth::attempt([
            'email_usuario' => $credentials['email'],
            'password' => $credentials['password']
        ])) {


            // Si tiene éxito, regenerar la sesión para evitar ataques de fijación de sesión
            $request->session()->regenerate();

            // Redirigir al panel de administración (o a la página que intentaba visitar)
            return redirect()->intended('/admin/dashboard');
        }

        // 3. Si la autenticación falla, volver con error
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
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // 2. Creación del usuario
        $usuario = Usuario::create([
            'nombre_usuario' => $request->nombre,
            'email_usuario' => $request->email,
            'contrasena_usuario' => Hash::make($request->password),
            'activo_usuario' => true,
            'creado_usuario' => Carbon::now(),
            'actualizado_usuario' => Carbon::now(),
        ]);

        // 3. Asignación automática del rol "inquilino"
        $rolInquilino = Rol::where('slug_rol', 'inquilino')->first();
        if ($rolInquilino) {
            $usuario->roles()->attach($rolInquilino->id_rol, [
                'asignado_rol_usuario' => Carbon::now()
            ]);
        }

        // 4. Redirigir al inicio o dashboard
        return redirect('/login')->with('status', '¡Bienvenido a SpotStay! Tu cuenta ha sido creada con éxito.');
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
