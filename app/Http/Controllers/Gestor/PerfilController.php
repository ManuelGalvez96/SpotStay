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

        $rules = [
            'nombre_usuario' => 'required|string|max:100',
            'email_usuario' => 'required|email|max:150|unique:tbl_usuario,email_usuario,' . $gestor->id_usuario . ',id_usuario',
            'telefono_usuario' => 'nullable|string|max:20',
        ];

        if ($request->filled('contrasena_usuario')) {
            $rules['contrasena_actual'] = 'required|string';
            $rules['contrasena_usuario'] = 'required|string|min:6|confirmed';
        }

        if ($request->hasFile('avatar_usuario')) {
            $rules['avatar_usuario'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $validated = $request->validate($rules);

        if ($request->filled('contrasena_usuario')) {
            if (!Hash::check($request->contrasena_actual, $gestor->contrasena_usuario)) {
                return back()->withErrors(['contrasena_actual' => 'La contraseña actual no es correcta.'])->withInput();
            }
            $validated['contrasena_usuario'] = Hash::make($validated['contrasena_usuario']);
        } else {
            unset($validated['contrasena_usuario']);
        }

        if ($request->hasFile('avatar_usuario')) {
            $path = $request->file('avatar_usuario')->store('avatares', 'public');
            $validated['avatar_usuario'] = $path;
        }

        unset($validated['contrasena_actual']);

        $gestor->update($validated);

        return redirect()->route('gestor.perfil')->with('success', 'Perfil actualizado correctamente.');
    }
}
