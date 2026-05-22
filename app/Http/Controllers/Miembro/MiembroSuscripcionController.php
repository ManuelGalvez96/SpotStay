<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Suscripcion;
use App\Models\Plan;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
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
        $usuarioModelo = Usuario::find($usuario->id_usuario);
        
        // Buscamos la suscripción pendiente o activa más reciente
        $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->whereIn('estado_suscripcion', ['pendiente_pago', 'activa'])
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcion) {
            if ($usuarioModelo && $usuarioModelo->stripe_status !== 'active') {
                $usuarioModelo->update([
                    'stripe_status' => 'active',
                ]);
            }

            return redirect($this->redirigirDashboard())
                ->with('info', 'No se encontró una suscripción asociada. Se ha permitido el acceso al panel.');
        }

        // Si la suscripción ya está activa, sincronizamos el estado del usuario y vamos al dashboard
        if ($suscripcion->estado_suscripcion === 'activa') {
            if ($usuarioModelo && $usuarioModelo->stripe_status !== 'active') {
                $usuarioModelo->update([
                    'stripe_status' => 'active',
                ]);
            }

            return redirect($this->redirigirDashboard());
        }

        // Si Stripe ya está activo, también vamos al dashboard aunque la suscripción siga en pendiente
        if ($usuario->stripe_status === 'active') {
            return redirect($this->redirigirDashboard());
        }

        return view('miembro.suscripcion', compact('suscripcion', 'rutaRetorno'));
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
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('miembro.suscripcion.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('miembro.suscripcion.index'),
            'customer_email' => $usuario->email_usuario,
            'metadata' => [
                'id_suscripcion' => $suscripcion->id_suscripcion,
                'id_usuario' => $usuario->id_usuario
            ],
            'subscription_data' => [
                'metadata' => [
                    'id_suscripcion' => $suscripcion->id_suscripcion,
                    'id_usuario' => $usuario->id_usuario
                ]
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
        $usuarioModelo = Usuario::find($usuario->id_usuario);
        
        // Buscar la suscripción específica
        $suscripcion = Suscripcion::where('id_suscripcion', $idSuscripcion)
            ->where('id_usuario_fk', $usuario->id_usuario)
            ->first();

        if ($suscripcion) {
            DB::beginTransaction();
            try {
                // 1. Actualizar suscripción
                $suscripcion->update([
                    'estado_suscripcion' => 'activa',
                    'inicio_suscripcion' => Carbon::now(),
                    'fin_suscripcion' => Carbon::now()->addMonth(),
                    'actualizado_suscripcion' => Carbon::now()
                ]);

                // 2. Actualizar estado del usuario
                if ($usuarioModelo) {
                    $usuarioModelo->update([
                        'stripe_status' => 'active',
                        'stripe_subscription_id' => $checkoutSession->subscription ?? null
                    ]);

                    // Refrescamos el modelo para que el Middleware vea los cambios inmediatamente
                    $usuarioModelo->refresh();
                }

                // 3. Registrar el Pago en la tabla de pagos
                $pago = Pago::create([
                    'id_pagador_fk' => $usuario->id_usuario,
                    'tipo_pago' => 'suscripcion',
                    'concepto_pago' => 'Suscripción Plan: ' . $suscripcion->plan_suscripcion,
                    'importe_pago' => $suscripcion->precio_pagado_suscripcion,
                    'estado_pago' => 'pagado',
                    'referencia_pago' => $checkoutSession->payment_intent,
                    'fecha_confirmacion_pago' => now(),
                    'creado_pago' => now(),
                    'actualizado_pago' => now(),
                ]);

                DB::commit();

                // 4. Generar Factura PDF (Aislado para no romper el flujo si falla PDFMonkey)
                try {
                    $this->generarFacturaSuscripcion($pago->id_pago);
                } catch (\Exception $eFactura) {
                    Log::warning("Pago procesado pero falló la factura: " . $eFactura->getMessage());
                }

            } catch (\Exception $e) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                Log::error("Error procesando éxito de suscripción: " . $e->getMessage());
                return redirect()->route('miembro.suscripcion.index')->with('error', 'Error al procesar el pago.');
            }
        }

        return redirect($this->redirigirDashboard())->with('success', '¡Pago realizado con éxito! Tu cuenta ya está activa.');
    }

    /**
     * Genera la factura PDF para una suscripción usando PDFMonkey.
     */
    private function generarFacturaSuscripcion($idPago)
    {
        try {
            $pagoInfo = Pago::with('pagador')->findOrFail($idPago);
            $usuario = $pagoInfo->pagador;

            $fechaPago = Carbon::parse($pagoInfo->fecha_confirmacion_pago)->format('d/m/Y H:i');
            $mesReferencia = Carbon::parse($pagoInfo->fecha_confirmacion_pago)->translatedFormat('F Y');

            $suscripcion = \App\Models\Suscripcion::where('id_usuario_fk', $usuario->id_usuario)->latest('id_suscripcion')->first();
            $periodoFin = $suscripcion && str_contains(strtolower($suscripcion->plan_suscripcion), 'mensual') 
                ? Carbon::parse($pagoInfo->fecha_confirmacion_pago)->addMonth()->format('d/m/Y') 
                : Carbon::parse($pagoInfo->fecha_confirmacion_pago)->addYear()->format('d/m/Y');

            $total = (float) $pagoInfo->importe_pago;
            $base = $total / 1.21;
            $iva = $total - $base;

            // Payload específico para la nueva plantilla de Suscripciones en PDFMonkey
            $payload = [
                'nombre_cliente' => $usuario->nombre_usuario,
                'dni_cliente' => $usuario->dni_usuario ?? 'No especificado',
                'direccion_cliente' => $usuario->direccion_fiscal_usuario ?? 'Dirección no especificada',
                'email_cliente' => $usuario->email_usuario,
                
                'numero_factura' => date('Y') . '-' . str_pad((string) $idPago, 6, '0', STR_PAD_LEFT),
                'fecha_emision' => $fechaPago,
                
                'plan_nombre' => str_replace('Suscripción Plan: ', '', $pagoInfo->concepto_pago),
                'periodo_inicio' => Carbon::parse($pagoInfo->fecha_confirmacion_pago)->format('d/m/Y'),
                'periodo_fin' => $periodoFin,
                
                'precio_base' => number_format($base, 2, ',', '.') . ' €',
                'porcentaje_iva' => '21',
                'importe_iva' => number_format($iva, 2, ',', '.') . ' €',
                'total_pagado' => number_format($total, 2, ',', '.') . ' €',
                
                'referencia_pago' => $pagoInfo->referencia_pago
            ];

            $response = Http::withoutVerifying()
                ->withToken(config('services.pdfmonkey.api_key'))
                ->post('https://api.pdfmonkey.io/api/v1/documents', [
                    'document' => [
                        'document_template_id' => config('services.pdfmonkey.template_id_suscripciones'),
                        'status' => 'pending',
                        'payload' => $payload
                    ]
                ]);

            if ($response->successful()) {
                $docId = $response->json()['document']['id'];
                $downloadUrl = null;

                // Esperar a que se genere el documento (máximo 10 intentos)
                for ($i = 0; $i < 10; $i++) {
                    usleep(1500000); // 1.5s
                    $check = Http::withoutVerifying()
                        ->withToken(config('services.pdfmonkey.api_key'))
                        ->get("https://api.pdfmonkey.io/api/v1/documents/{$docId}");
                    
                    if ($check->successful() && !empty($check->json()['document']['download_url'])) {
                        $downloadUrl = $check->json()['document']['download_url'];
                        break;
                    }
                }

                if ($downloadUrl) {
                    $pdfContenido = Http::withoutVerifying()->get($downloadUrl)->body();
                    $nombreArchivo = 'factura_suscripcion_' . $idPago . '_' . time() . '.pdf';
                    $rutaCarpeta = public_path('facturas');
                    if (!File::exists($rutaCarpeta)) {
                        File::makeDirectory($rutaCarpeta, 0755, true);
                    }
                    file_put_contents($rutaCarpeta . '/' . $nombreArchivo, $pdfContenido);

                    DB::table('tbl_documento')->insert([
                        'id_usuario_fk' => $usuario->id_usuario,
                        'tipo_documento' => 'factura',
                        'tipo_entidad_documento' => 'pago',
                        'id_entidad_documento' => $idPago,
                        'nombre_documento' => 'Factura Suscripción #' . $idPago,
                        'url_documento' => 'facturas/' . $nombreArchivo,
                        'hash_documento' => $docId,
                        'creado_documento' => now(),
                        'actualizado_documento' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error Generando Factura de Suscripción: " . $e->getMessage());
        }
    }

    /**
     * Determina a dónde enviar al usuario después del pago o si ya está activo.
     */
    private function redirigirDashboard()
    {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        
        // Si es Arrendador
        if ($user->roles()->where('slug_rol', 'arrendador')->exists()) {
            // Si aún no tiene configurado el IBAN/Stripe Account, lo mandamos a configurar
            if (empty($user->stripe_account_id)) {
                return route('arrendador.stripe.configurar');
            }
            return '/arrendador/dashboard';
        }
        
        // Si es Miembro normal
        return '/miembro/inicio';
    }
}
