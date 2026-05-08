<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Carbon\Carbon;

class MiembroSuscripcionController extends Controller
{
    /**
     * Muestra el panel de suscripción con el plan pendiente de pago.
     */
    public function index()
    {
        $usuario = Auth::user();
        
        // Buscamos la suscripción pendiente más reciente
        $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->where('estado_suscripcion', 'pendiente_pago')
            ->latest('id_suscripcion')
            ->first();

        // Si ya está activo, al dashboard directo
        if ($usuario->stripe_status === 'active') {
            return redirect($this->redirigirDashboard());
        }

        // Si no tiene nada pendiente y no es activo, al registro para que elija plan
        if (!$suscripcion) {
            return redirect('/register')->with('info', 'Por favor, selecciona un plan para continuar.');
        }

        return view('miembro.suscripcion', compact('suscripcion'));
    }

    /**
     * Crea una sesión de Checkout de Stripe para el plan seleccionado.
     */
    public function checkout(Request $request)
    {
        $usuario = Auth::user();
        $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->where('estado_suscripcion', 'pendiente_pago')
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcion) {
            return back()->with('error', 'No se ha encontrado ninguna suscripción pendiente.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Suscripción SpotStay: ' . $suscripcion->plan_suscripcion,
                    ],
                    'unit_amount' => (int)($suscripcion->precio_pagado_suscripcion * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('miembro.suscripcion.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('miembro.suscripcion.index'),
            'customer_email' => $usuario->email_usuario,
            'metadata' => [
                'id_suscripcion' => $suscripcion->id_suscripcion,
                'id_usuario' => $usuario->id_usuario
            ]
        ]);

        return redirect($session->url);
    }

    /**
     * Maneja el éxito del pago.
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        if (!$sessionId) return redirect()->route('miembro.suscripcion.index');

        // Configurar Stripe para recuperar la sesión
        Stripe::setApiKey(config('services.stripe.secret'));
        $checkoutSession = Session::retrieve($sessionId);
        
        // Recuperar ID de suscripción de los metadatos
        $idSuscripcion = $checkoutSession->metadata->id_suscripcion;
        $usuario = Auth::user();
        
        // Buscar la suscripción específica
        $suscripcion = Suscripcion::where('id_suscripcion', $idSuscripcion)
            ->where('id_usuario_fk', $usuario->id_usuario)
            ->first();

        if ($suscripcion) {
            $suscripcion->update([
                'estado_suscripcion' => 'activa',
                'inicio_suscripcion' => Carbon::now(),
                'actualizado_suscripcion' => Carbon::now()
            ]);

            $usuario->update([
                'stripe_status' => 'active'
            ]);
        }

        return redirect($this->redirigirDashboard())->with('success', '¡Pago realizado con éxito! Tu cuenta ya está activa.');
    }

    private function redirigirDashboard()
    {
        $user = Auth::user();
        if ($user->roles()->where('slug_rol', 'arrendador')->exists()) {
            return '/arrendador/dashboard';
        }
        return '/miembro/inicio';
    }
}
