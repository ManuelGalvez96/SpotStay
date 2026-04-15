<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

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
        ], $request->filled('remember'))) {

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
