<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use App\Models\CodigoGestor;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PropiedadController extends Controller
{
    public function inicio(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = $this->obtenerArrendadorBase($arrendadorId);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        $propiedades = $this->consultarPropiedades($arrendadorId, $columnaPrecio)->paginate(10);

        $totales = $this->obtenerTotales($arrendadorId);

        // Comprobar el estado del límite de la suscripción del arrendador
        $infoLimite = $this->comprobarLimiteSuscripcion($arrendadorId);

        return view('arrendador.propiedades', [
            'arrendador' => $arrendador,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'propiedades' => $propiedades,
            'totales' => $totales,
            'arrendadorId' => $arrendadorId,
            'limiteAlcanzado' => $infoLimite['limiteAlcanzado'],
            'maxPropiedades' => $infoLimite['maxPropiedades'],
            'nombrePlan' => $infoLimite['nombrePlan'],
        ]);
    }

    public function datosPropiedades(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();
        $propiedades = $this->consultarPropiedades($arrendadorId, $columnaPrecio)->paginate(10);
        $totales = $this->obtenerTotales($arrendadorId);

        return response()->json([
            'propiedades' => $propiedades,
            'totales' => $totales,
            'arrendadorId' => $arrendadorId,
        ]);
    }

    public function guardar(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $propiedadId = (int) $request->input('id_propiedad', 0);
        $esEdicion = $propiedadId > 0;
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();
        $imagenPrincipalIndice = (int) $request->input('imagen-principal-indice', -1);

        $datos = $request->validate([
            'titulo_propiedad' => ['required', 'string', 'max:150'],
            'tipo_propiedad' => ['required', 'in:piso,casa,estudio,habitacion'],
            'calle_propiedad' => ['required', 'string', 'max:150'],
            'numero_propiedad' => ['required', 'string', 'max:20'],
            'piso_propiedad' => ['nullable', 'string', 'max:20'],
            'puerta_propiedad' => ['nullable', 'string', 'max:20'],
            'ciudad_propiedad' => ['required', 'string', 'max:100'],
            'codigo_postal_propiedad' => ['required', 'string', 'max:10'],
            'habitaciones_propiedad' => ['nullable', 'string', 'max:20'],
            'banos_propiedad' => ['nullable', 'integer', 'min:0'],
            'metros_cuadrados_propiedad' => ['nullable', 'integer', 'min:0'],
            'ascensor_propiedad' => ['nullable', 'boolean'],
            'amueblado_propiedad' => ['nullable', 'boolean'],
            'piscina_propiedad' => ['nullable', 'boolean'],
            'terraza_propiedad' => ['nullable', 'boolean'],
            'garaje_propiedad' => ['nullable', 'boolean'],
            'aire_acondicionado_propiedad' => ['nullable', 'boolean'],
            'calefaccion_propiedad' => ['nullable', 'boolean'],
            'trastero_propiedad' => ['nullable', 'boolean'],
            'adicional_propiedad' => ['nullable', 'string', 'max:255'],
            'precio_propiedad' => ['required', 'numeric', 'min:0'],
            'descripcion_propiedad' => ['nullable', 'string'],
            'imagenes_propiedad' => ['nullable', 'array', 'max:10'],
            'imagenes_propiedad.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $datosPropiedad = [
            'id_arrendador_fk' => $arrendadorId,
            'id_gestor_fk' => $arrendadorId,
            'titulo_propiedad' => $datos['titulo_propiedad'],
            'tipo_propiedad' => $datos['tipo_propiedad'],
            'calle_propiedad' => $datos['calle_propiedad'],
            'numero_propiedad' => $datos['numero_propiedad'],
            'piso_propiedad' => $datos['piso_propiedad'] ?? null,
            'puerta_propiedad' => $datos['puerta_propiedad'] ?? null,
            'ciudad_propiedad' => $datos['ciudad_propiedad'],
            'codigo_postal_propiedad' => $datos['codigo_postal_propiedad'],
            'habitaciones_propiedad' => $datos['habitaciones_propiedad'] ?? null,
            'banos_propiedad' => $datos['banos_propiedad'] ?? null,
            'metros_cuadrados_propiedad' => $datos['metros_cuadrados_propiedad'] ?? null,
            'ascensor_propiedad' => (bool) ($datos['ascensor_propiedad'] ?? false),
            'amueblado_propiedad' => (bool) ($datos['amueblado_propiedad'] ?? false),
            'piscina_propiedad' => (bool) ($datos['piscina_propiedad'] ?? false),
            'terraza_propiedad' => (bool) ($datos['terraza_propiedad'] ?? false),
            'garaje_propiedad' => (bool) ($datos['garaje_propiedad'] ?? false),
            'aire_acondicionado_propiedad' => (bool) ($datos['aire_acondicionado_propiedad'] ?? false),
            'calefaccion_propiedad' => (bool) ($datos['calefaccion_propiedad'] ?? false),
            'trastero_propiedad' => (bool) ($datos['trastero_propiedad'] ?? false),
            'adicional_propiedad' => $datos['adicional_propiedad'] ?? null,
            $columnaPrecio => $datos['precio_propiedad'],
            'descripcion_propiedad' => $datos['descripcion_propiedad'] ?? null,
            'actualizado_propiedad' => Carbon::now(),
        ];

        // Validar límite del plan de suscripción en la creación de nuevas propiedades
        if (!$esEdicion) {
            $checkPlan = $this->comprobarLimiteSuscripcion($arrendadorId);
            if ($checkPlan['limiteAlcanzado']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $checkPlan['mensaje'],
                    ], 422);
                }

                return redirect()
                    ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                    ->with('error', $checkPlan['mensaje']);
            }
        }

        DB::beginTransaction();

        try {
            if ($propiedadId > 0) {
                $existe = DB::table('tbl_propiedad')
                    ->where('id_propiedad', $propiedadId)
                    ->where('id_arrendador_fk', $arrendadorId)
                    ->exists();

                if (!$existe) {
                    DB::rollBack();

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se encontró la propiedad para editar.',
                        ], 404);
                    }

                    return redirect()
                        ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                        ->with('error', 'No se encontró la propiedad para editar.');
                }

                DB::table('tbl_propiedad')
                    ->where('id_propiedad', $propiedadId)
                    ->where('id_arrendador_fk', $arrendadorId)
                    ->update($datosPropiedad);
            } else {
                $datosPropiedad['creado_propiedad'] = Carbon::now();
                $datosPropiedad['estado_propiedad'] = 'borrador';
                $propiedadId = (int) DB::table('tbl_propiedad')->insertGetId($datosPropiedad);
            }

            $imagenesSubidas = $request->file('imagenes_propiedad', []);
            \Log::info('Imagenes recibidas: ', ['imagenes' => $request->allFiles()]);
            if (!empty($imagenesSubidas)) {
                $totalActual = (int) DB::table('tbl_fotos')
                    ->where('id_propiedad_fk', $propiedadId)
                    ->count();

                if (($totalActual + count($imagenesSubidas)) > 10) {
                    DB::rollBack();

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No puedes superar 10 imágenes por propiedad.',
                        ], 422);
                    }

                    return redirect()
                        ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                        ->with('error', 'No puedes superar 10 imágenes por propiedad.');
                }

                foreach ($imagenesSubidas as $indice => $imagenSubida) {
                    $nombreArchivo = now()->format('YmdHis') . '_' . $propiedadId . '_' . $indice . '_' . uniqid() . '.' . $imagenSubida->getClientOriginalExtension();
                    $rutaGuardada = 'propiedades/' . $nombreArchivo;
                    $imagenSubida->move(public_path('img/propiedades'), $nombreArchivo);

                    $esPrincipal = ($indice === $imagenPrincipalIndice);

                    DB::table('tbl_fotos')->insert([
                        'id_propiedad_fk' => $propiedadId,
                        'ruta_foto' => $rutaGuardada,
                        'es_principal_foto' => $esPrincipal,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            logger()->error('Error al guardar propiedad del arrendador', [
                'arrendador_id' => $arrendadorId,
                'propiedad_id' => $propiedadId,
                'error' => $exception->getMessage(),
                'archivo' => $exception->getFile(),
                'linea' => $exception->getLine(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo guardar la propiedad en este momento.',
                    'debug' => config('app.debug') ? $exception->getMessage() : null,
                ], 500);
            }

            return redirect()
                ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
                ->with('error', 'No se pudo guardar la propiedad en este momento.');
        }

        $mensaje = $esEdicion ? 'Propiedad actualizada correctamente.' : 'Propiedad creada correctamente.';

        $propiedadActualizada = DB::table('tbl_propiedad as p')
            ->where('p.id_propiedad', $propiedadId)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.id_propiedad',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'p.ciudad_propiedad',
                'p.codigo_postal_propiedad',
                DB::raw("p.{$columnaPrecio} as precio_propiedad"),
                'p.estado_propiedad'
            )
            ->first();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'arrendador_id' => $arrendadorId,
                'propiedad' => $propiedadActualizada,
            ]);
        }

        return redirect()
            ->route('arrendador.propiedades', ['arrendador_id' => $arrendadorId])
            ->with('success', $mensaje);
    }

    public function mostrar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $propiedad = DB::table('tbl_propiedad as p')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'p.id_arrendador_fk')
            ->where('p.id_propiedad', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.*',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'arrendador.nombre_usuario as nombre_arrendador',
                'arrendador.email_usuario as email_arrendador'
            )
            ->first();

        if (!$propiedad) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        $alquilerActivo = DB::table('tbl_alquiler as a')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('a.id_propiedad_fk', $id)
            ->where('a.estado_alquiler', 'activo')
            ->select('a.*', 'inquilino.nombre_usuario as nombre_inquilino', 'inquilino.email_usuario as email_inquilino')
            ->first();

        $fotos = DB::table('tbl_fotos')
            ->where('id_propiedad_fk', $id)
            ->orderBy('es_principal_foto', 'desc')
            ->orderBy('orden')
            ->get();

        return response()->json([
            'propiedad' => $propiedad,
            'alquiler_activo' => $alquilerActivo,
            'fotos' => $fotos,
        ]);
    }

    public function datosEdicion(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);
        $columnaPrecio = $this->obtenerColumnaPrecioPropiedad();

        $propiedad = $this->consultarPropiedadEditable($arrendadorId, $id, $columnaPrecio);

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la propiedad para editar.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'propiedad' => $propiedad,
        ]);
    }

    private function consultarPropiedades(int $arrendadorId, string $columnaPrecio)
    {
        return DB::table('tbl_propiedad as p')
        //Obtener inquilinos
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk, COUNT(*) as total_inquilinos FROM tbl_alquiler GROUP BY id_propiedad_fk) as alq'), 'alq.id_propiedad_fk', '=', 'p.id_propiedad')
        //Obtener nombre del gestor 
            ->leftJoin('tbl_usuario as g', 'g.id_usuario', '=', 'p.id_gestor_fk')
        //Obtener total de incidencias
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk, COUNT(*) as total_incidencias FROM tbl_incidencia GROUP BY id_propiedad_fk) as inc'), 'inc.id_propiedad_fk', '=', 'p.id_propiedad')
        //Obtener cuotas de alquiler atrasadas y pendientes
            ->leftJoin(DB::raw('(
                SELECT
                    a.id_propiedad_fk,
                    SUM(CASE WHEN ac.estado = "atrasado" THEN 1 ELSE 0 END) as cuotas_atrasadas,
                    SUM(CASE WHEN ac.estado = "pendiente" THEN 1 ELSE 0 END) as cuotas_pendientes
                FROM tbl_alquiler_cuota ac
                JOIN tbl_alquiler a
                    ON a.id_alquiler = ac.id_alquiler_fk
                GROUP BY a.id_propiedad_fk
            ) as cuotas'), 'cuotas.id_propiedad_fk', '=', 'p.id_propiedad')
        //QUEDA AÑADIR GASTOS ATRASADOS Y PENDIENTES
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.id_propiedad',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'p.ciudad_propiedad',
                'p.codigo_postal_propiedad',
                'p.estado_propiedad',
                DB::raw("p.{$columnaPrecio} as precio_propiedad"),
                'alq.total_inquilinos',
                'p.creado_propiedad',
                'g.nombre_usuario as nombre_gestor',
                'inc.total_incidencias',
                'cuotas.cuotas_atrasadas',
                'cuotas.cuotas_pendientes'
            )
            ->orderByDesc('p.creado_propiedad');
    }

    private function consultarPropiedadEditable(int $arrendadorId, int $propiedadId, string $columnaPrecio)
    {
        return DB::table('tbl_propiedad as p')
            ->where('p.id_propiedad', $propiedadId)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.id_propiedad',
                'p.titulo_propiedad',
                'p.tipo_propiedad',
                'p.calle_propiedad',
                'p.numero_propiedad',
                'p.piso_propiedad',
                'p.puerta_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'p.ciudad_propiedad',
                'p.codigo_postal_propiedad',
                'p.latitud_propiedad',
                'p.longitud_propiedad',
                'p.descripcion_propiedad',
                DB::raw("p.{$columnaPrecio} as precio_propiedad"),
                'p.estado_propiedad',
                'p.banos_propiedad',
                'p.metros_cuadrados_propiedad',
                'p.ascensor_propiedad',
                'p.amueblado_propiedad',
                'p.piscina_propiedad',
                'p.terraza_propiedad',
                'p.garaje_propiedad',
                'p.aire_acondicionado_propiedad',
                'p.calefaccion_propiedad',
                'p.trastero_propiedad',
                'p.adicional_propiedad'
            )
            ->first();
    }

    private function obtenerTotales(int $arrendadorId): array
    {
        $totalPropiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->count();

        $publicadas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'publicada')
            ->count();

        $alquiladas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'alquilada')
            ->count();

        $inactivas = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('estado_propiedad', 'inactiva')
            ->count();

        return compact('totalPropiedades', 'publicadas', 'alquiladas', 'inactivas');
    }

    private function obtenerIdArrendador(Request $request): int
    {
        if (Auth::check()) {
            $usuarioAutenticado = Auth::user();

            if ($usuarioAutenticado instanceof Usuario && $usuarioAutenticado->roles()->where('slug_rol', 'arrendador')->exists()) {
                return (int) ($usuarioAutenticado->id_usuario ?? $usuarioAutenticado->id ?? 0);
            }
        }

        $arrendadorId = (int) $request->input('arrendador_id', 0);

        if ($arrendadorId <= 0) {
            $arrendadorId = (int) $request->query('arrendador_id', 0);
        }

        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        return (int) DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select('u.id_usuario', DB::raw('COUNT(*) as total_propiedades'))
            ->orderByDesc('total_propiedades')
            ->orderBy('u.id_usuario')
            ->value('u.id_usuario');
    }

    private function obtenerArrendadorBase(int $arrendadorId)
    {
        return DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();
    }

    private function obtenerColumnaPrecioPropiedad(): string
    {

        return 'precio_propiedad';
    }

    private function normalizarGastos(?string $gastos): ?string
    {
        if ($gastos === null || trim($gastos) === '') {
            return null;
        }

        $decodificado = json_decode($gastos, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decodificado);
        }

        return $gastos;
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

    private function mapearDireccionParaGuardar(string $direccion): array
    {
        $direccionLimpia = trim($direccion);

        // Si la tabla tiene columna direccion_propiedad directa, usarla
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return ['direccion_propiedad' => $direccionLimpia];
        }

        // Si no, mapear a calle_propiedad y numero_propiedad
        $partes = preg_split('/\s+/', $direccionLimpia);
        $numero = '';

        if (!empty($partes)) {
            $ultimo = end($partes);
            if (preg_match('/^\d+$/', (string) $ultimo)) {
                $numero = (string) $ultimo;
                array_pop($partes);
            }
        }

        $calle = trim(implode(' ', $partes));
        if ($calle === '') {
            $calle = $direccionLimpia;
        }

        $datos = [];
        
        // Asegurar que siempre se incluyen estos campos
        $datos['calle_propiedad'] = $calle;
        $datos['numero_propiedad'] = $numero !== '' ? $numero : 'S/N';

        return $datos;
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }

    //MODAL CONFIGURACION GESTOR

    //Ver permisos del gestor
    public function getPermisosGestor($idPropiedad){
        $permisos = DB::table('tbl_propiedad as prop')

        ->leftJoin('tbl_usuario as u', 'u.id_usuario', '=', 'prop.id_gestor_fk')

        ->leftJoin('tbl_propiedad_permisos as p', function ($join) {
            $join->on('p.id_propiedad_fk', '=', 'prop.id_propiedad')
                 ->on('p.id_gestor_fk', '=', 'prop.id_gestor_fk');
        })

        ->where('prop.id_propiedad', $idPropiedad)

        ->select(
            'u.id_usuario as id_gestor',
            'u.nombre_usuario as nombre_gestor',
            'u.email_usuario as email_gestor',

            'p.incidencias',
            'p.gastos',
            'p.chat',
            'p.editar_propiedad'
        )

        ->first();
        //Si no hay permisos específicos se devuelve false, por si no están creados aún
        return response()->json([
            'id_gestor' => $permisos?->id_gestor ?? null,
            'nombre_gestor' => $permisos?->nombre_gestor ?? null,
            'email_gestor' => $permisos?->email_gestor ?? null,
            'incidencias' => (bool) ($permisos?->incidencias ?? false),
            'gastos' => (bool) ($permisos?->gastos ?? false),
            'chat' => (bool) ($permisos?->chat ?? false),
            'editar_propiedad' => (bool) ($permisos?->editar_propiedad ?? false),
        ]);
    }

    //Actualizar permisos del gestor
    public function updatePermisosGestor(Request $request, $idPropiedad){
        // Obtener id del gestor desde la petición si viene, si no usar el id_gestor_fk de la propiedad
        $idGestor = $request->input('gestor_id');
        if (empty($idGestor)) {
            $idGestor = DB::table('tbl_propiedad')
                ->where('id_propiedad', $idPropiedad)
                ->value('id_gestor_fk');
        }

        $codigoGestor = trim((string) $request->input('codigo_gestor', ''));
        $codigoGestor = $codigoGestor === '' ? null : $codigoGestor;

        $request->validate([
            'gestor_id' => ['nullable', 'integer', 'min:1'],
            'codigo_gestor' => ['nullable', 'string', 'max:30'],
            'incidencias' => ['nullable', 'boolean'],
            'gastos' => ['nullable', 'boolean'],
            'chat' => ['nullable', 'boolean'],
            'editar_propiedad' => ['nullable', 'boolean'],
        ]);

        if (empty($idGestor)) {
            return response()->json(['success' => false, 'message' => 'Gestor no encontrado.'], 400);
        }

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $idPropiedad)
            ->where('id_arrendador_fk', $this->obtenerIdArrendador($request))
            ->first();

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        }

        $idGestor = (int) $idGestor;
        $gestorActualId = $propiedad->id_gestor_fk ? (int) $propiedad->id_gestor_fk : null;
        $gestorCambiado = $gestorActualId !== $idGestor;

        $gestorValido = DB::table('tbl_usuario')
            ->where('id_usuario', $idGestor)
            ->where('activo_usuario', true)
            ->exists();

        if (!$gestorValido) {
            return response()->json(['success' => false, 'message' => 'El gestor seleccionado no es válido.'], 422);
        }

        if ($gestorCambiado) {
            if ($codigoGestor === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes introducir el código del gestor para asignarlo.',
                    'requiere_desasignar' => false,
                ], 422);
            }

            $codigoValido = CodigoGestor::where('codigo_gestor', $codigoGestor)
                ->where('estado_codigo_gestor', 'activo')
                ->where('id_gestor_fk', $idGestor)
                ->exists();

            if (!$codigoValido) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código del gestor no es correcto.',
                    'requiere_desasignar' => true,
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            // Actualizar id_gestor_fk en tbl_propiedad para que ambas vistas coincidan
            DB::table('tbl_propiedad')
                ->where('id_propiedad', $idPropiedad)
                ->update([
                    'id_gestor_fk' => $idGestor,
                    'actualizado_propiedad' => Carbon::now(),
                ]);

            // Usar la tabla de permisos vinculada a propiedad
            DB::table('tbl_propiedad_permisos')
                ->updateOrInsert(
                    [
                        'id_gestor_fk' => $idGestor,
                        'id_propiedad_fk' => $idPropiedad,
                    ],
                    [
                        'incidencias' => (bool) $request->incidencias,
                        'gastos' => (bool) $request->gastos,
                        'chat' => (bool) $request->chat,
                        'editar_propiedad' => (bool) $request->editar_propiedad,
                        'updated_at' => now(),
                    ]
                );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $gestorCambiado ? 'Gestor actualizado correctamente.' : 'Permisos actualizados correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el gestor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function desasignarGestor(Request $request, $idPropiedad)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $existe = DB::table('tbl_propiedad')
            ->where('id_propiedad', $idPropiedad)
            ->where('id_arrendador_fk', $arrendadorId)
            ->exists();

        if (!$existe) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        }

        DB::beginTransaction();

        try {
            DB::table('tbl_propiedad')
                ->where('id_propiedad', $idPropiedad)
                ->update([
                    'id_gestor_fk' => null,
                    'actualizado_propiedad' => Carbon::now(),
                ]);

            DB::table('tbl_propiedad_permisos')
                ->where('id_propiedad_fk', $idPropiedad)
                ->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Gestor desasignado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el gestor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una propiedad del portafolio del arrendador de forma permanente.
     * Solo permite eliminar si la propiedad no está en alquiler (alquilada).
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function eliminar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        // 1. Obtener la propiedad y comprobar que pertenezca al arrendador autenticado
        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_arrendador_fk', $arrendadorId)
            ->select('id_propiedad', 'estado_propiedad')
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la propiedad especificada o no tienes permisos sobre ella.'
            ], 404);
        }

        // 2. Bloquear la eliminación si la propiedad se encuentra en alquiler activo
        if (isset($propiedad->estado_propiedad) && $propiedad->estado_propiedad === 'alquilada') {
            return response()->json([
                'success' => false,
                'message' => 'No es posible eliminar una propiedad que se encuentra actualmente en alquiler activo.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 3. Obtener relaciones hijas (alquileres, gastos y cuotas)
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

            // 4. Borrado en cascada seguro para evitar violaciones de claves foráneas
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

            // Eliminar incidencias de la propiedad
            DB::table('tbl_incidencia')
                ->where('id_propiedad_fk', $id)
                ->delete();

            if (Schema::hasTable('tbl_conversacion')) {
                DB::table('tbl_conversacion')
                    ->where('id_propiedad_fk', $id)
                    ->delete();
            }

            // Eliminar alquileres de la propiedad
            DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $id)
                ->delete();

            // Eliminar fotos asociadas de la propiedad
            DB::table('tbl_fotos')
                ->where('id_propiedad_fk', $id)
                ->delete();

            // Eliminar la propiedad definitivamente
            DB::table('tbl_propiedad')
                ->where('id_propiedad', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'La propiedad y todos sus datos relacionados han sido eliminados correctamente.'
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            \Log::error('Error al eliminar propiedad del Arrendador ID ' . $id . ': ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar eliminar la propiedad: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**

     * Comprueba si el arrendador ha alcanzado el límite de propiedades permitidas por su suscripción activa.
     *
     * @param int $arrendadorId
     * @return array
     */
    private function comprobarLimiteSuscripcion(int $arrendadorId): array
    {
        $hoy = Carbon::now()->toDateString();

        // 1. Obtener la suscripción activa del arrendador actual
        $suscripcionActiva = DB::table('tbl_suscripcion as sus')
            ->leftJoin('tbl_plan as plan', 'plan.id_plan', '=', 'sus.id_plan_fk')
            ->where('sus.id_usuario_fk', $arrendadorId)
            ->where('sus.estado_suscripcion', 'activa')
            ->where(function ($query) use ($hoy) {
                $query->whereNull('sus.fin_suscripcion')
                    ->orWhere('sus.fin_suscripcion', '>=', $hoy);
            })
            ->orderByDesc('sus.id_suscripcion')
            ->select(
                'sus.max_propiedades_suscripcion',
                'sus.plan_suscripcion',
                'plan.nombre_plan as nombre_plan'
            )
            ->first();

        // 2. Si no hay suscripción activa, bloqueamos por seguridad
        if (!$suscripcionActiva) {
            return [
                'limiteAlcanzado' => true,
                'maxPropiedades' => 0,
                'nombrePlan' => 'Sin plan activo',
                'mensaje' => 'No tienes una suscripción activa para publicar propiedades.',
            ];
        }

        $limite = (int) ($suscripcionActiva->max_propiedades_suscripcion ?? 0);

        // 3. Si el límite es 0 o menor, el plan no permite publicar ninguna propiedad
        if ($limite <= 0) {
            $nombrePlan = $suscripcionActiva->nombre_plan
                ?? ($suscripcionActiva->plan_suscripcion ? ucfirst($suscripcionActiva->plan_suscripcion) : 'actual');

            return [
                'limiteAlcanzado' => true,
                'maxPropiedades' => 0,
                'nombrePlan' => $nombrePlan,
                'mensaje' => 'El plan ' . $nombrePlan . ' no permite publicar propiedades.',
            ];
        }

        // 4. Contar el total global de propiedades creadas por el arrendador en su cuenta
        $totalPropiedades = (int) DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->count();

        $limiteAlcanzado = $totalPropiedades >= $limite;

        $nombrePlan = $suscripcionActiva->nombre_plan
            ?? ($suscripcionActiva->plan_suscripcion ? ucfirst($suscripcionActiva->plan_suscripcion) : 'actual');

        return [
            'limiteAlcanzado' => $limiteAlcanzado,
            'maxPropiedades' => $limite,
            'nombrePlan' => $nombrePlan,
            'totalPublicadas' => $totalPropiedades, // Cambiamos el valor de retorno para que contenga el total
            'mensaje' => 'Has alcanzado el límite de tu plan ' . $nombrePlan . ' (' . $limite . ' propiedades registradas). Inactiva o elimina alguna propiedad existente o mejora tu suscripción.',
        ];
    }
}

