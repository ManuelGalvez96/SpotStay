<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use App\Models\Contrato;
use App\Models\Pago;
use App\Services\ActividadService;
use App\Services\PdfMonkeyService;

class AlquilerController extends Controller
{
    private $actividadService;

    public function __construct()
    {
        $this->actividadService = new ActividadService();
    }
    /**
     * Mostrar formulario de nuevo alquiler
     */
    public function nueva()
    {
        $propiedadesPublicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->select('id_propiedad', 'titulo_propiedad', 'ciudad_propiedad', 'precio_propiedad')
            ->orderBy('titulo_propiedad')
            ->get();

        $inquilinos = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol', 'tbl_rol_usuario.id_rol_fk', '=', 'tbl_rol.id_rol')
            ->where('tbl_rol.nombre_rol', 'inquilino')
            ->select('tbl_usuario.id_usuario', 'tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario')
            ->orderBy('tbl_usuario.nombre_usuario')
            ->get();

        return view('admin.alquileres-crear', compact('propiedadesPublicadas', 'inquilinos'));
    }

    /**
     * Mostrar formulario de edicion de alquiler
     */
    public function editar($id)
    {
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_alquiler.estado_alquiler',
                'tbl_propiedad.precio_propiedad as precio_referencia'
            )
            ->where('tbl_alquiler.id_alquiler', $id)
            ->first();

        if (!$alquiler) {
            abort(404);
        }

        $propiedadesPublicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->select('id_propiedad', 'titulo_propiedad', 'ciudad_propiedad', 'precio_propiedad')
            ->orderBy('titulo_propiedad')
            ->get();

        $inquilinos = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol', 'tbl_rol_usuario.id_rol_fk', '=', 'tbl_rol.id_rol')
            ->where('tbl_rol.nombre_rol', 'inquilino')
            ->select('tbl_usuario.id_usuario', 'tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario')
            ->orderBy('tbl_usuario.nombre_usuario')
            ->get();

        return view('admin.alquileres-crear', compact('propiedadesPublicadas', 'inquilinos', 'alquiler'));
    }

    /**
     * Mostrar listado de alquileres con KPI
     */
    public function index()
    {
        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario as inquilino', 'tbl_alquiler.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_usuario as arrendador', 'tbl_propiedad.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->leftJoin('tbl_contrato as c', 'tbl_alquiler.id_alquiler', '=', 'c.id_alquiler_fk')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_alquiler.estado_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.precio_propiedad',
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador'
                , DB::raw("COALESCE(c.url_pdf_contrato, '') as url_pdf_contrato")
            )
            ->paginate(10);

        // Añadir marca de disponibilidad física del PDF por cada alquiler
        $alquileres->getCollection()->transform(function ($alq) {
            $alq->pdf_disponible = false;
            $url = $alq->url_pdf_contrato ?? '';
            if (!empty($url)) {
                // Si es URL absoluta que apunta al mismo host, comprobar ruta en public/
                if (preg_match('#^https?://#i', $url)) {
                    $componentes = parse_url($url);
                    $hostRemoto = $componentes['host'] ?? null;
                    $requestHost = request()->getHost();
                    if ($hostRemoto && $hostRemoto === $requestHost) {
                        $ruta = ltrim($componentes['path'] ?? '', '/\\');
                        $alq->pdf_disponible = File::exists(public_path($ruta));
                    } else {
                        // URL remota: asumimos no disponible localmente
                        $alq->pdf_disponible = false;
                    }
                } else {
                    $rutaRelativa = ltrim($url, '/\\');
                    $candidates = [
                        public_path($rutaRelativa),
                        public_path('storage/' . $rutaRelativa),
                        storage_path('app/public/' . $rutaRelativa),
                    ];
                    $exists = false;
                    foreach ($candidates as $p) {
                        if (File::exists($p)) {
                            $exists = true;
                            break;
                        }
                    }
                    $alq->pdf_disponible = $exists;
                }
            }
            return $alq;
        });

        $activos = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'activo')
            ->count();

        $pendientes = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'pendiente')
            ->count();

        $rechazados = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'rechazado')
            ->count();

        $finalizanMes = DB::table('tbl_alquiler')
            ->whereMonth('fecha_fin_alquiler', Carbon::now()->month)
            ->whereYear('fecha_fin_alquiler', Carbon::now()->year)
            ->count();

        $propiedades = DB::table('tbl_propiedad')->get();
        $propiedadesPublicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->get();
        $inquilinos = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol', 'tbl_rol_usuario.id_rol_fk', '=', 'tbl_rol.id_rol')
            ->where('tbl_rol.nombre_rol', 'inquilino')
            ->select('tbl_usuario.id_usuario', 'tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario')
            ->get();

        return view('admin.alquileres', compact(
            'alquileres',
            'activos',
            'pendientes',
            'rechazados',
            'finalizanMes',
            'propiedades',
            'propiedadesPublicadas',
            'inquilinos'
        ));
    }

    /**
     * Obtener detalle de alquiler (JSON)
     */
    public function show($id)
    {
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario as inquilino', 'tbl_alquiler.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_usuario as arrendador', 'tbl_propiedad.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->select(
                'tbl_alquiler.*',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.precio_propiedad',
                DB::raw($this->obtenerSelectFotoPropiedad()),
                'inquilino.nombre_usuario as nombre_usuario_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_usuario_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'arrendador.telefono_usuario as telefono_arrendador'
            )
            ->where('tbl_alquiler.id_alquiler', $id)
            ->first();

        if (!$alquiler) {
            return response()->json([
                'success' => false,
                'message' => 'Alquiler no encontrado'
            ], 404);
        }

        // Generar initiales
        $partes = explode(' ', $alquiler->nombre_usuario_inquilino);
        $alquiler->inicialesInq = strtoupper($partes[0][0] . (isset($partes[1]) ? $partes[1][0] : ''));

        $partes = explode(' ', $alquiler->nombre_usuario_arrendador);
        $alquiler->inicialesArr = strtoupper($partes[0][0] . (isset($partes[1]) ? $partes[1][0] : ''));

        // Colores
        $colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7', '#CCE5FF', '#FDE8C8'];
        $alquiler->colorInq = $colores[$alquiler->id_inquilino_fk % 10];
        $alquiler->colorArr = $colores[$alquiler->id_propiedad_fk % 10];

        // Contrato
        $contrato = DB::table('tbl_contrato')
            ->where('id_alquiler_fk', $id)
            ->first();

        if (!$contrato) {
            $contrato = (object) [
                'firmado_arrendador' => false,
                'firmado_inquilino' => false,
                'estado_contrato' => 'pendiente'
            ];
        }

        // Pago (fianza)
        $pago = DB::table('tbl_pago')
            ->where('id_alquiler_fk', $id)
            ->where('tipo_pago', 'fianza')
            ->first();

        if (!$pago) {
            $pago = (object) [
                'estado_pago' => 'pendiente',
                'importe_pago' => $alquiler->precio_propiedad * 2,
                'referencia_pago' => '—'
            ];
        }

        // Historial
        $historial = [];
        if ($alquiler->creado_alquiler) {
            $historial[] = [
                'comentario' => 'Solicitud de alquiler creada',
                'estado' => 'pendiente',
                'fecha' => Carbon::parse($alquiler->creado_alquiler)->format('Y-m-d H:i')
            ];
        }
        if ($alquiler->aprobado_alquiler) {
            $historial[] = [
                'comentario' => 'Alquiler aprobado',
                'estado' => 'aprobado',
                'fecha' => Carbon::parse($alquiler->aprobado_alquiler)->format('Y-m-d H:i')
            ];
        }

        return response()->json([
            'alquiler' => $alquiler,
            'contrato' => $contrato,
            'pago' => $pago,
            'historial' => $historial
        ]);
    }

    /**
     * Filtrar alquileres (read-only)
     */
    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->join('tbl_usuario as inquilino', 'tbl_alquiler.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_usuario as arrendador', 'tbl_propiedad.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->leftJoin('tbl_contrato as c', 'tbl_alquiler.id_alquiler', '=', 'c.id_alquiler_fk')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.id_propiedad_fk',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_alquiler.estado_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_alquiler.fecha_fin_alquiler',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                'tbl_propiedad.precio_propiedad',
                'inquilino.nombre_usuario as nombre_inquilino',
                'arrendador.id_usuario as id_arrendador',
                'arrendador.nombre_usuario as nombre_arrendador',
                DB::raw("COALESCE(c.url_pdf_contrato, '') as url_pdf_contrato")
            );

        if ($request->has('estado') && $request->estado) {
            $query->where('tbl_alquiler.estado_alquiler', $request->estado);
        }

        if ($request->has('propiedad') && $request->propiedad) {
            $query->where('tbl_alquiler.id_propiedad_fk', $request->propiedad);
        }

        if ($request->has('mes') && $request->mes) {
            $query->whereMonth('tbl_alquiler.fecha_inicio_alquiler', $request->mes);
        }

        if ($request->has('q') && $request->q) {
            $q = '%' . strtolower(trim($request->q)) . '%';
            $query->where(function ($where) use ($q) {
                $where->orWhereRaw('LOWER(tbl_propiedad.titulo_propiedad) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(inquilino.nombre_usuario) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(arrendador.nombre_usuario) LIKE ?', [$q])
                    ->orWhereRaw('LOWER(tbl_propiedad.ciudad_propiedad) LIKE ?', [$q]);
            });
        }

        $paginados = $query
            ->orderByDesc('tbl_alquiler.id_alquiler')
            ->paginate(10);

        $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7','#CCE5FF','#FDE8C8'];

        $alquileres = $paginados->getCollection()->map(function ($alq) use ($colores) {
            $partesInq = explode(' ', $alq->nombre_inquilino ?? '');
            $inicialesInq = strtoupper(substr($partesInq[0] ?? '', 0, 1) . substr($partesInq[1] ?? '', 0, 1));

            $partesArr = explode(' ', $alq->nombre_arrendador ?? '');
            $inicialesArr = strtoupper(substr($partesArr[0] ?? '', 0, 1) . substr($partesArr[1] ?? '', 0, 1));

            return [
                'id' => $alq->id_alquiler,
                'id_propiedad_fk' => $alq->id_propiedad_fk,
                'id_inquilino_fk' => $alq->id_inquilino_fk,
                'id_arrendador' => $alq->id_arrendador,
                'titulo_propiedad' => $alq->titulo_propiedad,
                'ciudad_propiedad' => $alq->ciudad_propiedad,
                'nombre_inquilino' => $alq->nombre_inquilino,
                'nombre_arrendador' => $alq->nombre_arrendador,
                'estado_alquiler' => $alq->estado_alquiler,
                'fecha_inicio_alquiler' => $alq->fecha_inicio_alquiler,
                'fecha_fin_alquiler' => $alq->fecha_fin_alquiler,
                'color_prop' => $colores[$alq->id_propiedad_fk % 10],
                'color_inq' => $colores[$alq->id_inquilino_fk % 10],
                'color_arr' => $colores[$alq->id_arrendador % 10],
                'iniciales_inq' => $inicialesInq,
                'iniciales_arr' => $inicialesArr,
                'url_pdf_contrato' => $alq->url_pdf_contrato ?? '',
            ];
        })->values();

            // Añadir URL de descarga solo si el fichero existe físicamente
            $alquileres = $alquileres->map(function ($a) {
                $url = $a['url_pdf_contrato'] ?? '';
                $exists = false;
                if (!empty($url)) {
                    $rutaRel = ltrim($url, '/\\');
                    $candidates = [public_path($rutaRel), public_path('storage/' . $rutaRel), storage_path('app/public/' . $rutaRel)];
                    foreach ($candidates as $p) {
                        if (file_exists($p)) {
                            $exists = true;
                            break;
                        }
                    }
                }

                if ($exists) {
                    $a['url_pdf_contrato'] = route('admin.alquileres.descargar-contrato', ['id' => $a['id']]);
                } else {
                    $a['url_pdf_contrato'] = '';
                }

                return $a;
            });

        return response()->json([
            'alquileres' => $alquileres,
            'total' => $paginados->total(),
            'currentPage' => $paginados->currentPage(),
            'totalPages' => $paginados->lastPage(),
            'from' => $paginados->firstItem(),
            'to' => $paginados->lastItem(),
        ]);
    }

    /**
     * Aprobar alquiler (TRANSACTION - touches 2 tables)
     */
    public function aprobar($id)
    {
        try {
            DB::beginTransaction();

            $admin = Auth::user();
            $adminId = $admin->id_usuario ?? $admin->id ?? null;

            // Obtener el alquiler para saber la propiedad
            $alquiler = DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->first();

            if (!$alquiler) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => 'Alquiler no encontrado']);
            }

            $propiedad = DB::table('tbl_propiedad')->find($alquiler->id_propiedad_fk);
            $inquilino = DB::table('tbl_usuario')->find($alquiler->id_inquilino_fk);
            $estadoPropiedadAnterior = $propiedad->estado_propiedad ?? 'publicada';

            // Actualizar alquiler a activo
            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->update([
                    'estado_alquiler' => 'activo',
                    'id_admin_aprueba_fk' => $adminId,
                    'aprobado_alquiler' => now()
                ]);

            // Actualizar propiedad a alquilada
            DB::table('tbl_propiedad')
                ->where('id_propiedad', $alquiler->id_propiedad_fk)
                ->update([
                    'estado_propiedad' => 'alquilada'
                ]);

            $this->generarCuotasAlAprobar($alquiler);
            
            // Registrar pago automático de la primera cuota
            $this->registrarPagoPrimeraCuota($alquiler);
            
            // Generar contrato con PDF
            $this->generarContratoConPDF($alquiler);

            DB::commit();

            $inquilinoNombre = $inquilino->nombre_usuario ?? 'Inquilino';

            if ($propiedad && $propiedad->id_gestor_fk) {
                $this->actividadService->alquilerAprobado(
                    $propiedad->id_gestor_fk,
                    $id,
                    $propiedad->titulo_propiedad,
                    $inquilinoNombre
                );

                $this->actividadService->propiedadEstadoCambiado(
                    $propiedad->id_gestor_fk,
                    $propiedad->id_propiedad,
                    $propiedad->titulo_propiedad,
                    $estadoPropiedadAnterior,
                    'alquilada',
                    'Admin'
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Descargar el contrato asociado a un alquiler (si existe).
     * Devuelve un attachment si el fichero es local, o redirige a la URL si es remota.
     */
    public function descargarContrato($id)
    {
        $contrato = DB::table('tbl_contrato')
            ->where('id_alquiler_fk', $id)
            ->first();

        if (!$contrato || empty($contrato->url_pdf_contrato)) {
            return abort(404, 'Contrato no encontrado');
        }

        $url = $contrato->url_pdf_contrato;

        // Si es una URL absoluta
        if (preg_match('#^https?://#i', $url)) {
            $componentes = parse_url($url);
            $hostRemoto = $componentes['host'] ?? null;
            $requestHost = request()->getHost();

            if ($hostRemoto && $hostRemoto === $requestHost) {
                $ruta = ltrim($componentes['path'] ?? '', '/\\');
                $rutaCompleta = public_path($ruta);
                if (File::exists($rutaCompleta)) {
                    return response()->download($rutaCompleta, basename($rutaCompleta));
                }
            }

            // remota o no encontrada localmente: redirigimos
            return redirect()->away($url);
        }

        // Ruta relativa
        $rutaRelativa = ltrim($url, '/\\');
        $rutaCompleta = public_path($rutaRelativa);
        if (File::exists($rutaCompleta)) {
            return response()->download($rutaCompleta, basename($rutaCompleta));
        }

        return abort(404, 'Fichero de contrato no disponible');
    }

    // Debug helper: return contrato DB row and file existence for given alquiler id
    public function contratoDebug($id)
    {
        $contrato = DB::table('tbl_contrato')->where('id_alquiler_fk', $id)->first();

        if (!$contrato) {
            return response()->json([
                'exists' => false,
                'message' => 'No se encontró fila en tbl_contrato para id_alquiler_fk = ' . $id,
            ], 200);
        }

        $url = $contrato->url_pdf_contrato ?? '';
        $localPath = null;
        $fileExists = false;
        $checkedPaths = [];

        if ($url) {
            // Normalize leading slash
            $ruta = ltrim($url, '/');

            // Candidate paths to check
            $candidates = [
                public_path($ruta), // e.g. public/contratos/contrato_3.pdf
                public_path('storage/' . $ruta), // e.g. public/storage/contratos/contrato_3.pdf (when using storage:link)
                storage_path('app/public/' . $ruta), // e.g. storage/app/public/contratos/contrato_3.pdf
            ];

            foreach ($candidates as $p) {
                $checkedPaths[] = $p;
                if (file_exists($p)) {
                    $localPath = $p;
                    $fileExists = true;
                    break;
                }
            }
        }

        return response()->json([
            'exists' => true,
            'contrato' => $contrato,
            'url_pdf_contrato' => $url,
            'checked_paths' => $checkedPaths,
            'local_path_found' => $localPath,
            'file_exists' => $fileExists,
        ], 200);
    }

    /**
     * Rechazar alquiler (single table, no transaction)
     */
    public function rechazar($id)
    {
        try {
            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->update([
                    'estado_alquiler' => 'rechazado'
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Crear nuevo alquiler (TRANSACTION - touches 2 tables)
     */
    public function crear(Request $request)
    {
        $datos = $request->validate([
            'id_propiedad' => 'required|exists:tbl_propiedad,id_propiedad',
            'id_inquilino' => 'required|exists:tbl_usuario,id_usuario',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'precio' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Evitar crear alquiler sobre una propiedad no disponible.
            $propiedad = DB::table('tbl_propiedad')
                ->where('id_propiedad', $datos['id_propiedad'])
                ->first();

            if (!$propiedad || !in_array($propiedad->estado_propiedad, ['publicada', 'activo'])) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'La propiedad no esta disponible para crear un alquiler.'
                    ], 422);
                }

                return back()
                    ->withErrors(['id_propiedad' => 'La propiedad no esta disponible para crear un alquiler.'])
                    ->withInput();
            }

            $existeAlquilerAbierto = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $datos['id_propiedad'])
                ->whereIn('estado_alquiler', ['pendiente', 'activo'])
                ->exists();

            if ($existeAlquilerAbierto) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Ya existe un alquiler pendiente o activo para esta propiedad.'
                    ], 422);
                }

                return back()
                    ->withErrors(['id_propiedad' => 'Ya existe un alquiler pendiente o activo para esta propiedad.'])
                    ->withInput();
            }

            // Crear alquiler
            $alquilerId = DB::table('tbl_alquiler')->insertGetId([
                'id_propiedad_fk' => $datos['id_propiedad'],
                'id_inquilino_fk' => $datos['id_inquilino'],
                'fecha_inicio_alquiler' => $datos['fecha_inicio'],
                'fecha_fin_alquiler' => $datos['fecha_fin'] ?? null,
                'estado_alquiler' => 'pendiente',
                'aprobado_alquiler' => null,
                'creado_alquiler' => now(),
                'actualizado_alquiler' => now()
            ]);

            // Crear pago fianza
            $fianza = $datos['precio'] * 2;
            DB::table('tbl_pago')->insert([
                'id_alquiler_fk' => $alquilerId,
                'id_pagador_fk' => $datos['id_inquilino'],
                'tipo_pago' => 'fianza',
                'importe_pago' => $fianza,
                'estado_pago' => 'pendiente',
                'referencia_pago' => 'FZ-' . $alquilerId . '-' . now()->format('Ymd'),
                'creado_pago' => now(),
                'actualizado_pago' => now()
            ]);

            DB::commit();

            $inquilinoNombre = DB::table('tbl_usuario')
                ->where('id_usuario', $datos['id_inquilino'])
                ->value('nombre_usuario') ?? 'Inquilino';

            if ($propiedad->id_gestor_fk) {
                $this->actividadService->alquilerCreado(
                    $propiedad->id_gestor_fk,
                    $datos['id_propiedad'],
                    $propiedad->titulo_propiedad,
                    $inquilinoNombre
                );
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'id_alquiler' => $alquilerId]);
            }

            return redirect('/admin/alquileres')->with('success', 'Alquiler creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }

            return back()
                ->withErrors(['general' => 'No se pudo crear el alquiler.'])
                ->withInput();
        }
    }

    /**
     * Actualizar un alquiler existente
     */
    public function actualizar(Request $request, $id)
    {
        $alquilerExistente = DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->first();

        if (!$alquilerExistente) {
            abort(404);
        }

        $datos = $request->validate([
            'id_propiedad' => 'required|exists:tbl_propiedad,id_propiedad',
            'id_inquilino' => 'required|exists:tbl_usuario,id_usuario',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'precio' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->update([
                    'id_propiedad_fk' => $datos['id_propiedad'],
                    'id_inquilino_fk' => $datos['id_inquilino'],
                    'fecha_inicio_alquiler' => $datos['fecha_inicio'],
                    'fecha_fin_alquiler' => $datos['fecha_fin'] ?? null,
                    'actualizado_alquiler' => now(),
                ]);

            DB::table('tbl_pago')
                ->where('id_alquiler_fk', $id)
                ->where('tipo_pago', 'fianza')
                ->update([
                    'id_pagador_fk' => $datos['id_inquilino'],
                    'importe_pago' => $datos['precio'] * 2,
                    'referencia_pago' => 'FZ-' . $id . '-' . now()->format('Ymd'),
                    'actualizado_pago' => now(),
                ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'id_alquiler' => $id]);
            }

            return redirect('/admin/alquileres')->with('success', 'Alquiler actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }

            return back()->withErrors(['general' => 'No se pudo actualizar el alquiler.'])->withInput();
        }
    }

    /**
     * Eliminar un alquiler y sus dependencias
     */
    public function eliminar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $alquiler = DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->first();

            if (!$alquiler) {
                DB::rollBack();

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Alquiler no encontrado.'], 404);
                }

                return redirect('/admin/alquileres')->with('error', 'Alquiler no encontrado.');
            }

            $propiedadEliminar = DB::table('tbl_propiedad')->find($alquiler->id_propiedad_fk);
            $estadoPropiedadAnterior = $propiedadEliminar->estado_propiedad ?? null;

            DB::table('tbl_pago')
                ->where('id_alquiler_fk', $id)
                ->delete();

            DB::table('tbl_contrato')
                ->where('id_alquiler_fk', $id)
                ->delete();

            if (Schema::hasTable('tbl_alquiler_cuota')) {
                DB::table('tbl_alquiler_cuota')
                    ->where('id_alquiler_fk', $id)
                    ->delete();
            }

            DB::table('tbl_gasto')
                ->where('id_alquiler_fk', $id)
                ->delete();

            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->delete();

            $hayOtroAlquilerActivo = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $alquiler->id_propiedad_fk)
                ->whereIn('estado_alquiler', ['pendiente', 'activo'])
                ->exists();

            if (!$hayOtroAlquilerActivo) {
                DB::table('tbl_propiedad')
                    ->where('id_propiedad', $alquiler->id_propiedad_fk)
                    ->update([
                        'estado_propiedad' => 'publicada',
                        'actualizado_propiedad' => now(),
                    ]);

                if ($propiedadEliminar && $propiedadEliminar->id_gestor_fk) {
                    $this->actividadService->propiedadEstadoCambiado(
                        $propiedadEliminar->id_gestor_fk,
                        $alquiler->id_propiedad_fk,
                        $propiedadEliminar->titulo_propiedad,
                        $estadoPropiedadAnterior,
                        'publicada',
                        'Admin'
                    );
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect('/admin/alquileres')->with('success', 'Alquiler eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }

            return redirect('/admin/alquileres')->with('error', 'No se pudo eliminar el alquiler.');
        }
    }

    private function generarCuotasAlAprobar(object $alquiler): void
    {
        if (!Schema::hasTable('tbl_alquiler_cuota')) {
            return;
        }

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $alquiler->id_propiedad_fk)
            ->select('precio_propiedad')
            ->first();

        $importeBase = round((float) ($propiedad->precio_propiedad ?? 0), 2);
        if ($importeBase <= 0) {
            return;
        }

        $inicio = Carbon::parse((string) $alquiler->fecha_inicio_alquiler)->startOfMonth();
        $limite = $alquiler->fecha_fin_alquiler
            ? Carbon::parse((string) $alquiler->fecha_fin_alquiler)->startOfMonth()
            : $inicio->copy()->addMonths(11);

        if ($limite->lessThan($inicio)) {
            return;
        }

        $diaVencimiento = Carbon::parse((string) $alquiler->fecha_inicio_alquiler)->day;

        $cursor = $inicio->copy();
        while ($cursor->lessThanOrEqualTo($limite)) {
            $ultimoDiaMes = (int) $cursor->copy()->endOfMonth()->day;
            $dia = min($diaVencimiento, $ultimoDiaMes);
            $fechaVencimiento = $cursor->copy()->day($dia)->toDateString();

            AlquilerCuota::firstOrCreate(
                [
                    'id_alquiler_fk' => (int) $alquiler->id_alquiler,
                    'mes_cuota' => $cursor->copy()->toDateString(),
                ],
                [
                    'importe_base' => $importeBase,
                    'estado' => 'pendiente',
                    'fecha_vencimiento' => $fechaVencimiento,
                    'pagado_en' => null,
                ]
            );

            $cursor->addMonth();
        }
    }

    private function registrarPagoPrimeraCuota(object $alquiler): void
    {
        if (!Schema::hasTable('tbl_alquiler_cuota')) {
            return;
        }

        // Obtener la primera cuota (más antigua)
        $primeraCuota = AlquilerCuota::where('id_alquiler_fk', (int) $alquiler->id_alquiler)
            ->orderBy('mes_cuota', 'asc')
            ->first();

        if (!$primeraCuota) {
            return;
        }

        // Obtener el precio de la propiedad
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $alquiler->id_propiedad_fk)
            ->select('precio_propiedad')
            ->first();

        $importeBase = round((float) ($propiedad->precio_propiedad ?? 0), 2);
        if ($importeBase <= 0) {
            return;
        }

        // Registrar pago de la primera cuota con estado 'pagado'
        Pago::create([
            'id_pagador_fk' => $alquiler->id_inquilino_fk,
            'id_alquiler_fk' => $alquiler->id_alquiler,
            'id_alquiler_cuota_fk' => $primeraCuota->id_alquiler_cuota,
            'tipo_pago' => 'alquiler',
            'concepto_pago' => 'Cuota alquiler ' . Carbon::parse((string) $primeraCuota->mes_cuota)->format('m/Y'),
            'importe_pago' => $importeBase,
            'estado_pago' => 'pagado',
            'referencia_pago' => 'ALQ-' . $primeraCuota->id_alquiler_cuota . '-' . now()->format('YmdHis'),
            'fecha_confirmacion_pago' => now(),
            'creado_pago' => now(),
            'actualizado_pago' => now(),
        ]);

        // Actualizar el estado de la cuota a 'pagado'
        $primeraCuota->update([
            'estado' => 'pagado',
            'pagado_en' => now(),
        ]);
    }

    private function generarContratoConPDF(object $alquiler): void
    {
        // Obtener datos completos del alquiler
        $datosAlquiler = DB::table('tbl_alquiler as a')
            ->join('tbl_usuario as arrendador', 'a.id_arrendador_fk', '=', 'arrendador.id_usuario')
            ->join('tbl_usuario as inquilino', 'a.id_inquilino_fk', '=', 'inquilino.id_usuario')
            ->join('tbl_propiedad as p', 'a.id_propiedad_fk', '=', 'p.id_propiedad')
            ->where('a.id_alquiler', $alquiler->id_alquiler)
            ->select(
                'a.id_alquiler',
                'a.fecha_inicio_alquiler',
                'a.fecha_fin_alquiler',
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador',
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'p.titulo_propiedad',
                'p.direccion_propiedad',
                'p.ciudad_propiedad',
                'p.precio_propiedad'
            )
            ->first();

        if (!$datosAlquiler) {
            return;
        }

        try {
            // Preparar datos para PdfMonkey
            $pdfMonkey = new PdfMonkeyService();
            
            if (!$pdfMonkey->estaConfigurado()) {
                return;
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

            // Generar PDF sincronizado
            $respuesta = $pdfMonkey->crearDocumentoSincronizado(
                $datosContrato,
                $pdfMonkey->construirMeta([], 'contrato_' . $alquiler->id_alquiler . '.pdf')
            );

            if (isset($respuesta['document_card']['download_url'])) {
                $urlPdf = $respuesta['document_card']['download_url'];

                // Crear registro de contrato
                $datosContratoBD = [
                    'id_alquiler_fk' => $alquiler->id_alquiler,
                    'url_pdf_contrato' => $urlPdf ?? '',
                    'estado_contrato' => 'pendiente',
                    'creado_contrato' => Carbon::now(),
                ];

                if (Schema::hasColumn('tbl_contrato', 'actualizado_contrato')) {
                    $datosContratoBD['actualizado_contrato'] = Carbon::now();
                }

                DB::table('tbl_contrato')->insertOrIgnore($datosContratoBD);

                return;
            }

            if (isset($respuesta['document']) && isset($respuesta['document']['id'])) {
                $idDocumentoPdf = $respuesta['document']['id'];
                $urlPdf = $pdfMonkey->obtenerUrlDescarga($idDocumentoPdf);

                // Crear registro de contrato
                $datosContratoBD = [
                    'id_alquiler_fk' => $alquiler->id_alquiler,
                    'url_pdf_contrato' => $urlPdf ?? '',
                    'estado_contrato' => 'pendiente',
                    'creado_contrato' => Carbon::now(),
                ];

                if (Schema::hasColumn('tbl_contrato', 'actualizado_contrato')) {
                    $datosContratoBD['actualizado_contrato'] = Carbon::now();
                }

                DB::table('tbl_contrato')->insertOrIgnore($datosContratoBD);
            }
        } catch (\Exception $e) {
            Log::error('Error al generar PDF del contrato: ' . $e->getMessage());
            // No romper la transacción si falla el PDF
        }
    }

    private function obtenerSelectFotoPropiedad(): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'foto_propiedad')) {
            return 'tbl_propiedad.foto_propiedad as foto_propiedad';
        }

        return 'NULL as foto_propiedad';
    }
}
