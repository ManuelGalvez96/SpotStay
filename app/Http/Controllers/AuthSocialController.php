<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthSocialController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Maneja el retorno de Google y autentica al usuario.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Buscamos si el usuario ya existe por nuestro campo email_usuario
            $usuario = Usuario::where('email_usuario', $googleUser->email)->first();

            if ($usuario) {
                // Si existe pero no tiene el google_id vinculado, lo vinculamos
                if (!$usuario->google_id) {
                    $usuario->update(['google_id' => $googleUser->id]);
                }
            } else {
                // Si no existe, creamos el nuevo usuario
                $usuario = Usuario::create([
                    'nombre_usuario' => $googleUser->name,
                    'email_usuario' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar_usuario' => $googleUser->avatar,
                    'activo_usuario' => true,
                    'creado_usuario' => now(),
                    // La contraseña queda nula ya que entra por Google
                ]);
            }

            // Iniciamos sesión. Nota: Auth::login() funcionará si indicamos el guard correcto
            // o si el modelo Usuario implementa Authenticatable (que ya lo hace).
            Auth::login($usuario);

            return redirect()->intended('/admin/dashboard');

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Error al iniciar sesión con Google: ' . $e->getMessage());
        }
    }
}
