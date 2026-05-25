<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Suscripcion;
use App\Models\Plan;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class MiembroSuscripcionController extends Controller
{
    /**
     * Muestra el panel de suscripción con el plan pendiente de pago.
     */
    public function index()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();
<<<<<<< HEAD
        $usuarioModelo = Usuario::find($usuario->id_usuario);
        
        // Buscamos la suscripción pendiente o activa más reciente

        // Buscamos la suscripción pendiente más reciente
=======

        $usuarioModelo = Usuario::find($usuario->id_usuario);

        // Buscamos la suscripción pendiente o activa más reciente
>>>>>>> david
        $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->whereIn('estado_suscripcion', ['pendiente_pago', 'activa', 'cancelada'])
            ->latest('id_suscripcion')
            ->first();

        // Si la suscripción está activa/cancelada pero la fecha de fin ya pasó, la marcamos como caducada y generamos la nueva
        if ($suscripcion && in_array($suscripcion->estado_suscripcion, ['activa', 'cancelada']) && $suscripcion->fin_suscripcion && Carbon::parse($suscripcion->fin_suscripcion)->isPast()) {
            DB::beginTransaction();
            try {
                $suscripcion->update([
                    'estado_suscripcion' => 'caducada',
                    'actualizado_suscripcion' => now()
                ]);
                
                // Comprobar si hay una programada (downgrade diferido)
                $programada = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
                    ->where('estado_suscripcion', 'programada')
                    ->latest('id_suscripcion')
                    ->first();
                    
                if ($programada) {
                    $programada->update([
                        'estado_suscripcion' => 'pendiente_pago',
                        'inicio_suscripcion' => now(),
                        'fin_suscripcion' => now()->copy()->addMonth(),
                        'actualizado_suscripcion' => now()
                    ]);
                    $suscripcion = $programada;
                    
                    if ($usuarioModelo) {
                        $usuarioModelo->update(['stripe_status' => 'pending_payment']);
                    }
                } else {
                    if ((float)$suscripcion->precio_pagado_suscripcion <= 0) {
                        // Plan gratuito se autorrenueva (solo para miembros)
                        $suscripcion = Suscripcion::create([
                            'id_usuario_fk' => $suscripcion->id_usuario_fk,
                            'id_plan_fk' => $suscripcion->id_plan_fk,
                            'plan_suscripcion' => $suscripcion->plan_suscripcion,
                            'max_propiedades_suscripcion' => $suscripcion->max_propiedades_suscripcion,
                            'precio_pagado_suscripcion' => 0.00,
                            'estado_suscripcion' => 'activa',
                            'inicio_suscripcion' => now(),
                            'fin_suscripcion' => now()->copy()->addMonth(),
                            'creado_suscripcion' => now(),
                            'actualizado_suscripcion' => now(),
                        ]);
                        
                        if ($usuarioModelo) {
                            $usuarioModelo->update(['stripe_status' => 'active']);
                        }
                    } else {
                        // Suscripción de pago, crear una nueva en pendiente de pago
                        $suscripcion = Suscripcion::create([
                            'id_usuario_fk' => $suscripcion->id_usuario_fk,
                            'id_plan_fk' => $suscripcion->id_plan_fk,
                            'plan_suscripcion' => $suscripcion->plan_suscripcion,
                            'max_propiedades_suscripcion' => $suscripcion->max_propiedades_suscripcion,
                            'precio_pagado_suscripcion' => $suscripcion->precio_pagado_suscripcion,
                            'estado_suscripcion' => 'pendiente_pago',
                            'inicio_suscripcion' => now(),
                            'fin_suscripcion' => now()->copy()->addMonth(),
                            'creado_suscripcion' => now(),
                            'actualizado_suscripcion' => now(),
                        ]);
                        
                        if ($usuarioModelo) {
                            $usuarioModelo->update(['stripe_status' => 'expired']);
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error procesando expiración en index(): " . $e->getMessage());
            }
        }

        if (!$suscripcion) {
            if ($usuarioModelo && $usuarioModelo->stripe_status !== 'active') {
                $usuarioModelo->update([
                    'stripe_status' => 'active',
                ]);
            }

            return redirect($this->redirigirDashboard())
                ->with('info', 'No se encontró una suscripción asociada. Se ha permitido el acceso al panel.');
        }

        // Si la suscripción ya está activa o cancelada (dentro de plazo), sincronizamos y vamos al dashboard
        if (in_array($suscripcion->estado_suscripcion, ['activa', 'cancelada'])) {
            if ($suscripcion->estado_suscripcion === 'activa') {
                if ($usuarioModelo && $usuarioModelo->stripe_status !== 'active') {
                    $usuarioModelo->update([
                        'stripe_status' => 'active',
                    ]);
                }
            }

            return redirect($this->redirigirDashboard());
        }

        // Si Stripe ya está activo, también vamos al dashboard aunque la suscripción siga en pendiente
        if ($usuarioModelo && $usuarioModelo->stripe_status === 'active') {
            return redirect($this->redirigirDashboard());
        }

        // Valor por defecto para la ruta de retorno (puede sobrescribirse si se necesita)
        $rutaRetorno = url('/miembro/inicio');

        return view('miembro.suscripcion', compact('suscripcion', 'rutaRetorno'));
    }

    /**
     * Crea una sesión de Checkout de Stripe para el plan seleccionado.
     */
    public function checkout(Request $request)
    {
        /** @var Usuario $usuario */
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

        // Intentar obtener una referencia de pago válida desde la sesión o la suscripción
        $referenciaPago = $checkoutSession->payment_intent ?? null;
        if (!$referenciaPago && !empty($checkoutSession->subscription)) {
            try {
                $sub = \Stripe\Subscription::retrieve($checkoutSession->subscription, ['expand' => ['latest_invoice.payment_intent']]);
                if (!empty($sub->latest_invoice->payment_intent->id)) {
                    $referenciaPago = $sub->latest_invoice->payment_intent->id;
                } elseif (!empty($sub->latest_invoice->charge)) {
                    $referenciaPago = $sub->latest_invoice->charge;
                }
            } catch (\Exception $e) {
                Log::warning("No se pudo obtener referencia de Stripe: " . $e->getMessage());
            }
        }
        /** @var Usuario $usuario */
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
                    'fin_suscripcion' => Carbon::now()->copy()->addMonth(),
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
                    'referencia_pago' => $referenciaPago,
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

            $fechaPagoCarbon = Carbon::parse($pagoInfo->fecha_confirmacion_pago);
            $fechaPago = $fechaPagoCarbon->format('d/m/Y H:i');
            $inicioPeriodo = $fechaPagoCarbon->copy();
            $finPeriodo = $fechaPagoCarbon->copy()->addMonth();
            $periodoTexto = 'Mensual';

            $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)->latest('id_suscripcion')->first();

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
                'periodo_inicio' => $inicioPeriodo->format('d/m/Y'),
                'periodo_fin' => $finPeriodo->format('d/m/Y'),
                'periodo_facturacion' => $periodoTexto,

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
     * Permite a un miembro o inquilino cancelar su renovación y volver al plan gratuito.
     */
    public function downgrade(Request $request)
    {
        $usuario = Auth::user();
        
        // Solo para miembros/inquilinos (no arrendadores)
        if ($this->esArrendador($usuario)) {
            return back()->with('error', 'Los arrendadores no pueden acceder a un plan gratuito.');
        }

        $suscripcion = Suscripcion::where('id_usuario_fk', $usuario->id_usuario)
            ->where('estado_suscripcion', 'pendiente_pago')
            ->latest('id_suscripcion')
            ->first();

        if (!$suscripcion) {
            return redirect($this->redirigirDashboard())->with('info', 'No se encontró una suscripción pendiente que cancelar.');
        }

        // Buscar el plan gratuito (Miembro Estándar)
        $planGratis = Plan::where('slug_plan', 'miembro-estandar')->first();
        if (!$planGratis) {
            return back()->with('error', 'No se ha encontrado el plan gratuito en el sistema.');
        }

        // Actualizar la suscripción pendiente para que sea la gratuita y activarla
        $suscripcion->update([
            'plan_suscripcion' => $planGratis->nombre_plan,
            'id_plan_fk' => $planGratis->id_plan,
            'max_propiedades_suscripcion' => $planGratis->max_propiedades_plan,
            'precio_pagado_suscripcion' => $planGratis->precio_plan,
            'estado_suscripcion' => 'activa',
            'inicio_suscripcion' => Carbon::now(),
            'fin_suscripcion' => Carbon::now()->addMonth(),
            'actualizado_suscripcion' => now()
        ]);

        return redirect($this->redirigirDashboard())->with('success', 'Has vuelto al plan base gratuito con éxito.');
    }

    private function esArrendador($usuario): bool
    {
        if (!$usuario || empty($usuario->id_usuario)) {
            return false;
        }

        return DB::table('tbl_rol_usuario as ru')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('ru.id_usuario_fk', $usuario->id_usuario)
            ->where('r.slug_rol', 'arrendador')
            ->exists();
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
