<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PropiedadController extends Controller
{
    public function nueva()
    {
        return view('admin.propiedades-crear');
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
            'estado' => 'required|in:publicada,alquilada,borrador,inactiva',
            'descripcion' => 'nullable|string',
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
            $query->where('estado_propiedad', $request->input('estado'));
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
        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update(['estado_propiedad' => 'inactiva']);

        return response()->json(['success' => true]);
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
