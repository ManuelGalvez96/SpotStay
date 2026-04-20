<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->select(
                'tbl_incidencia.id_incidencia',
                'tbl_incidencia.titulo_incidencia',
                'tbl_incidencia.estado_incidencia',
                'tbl_incidencia.prioridad_incidencia',
                'tbl_incidencia.creado_incidencia',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'tbl_propiedad.ciudad_propiedad'
            );

        $titulo = trim((string) $request->query('titulo', ''));
        $propiedad = trim((string) $request->query('propiedad', ''));
        $propiedadId = (int) $request->query('propiedad_id', 0);
        $estado = (string) $request->query('estado', '');
        $prioridad = (string) $request->query('prioridad', '');
        $fecha = (string) $request->query('fecha', '');

        if ($propiedadId > 0) {
            $query->where('tbl_propiedad.id_propiedad', $propiedadId);
        }

        if ($titulo !== '') {
            $query->where('tbl_incidencia.titulo_incidencia', 'like', '%' . $titulo . '%');
        }

        if ($propiedad !== '') {
            $query->where(function ($sub) use ($propiedad) {
                $sub->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $propiedad . '%')
                    ->orWhereRaw("CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad, tbl_propiedad.piso_propiedad, tbl_propiedad.puerta_propiedad) like ?", ['%' . $propiedad . '%'])
                    ->orWhere('tbl_propiedad.ciudad_propiedad', 'like', '%' . $propiedad . '%');
            });
        }

        if (in_array($estado, ['abierta', 'en_proceso', 'esperando', 'resuelta'], true)) {
            $query->where('tbl_incidencia.estado_incidencia', $estado);
        }

        if (in_array($prioridad, ['alta', 'media', 'baja', 'urgente'], true)) {
            if ($prioridad === 'alta') {
                $query->whereIn('tbl_incidencia.prioridad_incidencia', ['alta', 'urgente']);
            } else {
                $query->where('tbl_incidencia.prioridad_incidencia', $prioridad);
            }
        }

        if ($fecha !== '') {
            $query->whereDate('tbl_incidencia.creado_incidencia', $fecha);
        }

        $incidencias = $query
            ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('gestor.incidencias', compact('incidencias', 'titulo', 'propiedad', 'propiedadId', 'estado', 'prioridad', 'fecha'));
    }

    public function show(int $id)
    {
        $incidencia = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario as reporta', 'reporta.id_usuario', '=', 'tbl_incidencia.id_reporta_fk')
            ->leftJoin('tbl_usuario as asignado', 'asignado.id_usuario', '=', 'tbl_incidencia.id_asignado_fk')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->where('tbl_incidencia.id_incidencia', $id)
            ->select(
                'tbl_incidencia.*',
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'tbl_propiedad.ciudad_propiedad',
                'reporta.nombre_usuario as nombre_reporta',
                'reporta.email_usuario as email_reporta',
                'asignado.nombre_usuario as nombre_asignado',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_arrendador'
            )
            ->first();

        if (!$incidencia) {
            abort(404);
        }

        $historial = DB::table('tbl_historial_incidencia')
            ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_historial_incidencia.id_usuario_fk')
            ->where('tbl_historial_incidencia.id_incidencia_fk', $id)
            ->select(
                'tbl_historial_incidencia.*',
                'tbl_usuario.nombre_usuario'
            )
            ->orderBy('tbl_historial_incidencia.creado_historial', 'asc')
            ->get();

        $documentos = DB::table('tbl_documento')
            ->where('tipo_entidad_documento', 'incidencia')
            ->where('id_entidad_documento', $id)
            ->orderBy('creado_documento', 'desc')
            ->get();

        $tieneComunicacion = DB::table('tbl_historial_incidencia')
            ->where('id_incidencia_fk', $id)
            ->where('comentario_historial', 'like', 'Comunicación a %')
            ->exists();

        $tienePresupuesto = $documentos->contains(function ($doc) {
            return $doc->tipo_documento === 'presupuesto_incidencia';
        });

        $tieneAdjunto = $documentos->contains(function ($doc) {
            return $doc->tipo_documento === 'incidencia_adjunto';
        });

        $tieneIntervencion = DB::table('tbl_historial_incidencia')
            ->where('id_incidencia_fk', $id)
            ->whereNull('cambio_estado_historial')
            ->where('comentario_historial', 'not like', 'Comunicación a %')
            ->where('comentario_historial', 'not like', 'Documento adjunto subido:%')
            ->exists();

        $tiposEspera = [
            'arrendador' => 'Esperando arrendador',
            'empresa' => 'Esperando empresa',
            'inquilino' => 'Esperando inquilino',
        ];

        $transiciones = [
            'abierta' => ['en_proceso'],
            'en_proceso' => ['esperando', 'resuelta'],
            'esperando' => ['en_proceso'],
            'resuelta' => ['en_proceso'],
        ];

        $siguientesEstados = $transiciones[$incidencia->estado_incidencia] ?? [];

        $pasosFlujo = [
            ['clave' => 'iniciar', 'titulo' => 'Iniciar gestión', 'completado' => $incidencia->estado_incidencia !== 'abierta'],
            ['clave' => 'comunicacion', 'titulo' => 'Registrar comunicación', 'completado' => $tieneComunicacion],
            ['clave' => 'presupuesto', 'titulo' => 'Generar presupuesto', 'completado' => $tienePresupuesto],
            ['clave' => 'documento', 'titulo' => 'Subir documentación', 'completado' => $tieneAdjunto],
            ['clave' => 'intervencion', 'titulo' => 'Registrar intervención', 'completado' => $tieneIntervencion],
            ['clave' => 'cierre', 'titulo' => 'Cerrar o dejar en espera', 'completado' => in_array($incidencia->estado_incidencia, ['esperando', 'resuelta'], true)],
        ];

        $accionActual = 'iniciar';

        if ($incidencia->estado_incidencia === 'esperando') {
            $accionActual = $incidencia->esperando_de_incidencia === 'arrendador'
                ? 'esperando_arrendador'
                : 'reanudar';
        } elseif ($incidencia->estado_incidencia === 'resuelta') {
            $accionActual = 'reabrir';
        } elseif ($incidencia->estado_incidencia !== 'abierta') {
            if (!$tieneComunicacion) {
                $accionActual = 'comunicacion';
            } elseif (!$tienePresupuesto) {
                $accionActual = 'presupuesto';
            } elseif (!$tieneAdjunto) {
                $accionActual = 'documento';
            } elseif (!$tieneIntervencion) {
                $accionActual = 'intervencion';
            } else {
                $accionActual = 'cierre';
            }
        }

        return view('gestor.incidencia', compact(
            'incidencia',
            'historial',
            'documentos',
            'tiposEspera',
            'siguientesEstados',
            'pasosFlujo',
            'accionActual'
        ));
    }

    public function iniciarGestion(int $id): RedirectResponse
    {
        return $this->actualizarEstado(
            $id,
            'en_proceso',
            'Gestor inicia la gestión de la incidencia.',
            null,
            true
        );
    }

    public function cambiarEstado(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'estado' => 'required|in:abierta,en_proceso,esperando,resuelta',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return redirect()->back()->with('error', 'Incidencia no encontrada.');
        }

        $transiciones = [
            'abierta' => ['en_proceso'],
            'en_proceso' => ['esperando', 'resuelta'],
            'esperando' => ['en_proceso'],
            'resuelta' => ['en_proceso'],
        ];

        $estadoNuevo = $request->estado;
        $permitidos = $transiciones[$incidencia->estado_incidencia] ?? [];

        if (!in_array($estadoNuevo, $permitidos, true)) {
            return redirect()->back()->with('error', 'Transición de estado no permitida.');
        }

        $mensaje = $request->comentario ?: ('Estado actualizado a ' . str_replace('_', ' ', $estadoNuevo) . '.');

        return $this->actualizarEstado($id, $estadoNuevo, $mensaje, null, false);
    }

    public function marcarEspera(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'esperando_de' => 'required|in:arrendador,empresa,inquilino',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $razon = $request->esperando_de;
        $mensaje = $request->comentario ?: ('Incidencia en espera: pendiente de ' . $razon . '.');

        return $this->actualizarEstado($id, 'esperando', $mensaje, $razon, false);
    }

    public function registrarIntervencion(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'comentario_intervencion' => 'required|string|max:1000',
        ]);

        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return redirect()->back()->with('error', 'Incidencia no encontrada.');
        }

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $this->obtenerIdGestor(),
            'comentario_historial' => $request->comentario_intervencion,
            'cambio_estado_historial' => null,
            'creado_historial' => Carbon::now(),
            'actualizado_historial' => Carbon::now(),
        ]);

        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update([
                'actualizado_incidencia' => Carbon::now(),
            ]);

        return redirect()->back()->with('ok', 'Intervención registrada correctamente.');
    }

    public function registrarComunicacion(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'destinatario' => 'required|in:arrendador,empresa,inquilino',
            'mensaje' => 'required|string|max:1000',
        ]);

        $comentario = 'Comunicación a ' . $request->destinatario . ': ' . $request->mensaje;

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $this->obtenerIdGestor(),
            'comentario_historial' => $comentario,
            'cambio_estado_historial' => null,
            'creado_historial' => Carbon::now(),
            'actualizado_historial' => Carbon::now(),
        ]);

        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update([
                'actualizado_incidencia' => Carbon::now(),
            ]);

        return redirect()->back()->with('ok', 'Comunicación registrada en historial.');
    }

    public function subirDocumento(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'descripcion' => 'nullable|string|max:150',
        ]);

        $archivo = $request->file('archivo');
        $nombreBase = $request->descripcion ?: ('Adjunto incidencia #' . $id);
        $ruta = $archivo->store('incidencias', 'public');

        DB::table('tbl_documento')->insert([
            'id_usuario_fk' => $this->obtenerIdGestor(),
            'tipo_documento' => 'incidencia_adjunto',
            'tipo_entidad_documento' => 'incidencia',
            'id_entidad_documento' => $id,
            'nombre_documento' => $nombreBase,
            'url_documento' => Storage::url($ruta),
            'hash_documento' => hash_file('sha256', $archivo->getRealPath()),
            'pdfmonkey_id_documento' => null,
            'creado_documento' => Carbon::now(),
            'actualizado_documento' => Carbon::now(),
        ]);

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $this->obtenerIdGestor(),
            'comentario_historial' => 'Documento adjunto subido: ' . $nombreBase,
            'cambio_estado_historial' => null,
            'creado_historial' => Carbon::now(),
            'actualizado_historial' => Carbon::now(),
        ]);

        return redirect()->back()->with('ok', 'Documento subido correctamente.');
    }

    public function crearPresupuesto(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'importe' => 'required|numeric|min:0',
            'detalle_presupuesto' => 'required|string|max:1000',
            'pdf_presupuesto' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $nombrePresupuesto = 'Presupuesto incidencia #' . $id . ' - ' . number_format((float) $request->importe, 2, ',', '.') . ' EUR';
        $urlDocumento = null;
        $hashDocumento = hash('sha256', $nombrePresupuesto . '|' . $request->detalle_presupuesto . '|' . Carbon::now()->timestamp);

        if ($request->hasFile('pdf_presupuesto')) {
            $pdf = $request->file('pdf_presupuesto');
            $ruta = $pdf->store('presupuestos-incidencia', 'public');
            $urlDocumento = Storage::url($ruta);
            $hashDocumento = hash_file('sha256', $pdf->getRealPath());
        }

        DB::table('tbl_documento')->insert([
            'id_usuario_fk' => $this->obtenerIdGestor(),
            'tipo_documento' => 'presupuesto_incidencia',
            'tipo_entidad_documento' => 'incidencia',
            'id_entidad_documento' => $id,
            'nombre_documento' => $nombrePresupuesto,
            'url_documento' => $urlDocumento ?: 'sin-archivo',
            'hash_documento' => $hashDocumento,
            'pdfmonkey_id_documento' => null,
            'creado_documento' => Carbon::now(),
            'actualizado_documento' => Carbon::now(),
        ]);

        $comentario = 'Presupuesto generado (' . number_format((float) $request->importe, 2, ',', '.') . ' EUR): ' . $request->detalle_presupuesto;

        return $this->actualizarEstado($id, 'esperando', $comentario, 'arrendador', false);
    }

    private function actualizarEstado(
        int $id,
        string $estado,
        string $comentario,
        ?string $esperandoDe,
        bool $asignarSiNoTiene
    ): RedirectResponse {
        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return redirect()->back()->with('error', 'Incidencia no encontrada.');
        }

        $idGestor = $this->obtenerIdGestor();

        $dataUpdate = [
            'estado_incidencia' => $estado,
            'esperando_de_incidencia' => $estado === 'esperando' ? $esperandoDe : null,
            'actualizado_incidencia' => Carbon::now(),
            'resuelto_incidencia' => $estado === 'resuelta' ? Carbon::now() : null,
        ];

        if ($asignarSiNoTiene && empty($incidencia->id_asignado_fk)) {
            $dataUpdate['id_asignado_fk'] = $idGestor;
        }

        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update($dataUpdate);

        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'id_usuario_fk' => $idGestor,
            'comentario_historial' => $comentario,
            'cambio_estado_historial' => $estado,
            'creado_historial' => Carbon::now(),
            'actualizado_historial' => Carbon::now(),
        ]);

        return redirect()->back()->with('ok', 'Incidencia actualizada correctamente.');
    }

    private function obtenerIdGestor(): int
    {
        $gestorId = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_rol_usuario.id_usuario_fk', '=', 'tbl_usuario.id_usuario')
            ->join('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol', 'gestor')
            ->orderBy('tbl_usuario.id_usuario')
            ->value('tbl_usuario.id_usuario');

        return (int) ($gestorId ?: 1);
    }
}
