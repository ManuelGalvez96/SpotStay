<?php

namespace App\Http\Controllers\inquilino;

use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use App\Models\Pago;
use App\Services\InquilinoFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class InquilinoPagoController extends Controller
{
    protected $financeService;

    public function __construct(InquilinoFinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function pagarCuotaAlquiler(int $id)
    {
        $usuario = Auth::user();
        if (!$usuario) return response()->json(['success' => false], 401);

        $tipoPago = request()->query('tipo', 'alquiler');

        try {
            $monto = 0;
            $nombreProducto = "SpotStay: " . ucfirst($tipoPago);
            $descripcion = "";
            $idAlquiler = null;
            $stripeAccountId = null;

            if ($tipoPago === 'alquiler') {
                $cuota = AlquilerCuota::findOrFail($id);
                $idAlquiler = $cuota->id_alquiler_fk;
                $resumen = $this->financeService->obtenerResumenPagoAlquiler($idAlquiler);
                $monto = ($resumen['total_deuda'] > 0) ? $resumen['total_deuda'] : $cuota->importe_base;
                $descripcion = "Mensualidad de alquiler";
            } elseif ($tipoPago === 'gasto') {
                $detalle = DB::table('tbl_gasto_cuota_detalle')
                    ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('id_gasto_cuota_detalle', $id)
                    ->select('tbl_gasto_cuota_detalle.*', 'tbl_gasto.concepto_gasto', 'tbl_gasto.id_propiedad_fk')
                    ->first();
                if (!$detalle) throw new \Exception("Gasto no encontrado.");
                $idAlquiler = $detalle->id_alquiler_fk;
                $monto = $detalle->importe_detalle;
                $nombreProducto = "Suministro: " . ($detalle->concepto_gasto ?? 'General');
                $descripcion = "Pago de suministros/gastos";
            } elseif ($tipoPago === 'incidencia') {
                $incidencia = DB::table('tbl_incidencia')->where('id_incidencia', $id)->first();
                if (!$incidencia) throw new \Exception("Incidencia no encontrada.");
                $idAlquiler = DB::table('tbl_alquiler')->where('id_propiedad_fk', $incidencia->id_propiedad_fk)->where('estado_alquiler', 'activo')->value('id_alquiler');
                $monto = $incidencia->presupuesto_importe_incidencia;
                $nombreProducto = "Reparación: " . $incidencia->titulo_incidencia;
                $descripcion = "Pago de presupuesto de incidencia";
            }

            // Obtener info del Arrendador para Stripe Connect
            $alquilerInfo = DB::table('tbl_alquiler')
                ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
                ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
                ->where('tbl_alquiler.id_alquiler', $idAlquiler)
                ->select('tbl_usuario.stripe_account_id', 'tbl_propiedad.calle_propiedad', 'tbl_propiedad.id_propiedad')
                ->first();

            if (!$alquilerInfo || empty($alquilerInfo->stripe_account_id)) {
                return response()->json(['success' => false, 'message' => 'El arrendador no ha configurado Stripe para recibir pagos.'], 400);
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $nombreProducto,
                            'description' => $alquilerInfo->calle_propiedad . " - " . $descripcion,
                        ],
                        'unit_amount' => (int)($monto * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('inquilino.pago.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('inquilino.historial_pagos'),
                'customer_email' => $usuario->email_usuario,
                'metadata' => [
                    'tipo_pago' => $tipoPago,
                    'id_referencia' => $id,
                    'id_alquiler' => $idAlquiler,
                    'id_propiedad' => $alquilerInfo->id_propiedad ?? '',
                    'id_usuario' => $usuario->id_usuario,
                    'pago_total' => ($tipoPago === 'alquiler') ? '1' : '0'
                ]
            ];

            if (!str_contains($alquilerInfo->stripe_account_id, 'acct_manual')) {
                $sessionData['payment_intent_data'] = [
                    'transfer_data' => ['destination' => $alquilerInfo->stripe_account_id],
                ];
            }

            $session = StripeSession::create($sessionData);
            return response()->json(['success' => true, 'url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pagarTodo(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);

        $propiedadId = $request->input('propiedad_id');
        $resumen = $this->financeService->obtenerResumenCompletoGastos($usuario->id_usuario, $propiedadId);

        if (empty($resumen['items'])) {
            return response()->json(['success' => false, 'message' => 'No tienes pagos pendientes.'], 400);
        }

        try {
            $monto = $resumen['total_pendiente'];
            $idsAlquiler = [];
            $idsGasto = [];
            $idsIncidencia = [];
            $idAlquiler = null;
            $idPropiedad = null;
            $stripeAccountId = null;

            foreach ($resumen['items'] as $item) {
                if ($item['tipo'] === 'alquiler') $idsAlquiler[] = $item['id'];
                elseif ($item['tipo'] === 'gasto') $idsGasto[] = $item['id'];
                elseif ($item['tipo'] === 'incidencia') $idsIncidencia[] = $item['id'];
                if (!$idPropiedad) $idPropiedad = $item['id_propiedad'];
            }

            $alquilerInfo = DB::table('tbl_alquiler')
                ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
                ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
                ->where('tbl_alquiler.id_propiedad_fk', $idPropiedad)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->select('tbl_alquiler.id_alquiler', 'tbl_usuario.stripe_account_id', 'tbl_propiedad.calle_propiedad')
                ->first();

            if (!$alquilerInfo || empty($alquilerInfo->stripe_account_id)) {
                return response()->json(['success' => false, 'message' => 'El arrendador no ha configurado Stripe.'], 400);
            }

            $idAlquiler = $alquilerInfo->id_alquiler;

            Stripe::setApiKey(config('services.stripe.secret'));
            $sessionData = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'SpotStay: Pago total de deudas',
                            'description' => $alquilerInfo->calle_propiedad . ' - Liquidación de todos los conceptos pendientes',
                        ],
                        'unit_amount' => (int) ($monto * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('inquilino.pago.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('inquilino.historial_pagos', $propiedadId ? ['propiedad_id' => $propiedadId] : []),
                'customer_email' => $usuario->email_usuario,
                'metadata' => [
                    'tipo_pago' => 'pago_total',
                    'id_usuario' => $usuario->id_usuario,
                    'id_alquiler' => $idAlquiler,
                    'id_propiedad' => $idPropiedad ?? '',
                    'ids_alquiler' => json_encode($idsAlquiler),
                    'ids_gasto' => json_encode($idsGasto),
                    'ids_incidencia' => json_encode($idsIncidencia),
                ]
            ];

            if (!str_contains($alquilerInfo->stripe_account_id, 'acct_manual')) {
                $sessionData['payment_intent_data'] = [
                    'transfer_data' => ['destination' => $alquilerInfo->stripe_account_id],
                ];
            }

            $session = StripeSession::create($sessionData);
            return response()->json(['success' => true, 'url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');
        if (!$sessionId) return redirect()->route('gestionar_propiedades');

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::retrieve($sessionId);
        $meta = $session->metadata;
        $ahora = now();

        DB::beginTransaction();
        try {
            if ($meta->tipo_pago === 'pago_total') {
                $idsAlquiler = json_decode($meta->ids_alquiler ?? '[]', true);
                $idsGasto = json_decode($meta->ids_gasto ?? '[]', true);
                $idsIncidencia = json_decode($meta->ids_incidencia ?? '[]', true);
                $itemsFactura = [];

                foreach ($idsAlquiler as $idAlq) {
                    AlquilerCuota::where('id_alquiler_cuota', $idAlq)->update(['estado' => 'pagado', 'pagado_en' => $ahora]);
                    $cuota = AlquilerCuota::find($idAlq);
                    Pago::create([
                        'id_pagador_fk' => $meta->id_usuario,
                        'id_alquiler_fk' => $meta->id_alquiler,
                        'id_alquiler_cuota_fk' => $idAlq,
                        'tipo_pago' => 'alquiler',
                        'concepto_pago' => 'Liquidación de deuda',
                        'importe_pago' => $cuota ? $cuota->importe_base : 0,
                        'estado_pago' => 'pagado',
                        'referencia_pago' => $session->payment_intent,
                        'fecha_confirmacion_pago' => $ahora,
                    ]);
                    if ($cuota) {
                        $mesCuota = Carbon::parse($cuota->mes_cuota)->translatedFormat('F Y');
                        $itemsFactura[] = [
                            'concepto_pago' => 'Cuota alquiler - ' . $mesCuota,
                            'importe_pago' => number_format((float)$cuota->importe_base, 2, ',', '.') . '€',
                            'mes_cuota' => $mesCuota,
                        ];
                    }
                }

                if (!empty($idsGasto)) {
                    $gastosPendientes = DB::table('tbl_gasto_cuota_detalle')
                        ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                        ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                        ->whereIn('id_gasto_cuota_detalle', $idsGasto)
                        ->select('tbl_gasto_cuota_detalle.*', 'tbl_gasto.concepto_gasto', 'tbl_gasto_cuota.mes_cuota')
                        ->get();
                    foreach ($gastosPendientes as $gasto) {
                        DB::table('tbl_gasto_cuota_detalle')
                            ->where('id_gasto_cuota_detalle', $gasto->id_gasto_cuota_detalle)
                            ->update(['estado_detalle' => 'pagado']);
                        Pago::create([
                            'id_pagador_fk' => $meta->id_usuario,
                            'id_alquiler_fk' => $gasto->id_alquiler_fk,
                            'id_gasto_cuota_detalle_fk' => $gasto->id_gasto_cuota_detalle,
                            'tipo_pago' => 'gasto',
                            'concepto_pago' => $gasto->concepto_gasto ?? 'Gasto de reparación',
                            'importe_pago' => $gasto->importe_detalle,
                            'estado_pago' => 'pagado',
                            'referencia_pago' => $session->payment_intent,
                            'fecha_confirmacion_pago' => $ahora,
                        ]);
                        $mesGasto = Carbon::parse($gasto->mes_cuota)->translatedFormat('F Y');
                        $itemsFactura[] = [
                            'concepto_pago' => 'Suministro: ' . ($gasto->concepto_gasto ?? 'General'),
                            'importe_pago' => number_format((float)$gasto->importe_detalle, 2, ',', '.') . '€',
                            'mes_cuota' => $mesGasto,
                        ];
                    }
                }

                foreach ($idsIncidencia as $idInc) {
                    $incidencia = DB::table('tbl_incidencia')->where('id_incidencia', $idInc)->first();
                    DB::table('tbl_incidencia')->where('id_incidencia', $idInc)->update(['estado_workflow' => 'pagado']);
                    Pago::create([
                        'id_pagador_fk' => $meta->id_usuario,
                        'id_alquiler_fk' => $meta->id_alquiler,
                        'tipo_pago' => 'incidencia',
                        'concepto_pago' => 'Pago reparación #' . $idInc,
                        'importe_pago' => $incidencia ? ($incidencia->presupuesto_importe_incidencia ?? 0) : 0,
                        'estado_pago' => 'pagado',
                        'referencia_pago' => $session->payment_intent,
                        'fecha_confirmacion_pago' => $ahora,
                    ]);
                    if ($incidencia) {
                        $itemsFactura[] = [
                            'concepto_pago' => 'Reparación: ' . $incidencia->titulo_incidencia,
                            'importe_pago' => number_format((float)($incidencia->presupuesto_importe_incidencia ?? 0), 2, ',', '.') . '€',
                            'mes_cuota' => null,
                        ];
                    }
                }

                // Create master Pago for the consolidated invoice
                $pagoMaestro = Pago::create([
                    'id_pagador_fk' => $meta->id_usuario,
                    'id_alquiler_fk' => $meta->id_alquiler,
                    'tipo_pago' => 'liquidacion',
                    'concepto_pago' => 'Liquidación total de deuda',
                    'importe_pago' => $session->amount_total / 100,
                    'estado_pago' => 'pagado',
                    'referencia_pago' => $session->payment_intent,
                    'fecha_confirmacion_pago' => $ahora,
                ]);

                $this->generarFacturaPDF($pagoMaestro->id_pago, $itemsFactura);
            } elseif ($meta->tipo_pago === 'alquiler') {
                if (($meta->pago_total ?? '0') === '1') {
                    AlquilerCuota::where('id_alquiler_fk', $meta->id_alquiler)->whereIn('estado', ['pendiente', 'atrasado'])->whereDate('mes_cuota', '<=', now()->startOfMonth())->update(['estado' => 'pagado', 'pagado_en' => $ahora]);
                } else {
                    AlquilerCuota::where('id_alquiler_cuota', $meta->id_referencia)->update(['estado' => 'pagado', 'pagado_en' => $ahora]);
                }

                $pago = Pago::create([
                    'id_pagador_fk' => $meta->id_usuario,
                    'id_alquiler_fk' => $meta->id_alquiler,
                    'id_alquiler_cuota_fk' => $meta->id_referencia,
                    'tipo_pago' => 'alquiler',
                    'concepto_pago' => ($meta->pago_total === '1') ? 'Liquidación de deuda' : 'Cuota alquiler',
                    'importe_pago' => $session->amount_total / 100,
                    'estado_pago' => 'pagado',
                    'referencia_pago' => $session->payment_intent,
                    'fecha_confirmacion_pago' => $ahora,
                ]);

                $this->generarFacturaPDF($pago->id_pago);
            } elseif ($meta->tipo_pago === 'gasto') {
                $this->procesarPagoGastos($meta->id_alquiler, $meta->id_usuario, $session);
            } elseif ($meta->tipo_pago === 'incidencia') {
                DB::table('tbl_incidencia')->where('id_incidencia', $meta->id_referencia)->update(['estado_workflow' => 'pagado']);
                Pago::create([
                    'id_pagador_fk' => $meta->id_usuario,
                    'id_alquiler_fk' => $meta->id_alquiler,
                    'tipo_pago' => 'incidencia',
                    'concepto_pago' => 'Pago reparación #' . $meta->id_referencia,
                    'importe_pago' => $session->amount_total / 100,
                    'estado_pago' => 'pagado',
                    'referencia_pago' => $session->payment_intent,
                    'fecha_confirmacion_pago' => $ahora,
                ]);
            }

            DB::commit();
            $idProp = $meta->id_propiedad ?? DB::table('tbl_alquiler')->where('id_alquiler', $meta->id_alquiler)->value('id_propiedad_fk');
            $params = $idProp ? ['propiedad_id' => $idProp] : [];
            return redirect()->route('inquilino.historial_pagos', $params)->with('success', 'Pago realizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('inquilino.historial_pagos')->with('error', $e->getMessage());
        }
    }

    private function procesarPagoGastos($idAlquiler, $idUsuario, $session)
    {
        $gastos = DB::table('tbl_gasto_cuota_detalle')
            ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
            ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
            ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', $idAlquiler)
            ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $idUsuario)
            ->whereIn('tbl_gasto_cuota_detalle.estado_detalle', ['pendiente', 'atrasado'])
            ->select('tbl_gasto_cuota_detalle.*', 'tbl_gasto.concepto_gasto')
            ->get();
        foreach ($gastos as $gasto) {
            DB::table('tbl_gasto_cuota_detalle')->where('id_gasto_cuota_detalle', $gasto->id_gasto_cuota_detalle)->update(['estado_detalle' => 'pagado']);
            Pago::create([
                'id_pagador_fk' => $idUsuario,
                'id_alquiler_fk' => $idAlquiler,
                'id_gasto_cuota_detalle_fk' => $gasto->id_gasto_cuota_detalle,
                'tipo_pago' => 'gasto',
                'concepto_pago' => $gasto->concepto_gasto ?? 'Gasto de suministro',
                'importe_pago' => $gasto->importe_detalle,
                'estado_pago' => 'pagado',
                'referencia_pago' => $session->payment_intent,
                'fecha_confirmacion_pago' => now(),
            ]);
        }
    }

    private function generarFacturaPDF($idPago, $items = null)
    {
        try {
            $pagoInfo = DB::table('tbl_pago')
                ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'tbl_pago.id_pagador_fk')
                ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_pago.id_alquiler_fk')
                ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
                ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
                ->where('tbl_pago.id_pago', $idPago)
                ->select(
                    'tbl_pago.*',
                    'inquilino.nombre_usuario as nombre_inquilino',
                    'inquilino.dni_usuario as dni_inquilino',
                    'inquilino.email_usuario as email_inquilino',
                    'arrendador.nombre_usuario as nombre_arrendador',
                    'arrendador.dni_usuario as dni_arrendador',
                    'arrendador.email_usuario as email_arrendador',
                    'arrendador.iban_usuario as iban_arrendador',
                    'tbl_propiedad.*'
                )
                ->first();

            if (!$pagoInfo) return;

            $direccionCompleta = "{$pagoInfo->calle_propiedad}, {$pagoInfo->numero_propiedad}";
            if (!empty($pagoInfo->piso_propiedad)) $direccionCompleta .= ", Piso {$pagoInfo->piso_propiedad}";
            if (!empty($pagoInfo->puerta_propiedad)) $direccionCompleta .= ", Puerta {$pagoInfo->puerta_propiedad}";
            $direccionCompleta .= ". {$pagoInfo->ciudad_propiedad}";
            $fechaPago = Carbon::parse($pagoInfo->fecha_confirmacion_pago ?? $pagoInfo->creado_pago ?? now())->format('d/m/Y H:i');

            // Build items array for the template's {{#items}} block
            if ($items === null) {
                $importeFormateado = number_format((float) $pagoInfo->importe_pago, 2, ',', '.') . '€';
                $items = [[
                    'concepto_pago' => $pagoInfo->concepto_pago,
                    'importe_pago' => $importeFormateado,
                    'mes_cuota' => null,
                ]];
            }

            $items = $this->normalizarItemsFactura($items, (string) $pagoInfo->concepto_pago);

            $response = Http::withoutVerifying()
                ->withToken(config('services.pdfmonkey.api_key'))
                ->post('https://api.pdfmonkey.io/api/v1/documents', [
                    'document' => [
                        'document_template_id' => config('services.pdfmonkey.template_id'),
                        'status' => 'pending',
                        'payload' => [
                            'id_pago' => str_pad((string) $idPago, 6, '0', STR_PAD_LEFT),
                            'creado_pago' => $fechaPago,
                            'importe_pago' => number_format((float) $pagoInfo->importe_pago, 2, ',', '.') . '€',
                            'items' => $items,

                            'nombre_arrendador' => $pagoInfo->nombre_arrendador,
                            'dni_arrendador' => $pagoInfo->dni_arrendador,
                            'email_arrendador' => $pagoInfo->email_arrendador,
                            'iban_arrendador' => $pagoInfo->iban_arrendador ?? 'N/A',

                            'nombre_inquilino' => $pagoInfo->nombre_inquilino,
                            'dni_inquilino' => $pagoInfo->dni_inquilino,
                            'email_inquilino' => $pagoInfo->email_inquilino,
                            'calle_propiedad' => $direccionCompleta,

                            'concepto_pago' => $items[0]['concepto_pago'] ?? $pagoInfo->concepto_pago,
                            'referencia_pago' => $pagoInfo->referencia_pago
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $docId = $response->json()['document']['id'];
                $downloadUrl = null;

                for ($i = 0; $i < 10; $i++) {
                    usleep(1500000);
                    $check = Http::withoutVerifying()->withToken(config('services.pdfmonkey.api_key'))->get("https://api.pdfmonkey.io/api/v1/documents/{$docId}");
                    if ($check->successful() && !empty($check->json()['document']['download_url'])) {
                        $downloadUrl = $check->json()['document']['download_url'];
                        break;
                    }
                }

                if ($downloadUrl) {
                    $pdfContenido = Http::withoutVerifying()->get($downloadUrl)->body();
                    $nombreArchivo = 'factura_' . $idPago . '_' . time() . '.pdf';
                    $rutaCarpeta = public_path('facturas');
                    if (!File::exists($rutaCarpeta)) {
                        File::makeDirectory($rutaCarpeta, 0755, true);
                    }
                    file_put_contents($rutaCarpeta . '/' . $nombreArchivo, $pdfContenido);

                    DB::table('tbl_documento')->insert([
                        'id_usuario_fk' => $pagoInfo->id_pagador_fk,
                        'tipo_documento' => 'factura',
                        'tipo_entidad_documento' => 'pago',
                        'id_entidad_documento' => $idPago,
                        'nombre_documento' => 'Factura SpotStay #' . $idPago,
                        'url_documento' => 'facturas/' . $nombreArchivo,
                        'hash_documento' => $docId,
                        'creado_documento' => now(),
                        'actualizado_documento' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error Generando Factura: " . $e->getMessage());
        }
    }

    private function normalizarItemsFactura(array $items, string $conceptoPorDefecto): array
    {
        $itemsAgrupados = [];

        foreach ($items as $item) {
            $concepto = trim((string) ($item['concepto_pago'] ?? $conceptoPorDefecto));
            $mesCuota = $item['mes_cuota'] ?? null;
            $mesCuota = $mesCuota !== null && $mesCuota !== '' ? (string) $mesCuota : null;
            $importe = $this->normalizarImporteFactura($item['importe_pago'] ?? 0);
            $claveAgrupacion = $concepto . '|' . ($mesCuota ?? '');

            if (!isset($itemsAgrupados[$claveAgrupacion])) {
                $itemsAgrupados[$claveAgrupacion] = [
                    'concepto_pago' => $concepto,
                    'importe_numero' => 0,
                    'mes_cuota' => $mesCuota,
                ];
            }

            $itemsAgrupados[$claveAgrupacion]['importe_numero'] += $importe;
        }

        return array_map(function (array $item) {
            return [
                'concepto_pago' => $item['concepto_pago'],
                'importe_pago' => number_format((float) $item['importe_numero'], 2, ',', '.') . '€',
                'mes_cuota' => $item['mes_cuota'],
            ];
        }, array_values($itemsAgrupados));
    }

    private function normalizarImporteFactura(mixed $importe): float
    {
        if (is_numeric($importe)) {
            return (float) $importe;
        }

        $valor = (string) $importe;
        $valor = str_replace(['€', ' '], '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return (float) $valor;
    }

    public function historialPagos(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $query = DB::table('tbl_pago')
            ->leftJoin('tbl_documento', function($join) {
                $join->on('tbl_documento.id_entidad_documento', '=', 'tbl_pago.id_pago')
                     ->where('tbl_documento.tipo_entidad_documento', '=', 'pago')
                     ->where('tbl_documento.tipo_documento', '=', 'factura');
            })
            ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_pago.id_alquiler_fk')
            ->leftJoin('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_pago.id_pagador_fk', $usuario->id_usuario)
            ->whereNotNull('tbl_documento.url_documento')
            ->select(
                'tbl_pago.*',
                'tbl_documento.url_documento as factura_url',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.calle_propiedad'
            )
            ->orderBy('tbl_pago.fecha_confirmacion_pago', 'desc');

        // Filtros
        if ($request->filled('desde')) {
            $query->whereDate('tbl_pago.fecha_confirmacion_pago', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('tbl_pago.fecha_confirmacion_pago', '<=', $request->hasta);
        }
        if ($request->filled('tipo')) {
            $query->where('tbl_pago.tipo_pago', $request->tipo);
        }
        if ($request->filled('referencia')) {
            $query->where('tbl_pago.referencia_pago', 'like', '%' . $request->referencia . '%');
        }
        if ($request->filled('propiedad_id')) {
            $query->whereIn('tbl_pago.id_alquiler_fk', function ($q) use ($request) {
                $q->select('id_alquiler')->from('tbl_alquiler')
                  ->where('id_propiedad_fk', $request->propiedad_id);
            });
        }

        $pagos = $query->paginate(15)->withQueryString();

        return view('inquilino.historial_pagos', compact('pagos'));
    }

    public function obtenerHistorialSuministros($id)
    {
        return response()->json(DB::table('tbl_pago')->where('id_alquiler_fk', $id)->where('id_pagador_fk', Auth::id())->where('tipo_pago', 'gasto')->orderBy('creado_pago', 'desc')->get());
    }

    public function obtenerHistorialAlquiler($id)
    {
        return response()->json(DB::table('tbl_pago')->leftJoin('tbl_documento', function ($join) {
            $join->on('tbl_documento.id_entidad_documento', '=', 'tbl_pago.id_pago')->where('tbl_documento.tipo_entidad_documento', '=', 'pago')->where('tbl_documento.tipo_documento', '=', 'factura');
        })->where('tbl_pago.id_alquiler_fk', $id)->where('tbl_pago.id_pagador_fk', Auth::id())->where('tbl_pago.tipo_pago', 'alquiler')->select('tbl_pago.*', 'tbl_documento.url_documento as factura_url')->orderBy('tbl_pago.creado_pago', 'desc')->get());
    }

    public function verificarPagosConPdf(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $pagos = DB::table('tbl_pago')
            ->leftJoin('tbl_documento', function($join) {
                $join->on('tbl_documento.id_entidad_documento', '=', 'tbl_pago.id_pago')
                     ->where('tbl_documento.tipo_entidad_documento', '=', 'pago')
                     ->where('tbl_documento.tipo_documento', '=', 'factura');
            })
            ->where('tbl_pago.id_pagador_fk', $usuario->id_usuario)
            ->where(function($query) {
                $query->whereNotNull('tbl_documento.url_documento')
                      ->orWhere('tbl_pago.tipo_pago', 'suscripcion');
            })
            ->when($request->filled('propiedad_id'), function ($query) use ($request) {
                $query->whereIn('tbl_pago.id_alquiler_fk', function ($subquery) use ($request) {
                    $subquery->select('id_alquiler')
                        ->from('tbl_alquiler')
                        ->where('id_propiedad_fk', $request->propiedad_id);
                });
            })
            ->select(
                'tbl_pago.id_pago',
                'tbl_pago.referencia_pago',
                'tbl_pago.importe_pago',
                'tbl_pago.tipo_pago',
                'tbl_pago.fecha_confirmacion_pago',
                'tbl_pago.creado_pago',
                'tbl_documento.url_documento as factura_url'
            )
            ->orderBy('tbl_pago.fecha_confirmacion_pago', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $pagos->count(),
            'data' => $pagos
        ]);
    }
}
