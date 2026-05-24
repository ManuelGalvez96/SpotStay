<?php

namespace App\Http\Controllers\inquilino;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class InquilinoIncidenciaController extends Controller
{
    public function reportarIncidencia(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario) return redirect()->route('login');

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria' => 'required|string',
            'prioridad' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $propiedad = DB::table('tbl_propiedad')->where('id_propiedad', $id)->first();
            $idAsignado = ($propiedad->id_gestor_fk ?? 0) > 0 ? $propiedad->id_gestor_fk : ($propiedad->id_arrendador_fk ?? null);

            $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_reporta_fk' => $usuario->id_usuario,
                'id_asignado_fk' => $idAsignado,
                'titulo_incidencia' => $request->titulo,
                'descripcion_incidencia' => $request->descripcion,
                'categoria_incidencia' => $request->categoria,
                'prioridad_incidencia' => $request->prioridad,
                'estado_incidencia' => 'abierta',
                'creado_incidencia' => Carbon::now(),
                'actualizado_incidencia' => Carbon::now()
            ]);

            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $idIncidencia,
                'id_usuario_fk' => $usuario->id_usuario,
                'comentario_historial' => 'Incidencia reportada por el inquilino/propietario.',
                'cambio_estado_historial' => 'abierta',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

            DB::commit();
            return $request->ajax() ? response()->json(['success' => true]) : redirect()->back()->with('success', 'Incidencia reportada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $request->ajax() ? response()->json(['success' => false, 'message' => $e->getMessage()], 500) : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getIncidencias(Request $request, $id)
    {
        $estado = $request->query('estado', 'todas');
        $autor = $request->query('autor', 'todas');

        $query = DB::table('tbl_incidencia')->where('id_propiedad_fk', $id);
        if ($estado !== 'todas') $query->where('estado_incidencia', $estado);
        if ($autor === 'mias') $query->where('id_reporta_fk', Auth::id());

        $incidencias = $query->orderBy('creado_incidencia', 'desc')->get();

        return response()->json($incidencias->map(function ($inc) {
            return [
                'id' => $inc->id_incidencia,
                'titulo' => $inc->titulo_incidencia,
                'fecha' => Carbon::parse($inc->creado_incidencia)->format('d/m/Y'),
                'estado' => $inc->estado_incidencia,
                'estado_texto' => ucfirst(str_replace('_', ' ', $inc->estado_incidencia)),
                'id_reporta' => $inc->id_reporta_fk,
                'auth_id' => Auth::id()
            ];
        }));
    }

    public function getDetalleIncidencia($id)
    {
        $incidencia = DB::table('tbl_incidencia')->where('id_incidencia', $id)->first();
        if (!$incidencia) return response()->json(['error' => 'No encontrada'], 404);

        return response()->json([
            'id' => $incidencia->id_incidencia,
            'titulo' => $incidencia->titulo_incidencia,
            'descripcion' => $incidencia->descripcion_incidencia,
            'categoria' => ucfirst(str_replace('_', ' ', $incidencia->categoria_incidencia ?? 'N/A')),
            'prioridad' => ucfirst($incidencia->prioridad_incidencia ?? 'N/A'),
            'estado' => ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia ?? 'N/A')),
            'estado_workflow' => $incidencia->estado_incidencia,
            'presupuesto' => $incidencia->presupuesto_importe_incidencia,
            'fecha' => Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y H:i')
        ]);
    }

    public function obtenerEstadosIncidencias()
    {
        try {
            $columna = DB::select("SHOW COLUMNS FROM tbl_incidencia LIKE 'estado_incidencia'")[0]->Type;
            preg_match('/^enum\((.*)\)$/', $columna, $matches);
            $valoresEnum = explode(',', $matches[1]);
            $estados = [];
            foreach ($valoresEnum as $valor) {
                $val = trim($valor, "'");
                $estados[$val] = mb_convert_case(str_replace('_', ' ', $val), MB_CASE_TITLE, "UTF-8");
            }
            return response()->json(['success' => true, 'estados' => $estados]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'estados' => [
                'abierta' => 'Abierta',
                'solucionada' => 'Solucionada',
                'resuelta' => 'Resuelta',
            ]]);
        }
    }

    public function cerrarIncidencia($id)
    {
        $incidencia = DB::table('tbl_incidencia')->where('id_incidencia', $id)->first();
        if (!$incidencia || $incidencia->id_reporta_fk != Auth::id()) return back()->with('error', 'No permitido.');

        if ($incidencia->estado_incidencia !== 'solucionada') {
            return back()->with('error', 'Solo puedes marcar como resuelta una incidencia solucionada.');
        }

        DB::table('tbl_incidencia')->where('id_incidencia', $id)->update([
            'estado_incidencia' => 'resuelta',
            'resuelto_incidencia' => now(),
            'actualizado_incidencia' => now(),
        ]);
        return request()->ajax() ? response()->json(['success' => true]) : back()->with('success', 'Incidencia cerrada.');
    }

    public function decidirPagoIncidencia(Request $request, $id)
    {
        $request->validate(['responsable' => 'required|in:inquilino,propietario']);
        DB::table('tbl_incidencia')->where('id_incidencia', $id)->update([
            'responsable_pago' => $request->responsable,
            'estado_incidencia' => ($request->responsable === 'inquilino') ? 'esperando_pago' : 'esperando_decision',
            'actualizado_incidencia' => now()
        ]);
        return response()->json(['success' => true]);
    }

    public function pagarPresupuestoIncidencia($id)
    {
        $usuario = Auth::user();
        $incidencia = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->where('id_incidencia', $id)
            ->select('tbl_incidencia.*', 'tbl_usuario.stripe_account_id', 'tbl_usuario.nombre_usuario', 'tbl_propiedad.calle_propiedad', 'tbl_propiedad.id_propiedad')
            ->first();

        if (!$incidencia || !$incidencia->presupuesto_incidencia) return response()->json(['success' => false, 'message' => 'Sin presupuesto.'], 404);

        $idAlquiler = DB::table('tbl_alquiler')->where('id_propiedad_fk', $incidencia->id_propiedad)->where('estado_alquiler', 'activo')->value('id_alquiler');

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => "Reparación: " . $incidencia->titulo_incidencia, 'description' => $incidencia->calle_propiedad],
                        'unit_amount' => (int)($incidencia->presupuesto_incidencia * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('inquilino.pago.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('inquilino.ver_propiedad', $incidencia->id_propiedad),
                'customer_email' => $usuario->email_usuario,
                'metadata' => ['tipo_pago' => 'incidencia', 'id_referencia' => $id, 'id_usuario' => $usuario->id_usuario, 'id_alquiler' => $idAlquiler]
            ]);
            return response()->json(['success' => true, 'url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
