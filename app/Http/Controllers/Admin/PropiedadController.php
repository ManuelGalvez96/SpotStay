<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ActividadService;
use App\Services\PdfMonkeyService;

class PropiedadController extends Controller
{
    private $actividadService;

    public function __construct()
    {
        $this->actividadService = new ActividadService();
    }

    public function nueva()
    {
        return view('admin.propiedades-crear');
    }

    public function editar($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->select(
                'tbl_propiedad.*',
                'arrendador.email_usuario as email_arrendador'
            )
            ->where('tbl_propiedad.id_propiedad', $id)
            ->first();

        if (!$propiedad) {
            abort(404);
        }

        if (isset($propiedad->estado_propiedad) && $propiedad->estado_propiedad === 'alquilada') {
            abort(403, 'No se puede editar una propiedad alquilada.');
        }

        return view('admin.propiedades-crear', [
            'propiedadEditando' => $propiedad,
        ]);
    }

    public function crear(Request $request)
    {
        $datos = $request->validate([
            'titulo' => 'required|string|max:150',
            'calle' => 'required|string|max:150',
            'numero' => 'required|string|max:20',
            'piso' => 'nullable|string|max:20',
            'puerta' => 'nullable|string|max:20',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'precio' => 'required|numeric|min:0',
            'tipo' => 'nullable|string|in:piso,casa,estudio,chalet',
            'habitaciones' => 'nullable|string|in:1,2,3,4,4+',
            'metros' => 'nullable|integer|min:1',
            'banos' => 'nullable|string|in:1,2,3,3+',
            'estado' => 'required|in:publicada,alquilada,borrador,inactiva',
            'descripcion' => 'nullable|string',
            'extras' => 'nullable|array',
            'extras.*' => 'string|in:amueblado,piscina,terraza,garaje,ascensor,aire_acondicionado,calefaccion,trastero',
            'adicional' => 'nullable|string|max:255',
            'arrendador_email' => 'required|email',
        ]);

        $arrendador = DB::table('tbl_usuario as u')
            ->join('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('u.email_usuario', $datos['arrendador_email'])
            ->where('r.slug_rol', 'arrendador')
            ->select('u.id_usuario', 'u.nombre_usuario')
            ->first();

        if (!$arrendador) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe un arrendador con ese email.',
                ], 422);
            }

            return back()
                ->withErrors(['arrendador_email' => 'No existe un arrendador con ese email.'])
                ->withInput();
        }

        $precioColumna = $this->obtenerColumnaPrecio();
        $ahora = Carbon::now();

        $idPropiedad = DB::table('tbl_propiedad')->insertGetId([
            'id_arrendador_fk' => $arrendador->id_usuario,
            'id_gestor_fk' => $arrendador->id_usuario,
            'titulo_propiedad' => $datos['titulo'],
            'calle_propiedad' => $datos['calle'],
            'numero_propiedad' => $datos['numero'],
            'piso_propiedad' => $datos['piso'] ?: null,
            'puerta_propiedad' => $datos['puerta'] ?: null,
            'ciudad_propiedad' => $datos['ciudad'],
            'codigo_postal_propiedad' => $datos['codigo_postal'],
            'descripcion_propiedad' => $datos['descripcion'] ?: null,
            'tipo_propiedad' => $datos['tipo'] ?: null,
            'habitaciones_propiedad' => $datos['habitaciones'] ?: null,
            'metros_cuadrados_propiedad' => $datos['metros'] ?: null,
            'banos_propiedad' => $datos['banos'] ?: null,
            'amueblado_propiedad' => in_array('amueblado', $datos['extras'] ?? []) ? 1 : 0,
            'piscina_propiedad' => in_array('piscina', $datos['extras'] ?? []) ? 1 : 0,
            'terraza_propiedad' => in_array('terraza', $datos['extras'] ?? []) ? 1 : 0,
            'garaje_propiedad' => in_array('garaje', $datos['extras'] ?? []) ? 1 : 0,
            'ascensor_propiedad' => in_array('ascensor', $datos['extras'] ?? []) ? 1 : 0,
            'aire_acondicionado_propiedad' => in_array('aire_acondicionado', $datos['extras'] ?? []) ? 1 : 0,
            'calefaccion_propiedad' => in_array('calefaccion', $datos['extras'] ?? []) ? 1 : 0,
            'trastero_propiedad' => in_array('trastero', $datos['extras'] ?? []) ? 1 : 0,
            'adicional_propiedad' => $datos['adicional'] ?: null,
            $precioColumna => $datos['precio'],
            'estado_propiedad' => $datos['estado'],
            'creado_propiedad' => $ahora,
            'actualizado_propiedad' => $ahora,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Propiedad creada correctamente.',
                'propiedad' => [
                    'id' => $idPropiedad,
                    'titulo' => $datos['titulo'],
                    'direccion' => trim($datos['calle'] . ' ' . $datos['numero']),
                    'ciudad' => $datos['ciudad'],
                    'codigo_postal' => $datos['codigo_postal'],
                    'precio' => $datos['precio'],
                    'estado' => $datos['estado'],
                    'arrendador_nombre' => $arrendador->nombre_usuario,
                ],
            ]);
        }

        return redirect('/admin/propiedades')->with('success', 'Propiedad creada correctamente.');
    }

    public function actualizar(Request $request, $id)
    {
        $propiedadExistente = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->first();

        if (!$propiedadExistente) {
            abort(404);
        }

        $datos = $request->validate([
            'titulo' => 'required|string|max:150',
            'calle' => 'required|string|max:150',
            'numero' => 'required|string|max:20',
            'piso' => 'nullable|string|max:20',
            'puerta' => 'nullable|string|max:20',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'precio' => 'required|numeric|min:0',
            'tipo' => 'nullable|string|in:piso,casa,estudio,chalet',
            'habitaciones' => 'nullable|string|in:1,2,3,4,4+',
            'metros' => 'nullable|integer|min:1',
            'banos' => 'nullable|string|in:1,2,3,3+',
            'estado' => 'required|in:publicada,alquilada,borrador,inactiva',
            'descripcion' => 'nullable|string',
            'extras' => 'nullable|array',
            'extras.*' => 'string|in:amueblado,piscina,terraza,garaje,ascensor,aire_acondicionado,calefaccion,trastero',
            'adicional' => 'nullable|string|max:255',
            'arrendador_email' => 'required|email',
        ]);

        $arrendador = DB::table('tbl_usuario as u')
            ->join('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('u.email_usuario', $datos['arrendador_email'])
            ->where('r.slug_rol', 'arrendador')
            ->select('u.id_usuario')
            ->first();

        if (!$arrendador) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe un arrendador con ese email.',
                ], 422);
            }

            return back()
                ->withErrors(['arrendador_email' => 'No existe un arrendador con ese email.'])
                ->withInput();
        }

        $precioColumna = $this->obtenerColumnaPrecio();

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update([
                'id_arrendador_fk' => $arrendador->id_usuario,
                'titulo_propiedad' => $datos['titulo'],
                'calle_propiedad' => $datos['calle'],
                'numero_propiedad' => $datos['numero'],
                'piso_propiedad' => $datos['piso'] ?: null,
                'puerta_propiedad' => $datos['puerta'] ?: null,
                'ciudad_propiedad' => $datos['ciudad'],
                'codigo_postal_propiedad' => $datos['codigo_postal'],
                'descripcion_propiedad' => $datos['descripcion'] ?: null,
                'tipo_propiedad' => $datos['tipo'] ?: null,
                'habitaciones_propiedad' => $datos['habitaciones'] ?: null,
                'metros_cuadrados_propiedad' => $datos['metros'] ?: null,
                'banos_propiedad' => $datos['banos'] ?: null,
                'amueblado_propiedad' => in_array('amueblado', $datos['extras'] ?? []) ? 1 : 0,
                'piscina_propiedad' => in_array('piscina', $datos['extras'] ?? []) ? 1 : 0,
                'terraza_propiedad' => in_array('terraza', $datos['extras'] ?? []) ? 1 : 0,
                'garaje_propiedad' => in_array('garaje', $datos['extras'] ?? []) ? 1 : 0,
                'ascensor_propiedad' => in_array('ascensor', $datos['extras'] ?? []) ? 1 : 0,
                'aire_acondicionado_propiedad' => in_array('aire_acondicionado', $datos['extras'] ?? []) ? 1 : 0,
                'calefaccion_propiedad' => in_array('calefaccion', $datos['extras'] ?? []) ? 1 : 0,
                'trastero_propiedad' => in_array('trastero', $datos['extras'] ?? []) ? 1 : 0,
                'adicional_propiedad' => $datos['adicional'] ?: null,
                $precioColumna => $datos['precio'],
                'estado_propiedad' => $datos['estado'],
                'actualizado_propiedad' => Carbon::now(),
            ]);

        if ($propiedadExistente->estado_propiedad !== $datos['estado'] && $propiedadExistente->id_gestor_fk) {
            $this->actividadService->propiedadEstadoCambiado(
                $propiedadExistente->id_gestor_fk,
                $id,
                $datos['titulo'],
                $propiedadExistente->estado_propiedad,
                $datos['estado'],
                'Admin'
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Propiedad actualizada correctamente.',
            ]);
        }

        return redirect('/admin/propiedades')->with('success', 'Propiedad actualizada correctamente.');
    }

    public function index()
    {
        $propiedades = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk,
              COUNT(*) as total_inquilinos
              FROM tbl_alquiler WHERE estado_alquiler = "activo"
              GROUP BY id_propiedad_fk) as alq'),
              'alq.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->select(
              'tbl_propiedad.*',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'arrendador.nombre_usuario as nombre_arrendador',
              'alq.total_inquilinos'
            )
            ->orderBy('tbl_propiedad.creado_propiedad', 'desc')
            ->paginate(10);

        $totalPropiedades = DB::table('tbl_propiedad')->count();
        $alquiladas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'alquilada')->count();
        $publicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')->count();
        $inactivas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'inactiva')->count();

        return view('admin.propiedades', compact(
            'propiedades', 'totalPropiedades',
            'alquiladas', 'publicadas', 'inactivas'));
    }

    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk,
              COUNT(*) as total_inquilinos
              FROM tbl_alquiler WHERE estado_alquiler = "activo"
              GROUP BY id_propiedad_fk) as alq'),
              'alq.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->select(
              'tbl_propiedad.id_propiedad',
              'tbl_propiedad.titulo_propiedad',
              'tbl_propiedad.calle_propiedad',
              'tbl_propiedad.numero_propiedad',
              'tbl_propiedad.piso_propiedad',
              'tbl_propiedad.puerta_propiedad',
              'tbl_propiedad.ciudad_propiedad',
              'tbl_propiedad.codigo_postal_propiedad',
              'tbl_propiedad.precio_propiedad',
              'tbl_propiedad.estado_propiedad',
              'arrendador.nombre_usuario as nombre_arrendador',
              'alq.total_inquilinos',
              DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad")
            );

        if ($request->input('estado')) {
            $query->whereRaw('LOWER(estado_propiedad) = ?', [strtolower($request->input('estado'))]);
        }

        if ($request->input('ciudad')) {
            $query->whereRaw('LOWER(ciudad_propiedad) = ?', [strtolower($request->input('ciudad'))]);
        }

        if ($request->input('q')) {
            $q = '%' . strtolower(trim($request->input('q'))) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->whereRaw('LOWER(tbl_propiedad.titulo_propiedad) like ?', [$q])
                    ->orWhereRaw('LOWER(tbl_propiedad.calle_propiedad) like ?', [$q])
                    ->orWhereRaw('LOWER(tbl_propiedad.ciudad_propiedad) like ?', [$q])
                    ->orWhereRaw('LOWER(arrendador.nombre_usuario) like ?', [$q]);
            });
        }

        $precio = $request->input('precio');
        if ($precio) {
            if (str_contains($precio, '-')) {
                [$min, $max] = explode('-', $precio, 2);
                if (is_numeric($min)) {
                    $query->where('precio_propiedad', '>=', (float) $min);
                }
                if (is_numeric($max)) {
                    $query->where('precio_propiedad', '<=', (float) $max);
                }
            } elseif (str_contains($precio, '+')) {
                $min = str_replace('+', '', $precio);
                if (is_numeric($min)) {
                    $query->where('precio_propiedad', '>=', (float) $min);
                }
            }
        }

        $perPage = 10;
        $paginadas = $query->orderBy('tbl_propiedad.creado_propiedad', 'desc')->paginate($perPage);

        $propiedades = $paginadas->getCollection()->map(function ($p) {
            $totalInquilinos = (int) ($p->total_inquilinos ?? 0);
            $maxInquilinos = max(1, $totalInquilinos);

            return [
                'id' => $p->id_propiedad,
                'direccion' => $p->direccion_propiedad,
                'ciudad' => $p->ciudad_propiedad,
                'cp' => $p->codigo_postal_propiedad,
                'arrendadorNombre' => $p->nombre_arrendador,
                'estado' => $p->estado_propiedad,
                'precio' => '$' . number_format((float) $p->precio_propiedad, 2, '.', '') . '/mes',
                'inquilinosActuales' => $totalInquilinos,
                'inquilinosTotales' => $maxInquilinos,
                'color' => $this->colorPorId((int) $p->id_propiedad),
            ];
        })->values();

        return response()->json([
            'propiedades' => $propiedades,
            'total' => $paginadas->total(),
            'currentPage' => $paginadas->currentPage(),
            'totalPages' => $paginadas->lastPage(),
            'from' => $paginadas->firstItem(),
            'to' => $paginadas->lastItem(),
        ]);
    }

    public function show($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin('tbl_usuario as gestor',
              'gestor.id_usuario', '=',
              'tbl_propiedad.id_gestor_fk')
            ->select(
              'tbl_propiedad.*',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'arrendador.nombre_usuario as nombre_arrendador',
              'arrendador.email_usuario as email_arrendador',
              'gestor.nombre_usuario as nombre_gestor'
            )
            ->where('tbl_propiedad.id_propiedad', $id)
            ->first();

        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_alquiler.id_inquilino_fk')
            ->where('id_propiedad_fk', $id)
            ->where('estado_alquiler', 'activo')
            ->select('tbl_alquiler.*', 'tbl_usuario.nombre_usuario')
            ->get();

        return response()->json([
            'propiedad' => $propiedad,
            'alquileres' => $alquileres
        ]);
    }

    public function desactivar($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->select('id_propiedad', 'estado_propiedad')
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'Propiedad no encontrada.'
            ], 404);
        }

        if ($propiedad->estado_propiedad === 'alquilada') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar una propiedad alquilada.'
            ], 422);
        }

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update(['estado_propiedad' => 'inactiva']);

        if ($propiedad->id_gestor_fk) {
            $this->actividadService->propiedadEstadoCambiado(
                $propiedad->id_gestor_fk,
                $id,
                $propiedad->titulo_propiedad,
                $propiedad->estado_propiedad,
                'inactiva',
                'Admin'
            );
        }

        return response()->json(['success' => true]);
    }

    public function publicar($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->select('id_propiedad', 'id_arrendador_fk', 'estado_propiedad')
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'Propiedad no encontrada.'
            ], 404);
        }

        if ($propiedad->estado_propiedad === 'alquilada') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede publicar una propiedad alquilada.'
            ], 422);
        }

        if ($propiedad->estado_propiedad !== 'borrador') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden publicar propiedades en estado borrador.'
            ], 422);
        }

        $publicadasDelArrendador = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $propiedad->id_arrendador_fk)
            ->where('estado_propiedad', 'publicada')
            ->count();

        if ($publicadasDelArrendador >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'El arrendador ya tiene el máximo de 10 propiedades publicadas.'
            ], 422);
        }

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update([
                'estado_propiedad' => 'publicada',
                'aprobada_propiedad' => Schema::hasColumn('tbl_propiedad', 'aprobada_propiedad') ? Carbon::now() : null,
                'actualizado_propiedad' => Carbon::now()
            ]);

        return response()->json(['success' => true]);
    }

    public function eliminar(Request $request, $id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->select('id_propiedad', 'estado_propiedad')
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'Propiedad no encontrada.'
            ], 404);
        }

        if (isset($propiedad->estado_propiedad) && $propiedad->estado_propiedad === 'alquilada') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una propiedad alquilada.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $alquileresIds = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $id)
                ->pluck('id_alquiler')
                ->all();

            $gastosIds = Schema::hasTable('tbl_gasto')
                ? DB::table('tbl_gasto')
                    ->where('id_propiedad_fk', $id)
                    ->pluck('id_gasto')
                    ->all()
                : [];

            $gastoCuotasIds = !empty($gastosIds) && Schema::hasTable('tbl_gasto_cuota')
                ? DB::table('tbl_gasto_cuota')
                    ->whereIn('id_gasto_fk', $gastosIds)
                    ->pluck('id_gasto_cuota')
                    ->all()
                : [];

            $detallesIds = [];
            if (Schema::hasTable('tbl_gasto_cuota_detalle') && !empty($gastoCuotasIds)) {
                $tieneColAlquiler = Schema::hasColumn('tbl_gasto_cuota_detalle', 'id_alquiler_fk');

                if ($tieneColAlquiler && !empty($alquileresIds)) {
                    $detallesIds = DB::table('tbl_gasto_cuota_detalle')
                        ->where(function ($query) use ($alquileresIds, $gastoCuotasIds) {
                            $query->whereIn('id_alquiler_fk', $alquileresIds)
                                ->orWhereIn('id_gasto_cuota_fk', $gastoCuotasIds);
                        })
                        ->pluck('id_gasto_cuota_detalle')
                        ->all();
                } else {
                    $detallesIds = DB::table('tbl_gasto_cuota_detalle')
                        ->whereIn('id_gasto_cuota_fk', $gastoCuotasIds)
                        ->pluck('id_gasto_cuota_detalle')
                        ->all();
                }
            }

            if (Schema::hasTable('tbl_pago')) {
                DB::table('tbl_pago')
                    ->where(function ($query) use ($alquileresIds, $gastoCuotasIds, $detallesIds) {
                        $hasCondition = false;
                        if (!empty($alquileresIds)) {
                            $query->whereIn('id_alquiler_fk', $alquileresIds);
                            $hasCondition = true;
                        }

                        if (!empty($gastoCuotasIds)) {
                            $hasCondition ? $query->orWhereIn('id_gasto_cuota_fk', $gastoCuotasIds) : $query->whereIn('id_gasto_cuota_fk', $gastoCuotasIds);
                            $hasCondition = true;
                        }

                        if (!empty($detallesIds)) {
                            $hasCondition ? $query->orWhereIn('id_gasto_cuota_detalle_fk', $detallesIds) : $query->whereIn('id_gasto_cuota_detalle_fk', $detallesIds);
                        }
                    })
                    ->delete();
            }

            if (!empty($alquileresIds)) {
                DB::table('tbl_contrato')
                    ->whereIn('id_alquiler_fk', $alquileresIds)
                    ->delete();

                if (Schema::hasTable('tbl_valoracion')) {
                    DB::table('tbl_valoracion')
                        ->where('id_propiedad_fk', $id)
                        ->orWhereIn('id_alquiler_fk', $alquileresIds)
                        ->delete();
                }
            }

            if (!empty($gastosIds) && Schema::hasTable('tbl_gasto')) {
                DB::table('tbl_gasto')
                    ->where('id_propiedad_fk', $id)
                    ->delete();
            }

            DB::table('tbl_incidencia')
                ->where('id_propiedad_fk', $id)
                ->delete();

            if (Schema::hasTable('tbl_conversacion')) {
                DB::table('tbl_conversacion')
                    ->where('id_propiedad_fk', $id)
                    ->delete();
            }

            DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $id)
                ->delete();

            DB::table('tbl_propiedad')
                ->where('id_propiedad', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad eliminada correctamente.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar propiedad ID ' . $id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la propiedad. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarPdf($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->select('id_propiedad', 'estado_propiedad', 'id_gestor_fk', 'titulo_propiedad')
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'Propiedad no encontrada.'
            ], 404);
        }

        $contrato = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->where('a.id_propiedad_fk', $id)
            ->whereNotNull('c.url_pdf_contrato')
            ->orderByDesc('c.id_contrato')
            ->select('c.id_contrato', 'a.id_alquiler', 'c.url_pdf_contrato')
            ->first();

        if (!$contrato) {
            $alquilerActivo = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $id)
                ->whereIn('estado_alquiler', ['activo', 'pendiente'])
                ->orderByDesc('id_alquiler')
                ->select('id_alquiler')
                ->first();

            if (!$alquilerActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay un contrato disponible para esta propiedad.'
                ], 404);
            }

            $urlPdfNueva = $this->generarPdfContrato($alquilerActivo->id_alquiler, null);

            if (!$urlPdfNueva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo generar el PDF del contrato.'
                ], 500);
            }

            return redirect()->away($urlPdfNueva);
        }

        if ($this->esUrlPdfExpirada($contrato->url_pdf_contrato)) {
            $urlPdfNueva = $this->generarPdfContrato($contrato->id_alquiler, $contrato->id_contrato);

            if (!$urlPdfNueva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo regenerar el PDF del contrato.'
                ], 500);
            }

            return redirect()->away($urlPdfNueva);
        }

        return redirect()->away($this->normalizarUrlPdf($contrato->url_pdf_contrato));
    }

    private function esUrlPdfExpirada(string $urlPdf): bool
    {
        $componentes = parse_url($urlPdf);
        if (!$componentes || empty($componentes['query'])) {
            return false;
        }

        parse_str($componentes['query'], $parametros);
        $ahora = Carbon::now('UTC')->timestamp;
        $margenSeguridad = 30;

        if (isset($parametros['Expires']) && is_numeric($parametros['Expires'])) {
            return $ahora >= (((int) $parametros['Expires']) - $margenSeguridad);
        }

        $xAmzDate = $parametros['X-Amz-Date'] ?? null;
        $xAmzExpires = $parametros['X-Amz-Expires'] ?? null;
        if ($xAmzDate && $xAmzExpires && is_numeric($xAmzExpires)) {
            $fechaFirma = Carbon::createFromFormat('Ymd\\THis\\Z', $xAmzDate, 'UTC');
            if ($fechaFirma !== false) {
                $expiraEn = $fechaFirma->copy()->addSeconds((int) $xAmzExpires)->timestamp;
                return $ahora >= ($expiraEn - $margenSeguridad);
            }
        }

        return false;
    }

    private function normalizarUrlPdf(string $urlPdf): string
    {
        if (preg_match('#^https?://#i', $urlPdf)) {
            return $urlPdf;
        }

        return url('/' . ltrim($urlPdf, '/\\'));
    }

    private function generarPdfContrato(int $idAlquiler, ?int $idContrato = null): ?string
    {
        try {
            $pdfMonkey = new PdfMonkeyService();

            if (!$pdfMonkey->estaConfigurado()) {
                Log::warning('PdfMonkey no está configurado para regenerar el contrato.');
                return null;
            }

            $datosAlquiler = DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'a.id_propiedad_fk', '=', 'p.id_propiedad')
                ->join('tbl_usuario as arrendador', 'p.id_arrendador_fk', '=', 'arrendador.id_usuario')
                ->join('tbl_usuario as inquilino', 'a.id_inquilino_fk', '=', 'inquilino.id_usuario')
                ->where('a.id_alquiler', $idAlquiler)
                ->select(
                    'a.id_alquiler',
                    'a.fecha_inicio_alquiler',
                    'a.fecha_fin_alquiler',
                    'arrendador.nombre_usuario as nombre_arrendador',
                    'arrendador.email_usuario as email_arrendador',
                    'inquilino.nombre_usuario as nombre_inquilino',
                    'inquilino.email_usuario as email_inquilino',
                    'p.titulo_propiedad',
                    DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                    'p.ciudad_propiedad',
                    'p.precio_propiedad'
                )
                ->first();

            if (!$datosAlquiler) {
                return null;
            }

            $precioMensual = (float) ($datosAlquiler->precio_propiedad ?? 0);
            $fianza = $precioMensual * 2;

            $datosContrato = [
                'nombre_arrendador' => $datosAlquiler->nombre_arrendador,
                'email_arrendador' => $datosAlquiler->email_arrendador,
                'nombre_inquilino' => $datosAlquiler->nombre_inquilino,
                'email_inquilino' => $datosAlquiler->email_inquilino,
                'titulo_propiedad' => $datosAlquiler->titulo_propiedad,
                'direccion_propiedad' => $datosAlquiler->direccion_propiedad,
                'ciudad_propiedad' => $datosAlquiler->ciudad_propiedad,
                'precio_mensual' => number_format($precioMensual, 2, '.', ''),
                'fianza' => number_format($fianza, 2, '.', ''),
                'fecha_inicio' => Carbon::parse($datosAlquiler->fecha_inicio_alquiler)->format('d/m/Y'),
                'fecha_fin' => $datosAlquiler->fecha_fin_alquiler
                    ? Carbon::parse($datosAlquiler->fecha_fin_alquiler)->format('d/m/Y')
                    : 'Indefinida',
                'fecha_generacion' => Carbon::now()->format('d/m/Y'),
            ];

            $respuesta = $pdfMonkey->crearDocumentoSincronizado(
                $datosContrato,
                $pdfMonkey->construirMeta([], 'contrato_' . $idAlquiler . '.pdf')
            );

            $urlPdf = $respuesta['document_card']['download_url']
                ?? ($respuesta['document']['id'] ?? null ? $pdfMonkey->obtenerUrlDescarga($respuesta['document']['id']) : null);

            if (!$urlPdf) {
                return null;
            }

            $datosActualizar = [
                'url_pdf_contrato' => $urlPdf,
                'actualizado_contrato' => Carbon::now(),
            ];

            if ($idContrato) {
                DB::table('tbl_contrato')
                    ->where('id_contrato', $idContrato)
                    ->update($datosActualizar);
            } else {
                DB::table('tbl_contrato')->insertOrIgnore([
                    'id_alquiler_fk' => $idAlquiler,
                    'url_pdf_contrato' => $urlPdf,
                    'estado_contrato' => 'pendiente',
                    'creado_contrato' => Carbon::now(),
                    'actualizado_contrato' => Carbon::now(),
                ]);
            }

            return $urlPdf;
        } catch (\Throwable $e) {
            Log::error('Error al regenerar PDF del contrato de propiedad: ' . $e->getMessage(), [
                'id_alquiler' => $idAlquiler,
                'id_contrato' => $idContrato,
            ]);

            return null;
        }
    }

    public function exportar()
    {
        $propiedades = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
                        ->select(
                            'tbl_propiedad.*',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                            'arrendador.nombre_usuario as nombre_arrendador'
                        )
            ->get();

        return response()->json($propiedades);
    }

    private function obtenerSelectDireccionPropiedad(string $aliasTabla = 'p'): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return "{$aliasTabla}.direccion_propiedad as direccion_propiedad";
        }

        $partes = [];
        foreach (['calle_propiedad', 'numero_propiedad', 'piso_propiedad', 'puerta_propiedad'] as $columna) {
            if (Schema::hasColumn('tbl_propiedad', $columna)) {
                $partes[] = "NULLIF(TRIM({$aliasTabla}.{$columna}), '')";
            }
        }

        if (empty($partes)) {
            return "'' as direccion_propiedad";
        }

        return 'TRIM(CONCAT_WS(\' \' , ' . implode(', ', $partes) . ')) as direccion_propiedad';
    }

    private function obtenerColumnaPrecio(): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'precio_propiedad')) {
            return 'precio_propiedad';
        }

        if (Schema::hasColumn('tbl_propiedad', 'precio_mensual_propiedad')) {
            return 'precio_mensual_propiedad';
        }

        return 'precio_propiedad';
    }

    private function colorPorId(int $id): string
    {
        $paleta = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7', '#CCE5FF', '#FDE8C8'];
        return $paleta[$id % count($paleta)];
    }
}
