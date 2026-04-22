<?php

namespace App\Http\Controllers\inquilino;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InquilinoController extends Controller
{
    public function gestionarPropiedades(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');

        // ID del usuario autenticado
        $userId = $usuario->id_usuario;

        // --- CONTROL DE ACCESO ---
        $alquileresActivosInquilino = DB::table('tbl_alquiler')
            ->where('id_inquilino_fk', $userId)
            ->where('estado_alquiler', 'activo')
            ->exists();

        $alquileresActivosPropietario = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_propiedad.id_arrendador_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->exists();

        if (!$alquileresActivosInquilino && !$alquileresActivosPropietario) {
            $urlRedirect = '/login';
            if ($usuario->roles()->where('slug_rol', 'admin')->exists()) {
                $urlRedirect = '/admin/dashboard';
            } elseif ($usuario->roles()->whereIn('slug_rol', ['miembro', 'inquilino', 'propietario'])->exists()) {
                $urlRedirect = '/miembro/inicio';
            }
            return redirect($urlRedirect)->with('error', 'Acceso restringido: <br>Solo inquilinos o propietarios con alquileres activos pueden acceder a esta sección.');
        }

        // Lógica de usuario consistente con Miembro
        $nombreUsuario = $usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '';
        $tieneFoto = !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';

        // 1. Contratos Activos (Total general para KPIs, no se filtra)
        $totalContratos = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($query) use ($userId) {
                $query->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->count(DB::raw('DISTINCT tbl_propiedad.id_propiedad'));

        // 2. Días para el próximo pago
        $proximoPago = DB::table('tbl_pago')
            ->where('id_pagador_fk', $userId)
            ->where('estado_pago', 'pendiente')
            ->orderBy('mes_pago', 'asc')
            ->first();

        if ($proximoPago && $proximoPago->mes_pago) {
            $fechaPago = Carbon::parse($proximoPago->mes_pago)->day(1);
            $diasParaPago = Carbon::now()->diffInDays($fechaPago, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : round($diasParaPago);
        } else {
            $fechaPago = Carbon::now()->addMonth()->day(1);
            $diasParaPago = round(Carbon::now()->diffInDays($fechaPago));
        }

        // 3. Incidencias Totales Activas
        $totalIncidencias = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->where('tbl_incidencia.estado_incidencia', '!=', 'resuelta')
            ->where(function ($query) use ($userId) {
                $query->where('tbl_incidencia.id_reporta_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->count();

        // 4. Listado de Propiedades Únicas (FILTRADO)
        $query = DB::table('tbl_propiedad')
            ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->leftJoin('tbl_fotos', function ($join) {
                $join->on('tbl_fotos.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
                    ->whereRaw('tbl_fotos.id_foto = (select min(id_foto) from tbl_fotos where id_propiedad_fk = tbl_propiedad.id_propiedad)');
            })
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($qb) use ($userId) {
                $qb->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            });

        // Aplicar filtros dinámicos
        if ($request->filled('q')) {
            $query->where('tbl_propiedad.titulo_propiedad', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('ciudad')) {
            $query->where('tbl_propiedad.ciudad_propiedad', $request->ciudad);
        }

        $alquileres = $query->select(
                'tbl_propiedad.*',
                DB::raw('MIN(tbl_fotos.ruta_foto) as ruta_foto'),
                DB::raw('MIN(tbl_alquiler.estado_alquiler) as estado_alquiler'),
                DB::raw('MIN(tbl_alquiler.fecha_fin_alquiler) as fecha_fin_alquiler'),
                DB::raw('(SELECT COUNT(*) FROM tbl_incidencia WHERE id_propiedad_fk = tbl_propiedad.id_propiedad AND estado_incidencia != "resuelta") as total_incidencias_propiedad')
            )
            ->groupBy('tbl_propiedad.id_propiedad')
            ->get();

        // 5. Obtener ciudades únicas para el filtro
        $ciudades = DB::table('tbl_propiedad')
            ->join('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->where(function ($qb) use ($userId) {
                $qb->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->distinct()
            ->pluck('ciudad_propiedad');

        // Si es una petición AJAX (Fetch), devolver solo el grid
        if ($request->ajax()) {
            return view('inquilino.partials.grid_propiedades', compact('alquileres'))->render();
        }

        return view('inquilino.gestionar_propiedades', [
            'nombreUsuario' => $nombreUsuario,
            'tieneFoto' => $tieneFoto,
            'fotoUsuario' => $fotoUsuario,
            'inicialUsuario' => $inicialUsuario,
            'esInquilino' => true,
            'totalContratos' => $totalContratos,
            'diasParaPago' => $diasParaPago,
            'totalIncidencias' => $totalIncidencias,
            'alquileres' => $alquileres,
            'ciudades' => $ciudades
        ]);
    }

    public function verPropiedad($id)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');

        $userId = $usuario->id_usuario;

        // 1. Obtener el alquiler activo para esta propiedad y usuario (inquilino o propietario)
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->leftJoin('tbl_contrato', 'tbl_contrato.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->where('tbl_alquiler.id_propiedad_fk', $id)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(function ($query) use ($userId) {
                $query->where('tbl_alquiler.id_inquilino_fk', $userId)
                    ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
            })
            ->select(
                'tbl_alquiler.*',
                'tbl_propiedad.*',
                'tbl_contrato.url_pdf_contrato',
                'tbl_contrato.estado_contrato as estado_contrato_pdf'
            )
            ->first();

        if (!$alquiler) {
            return redirect()->route('gestionar_propiedades')->with('error', 'No tienes un alquiler activo para esta propiedad.');
        }

        // Lógica de usuario consistente con Miembro
        $nombreUsuario = $usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '';
        $tieneFoto = !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';

        // 2. Fotos de la propiedad
        $fotos = DB::table('tbl_fotos')
            ->where('id_propiedad_fk', $id)
            ->get();

        // 3. Próximo pago
        $proximoPago = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $alquiler->id_alquiler)
            ->where('estado_pago', 'pendiente')
            ->orderBy('mes_pago', 'asc')
            ->first();

        if ($proximoPago && $proximoPago->mes_pago) {
            $fechaPago = Carbon::parse($proximoPago->mes_pago)->day(1);
            $diasParaPago = Carbon::now()->diffInDays($fechaPago, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : round($diasParaPago);
        } else {
            $diasParaPago = 0;
        }

        // 4. Incidencias (Todas las de la propiedad)
        $incidencias = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $id)
            ->orderBy('creado_incidencia', 'desc')
            ->get();

        return view('inquilino.ver_propiedad', [
            'nombreUsuario' => $nombreUsuario,
            'tieneFoto' => $tieneFoto,
            'fotoUsuario' => $fotoUsuario,
            'inicialUsuario' => $inicialUsuario,
            'alquiler' => $alquiler,
            'fotos' => $fotos,
            'diasParaPago' => $diasParaPago,
            'incidencias' => $incidencias,
            'esInquilino' => true,
            'pdfEjemplo' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
        ]);
    }

    public function reportarIncidencia(Request $request, $id)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');

        $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria' => 'required|string',
            'prioridad' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear la incidencia
            $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
                'id_propiedad_fk' => $id,
                'id_reporta_fk' => $usuario->id_usuario,
                'titulo_incidencia' => $request->titulo,
                'descripcion_incidencia' => $request->descripcion,
                'categoria_incidencia' => $request->categoria,
                'prioridad_incidencia' => $request->prioridad,
                'estado_incidencia' => 'abierta',
                'creado_incidencia' => Carbon::now(),
                'actualizado_incidencia' => Carbon::now()
            ]);

            // 2. Crear el primer registro en el historial
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $idIncidencia,
                'id_usuario_fk' => $usuario->id_usuario,
                'comentario_historial' => 'Incidencia reportada por el inquilino/propietario.',
                'cambio_estado_historial' => 'abierta',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Incidencia reportada correctamente. Se ha añadido al listado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al reportar la incidencia: ' . $e->getMessage());
        }
    }

    /**
     * Permite al inquilino cerrar una incidencia que él mismo ha reportado.
     */
    public function cerrarIncidencia($id)
    {
        $userId = auth()->id();

        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->first();

        if (!$incidencia) {
            return back()->with('error', 'Incidencia no encontrada.');
        }

        // Seguridad: Solo el autor que reportó puede cerrar la incidencia
        if ($incidencia->id_reporta_fk != $userId) {
            return back()->with('error', 'No tienes permiso para cerrar esta incidencia.');
        }

        // Seguridad adicional: No cerrar si ya está resuelta
        if ($incidencia->estado_incidencia === 'resuelta') {
            return back()->with('info', 'Esta incidencia ya está marcada como resuelta.');
        }

        try {
            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'estado_incidencia' => 'resuelta',
                    'actualizado_incidencia' => now()
                ]);

            return back()->with('success', '¡Incidencia cerrada correctamente! Gracias por confirmar la solución.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cerrar la incidencia: ' . $e->getMessage());
        }
    }
}
