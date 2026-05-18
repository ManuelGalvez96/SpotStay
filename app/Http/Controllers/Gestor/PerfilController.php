<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $gestor = Auth::user();
        return view('gestor.perfil', compact('gestor'));
    }

    public function update(Request $request)
    {
        $gestor = Auth::user();
        $datosActualizar = [];

        if ($request->has('nombre_usuario')) {
            $validado = $request->validate([
                'nombre_usuario' => 'required|string|max:100',
                'email_usuario' => 'required|email|max:150|unique:tbl_usuario,email_usuario,' . $gestor->id_usuario . ',id_usuario',
                'telefono_usuario' => 'nullable|string|max:20',
            ], [
                'nombre_usuario.required' => 'El nombre es obligatorio.',
                'email_usuario.required' => 'El correo electrónico es obligatorio.',
                'email_usuario.email' => 'El correo electrónico no es válido.',
                'email_usuario.unique' => 'Este correo electrónico ya está registrado.',
            ]);
            $datosActualizar = array_merge($datosActualizar, $validado);
        }

        if ($request->hasFile('avatar_usuario')) {
            $request->validate([
                'avatar_usuario' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ], [
                'avatar_usuario.image' => 'El archivo debe ser una imagen.',
                'avatar_usuario.mimes' => 'La imagen debe ser JPEG, PNG, GIF o WebP.',
                'avatar_usuario.max' => 'La imagen no puede superar los 2MB.',
            ]);
            $datosActualizar['avatar_usuario'] = $request->file('avatar_usuario')->store('avatares', 'public');
        }

        if ($request->filled('contrasena_usuario')) {
            $request->validate([
                'contrasena_actual' => 'required|string',
                'contrasena_usuario' => 'required|string|min:6|confirmed|regex:/[0-9]/',
            ], [
                'contrasena_actual.required' => 'La contraseña actual es obligatoria.',
                'contrasena_usuario.required' => 'La nueva contraseña es obligatoria.',
                'contrasena_usuario.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'contrasena_usuario.confirmed' => 'Las contraseñas no coinciden.',
                'contrasena_usuario.regex' => 'La contraseña debe contener al menos un número.',
            ]);

            if (!Hash::check($request->contrasena_actual, $gestor->contrasena_usuario)) {
                return back()->withErrors(['contrasena_actual' => 'La contraseña actual no es correcta.'])->withInput();
            }

            $datosActualizar['contrasena_usuario'] = Hash::make($request->contrasena_usuario);
        }

        if (!empty($datosActualizar)) {
            $gestor->update($datosActualizar);
            return redirect()->route('gestor.perfil')->with('success', 'Perfil actualizado correctamente.');
        }

        return redirect()->route('gestor.perfil');
    }
}
