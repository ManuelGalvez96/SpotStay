<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudArrendador;
use App\Models\SolicitudGestor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller
{
    public function index()
    {
        $solicitudesPendientes = $this->paginarColeccion(
            $this->obtenerSolicitudesCombinadas('pendiente', 'mes'),
            7,
            1
        );

        $aprobadas = $this->contarSolicitudesCombinadas('aprobada', 'mes');
        $rechazadas = $this->contarSolicitudesCombinadas('rechazada', 'mes');
        $totalSolicitudes = $this->contarSolicitudesCombinadas(null, 'all');
        $tiempoMedio = 4.2;

        $ultimasAprobadas = $this->obtenerSolicitudesCombinadas('aprobada', 'mes')
            ->take(3)
            ->values();

        $ultimasRechazadas = $this->obtenerSolicitudesCombinadas('rechazada', 'mes')
            ->take(3)
            ->values();

        return view('admin.solicitudes', compact(
            'solicitudesPendientes',
            'aprobadas',
            'rechazadas',
            'totalSolicitudes',
            'tiempoMedio',
            'ultimasAprobadas',
            'ultimasRechazadas'
        ));
    }

    public function aprobar(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $tipoSolicitud = $this->resolverTipoSolicitud($request->query('tipo', 'arrendador'));
            $idAdmin = $this->obtenerIdAdmin();

            if ($tipoSolicitud === 'gestor') {
                $solicitud = DB::table('tbl_solicitud_gestor')
                    ->where('id_solicitud_gestor', $id)
                    ->first();

                if (!$solicitud) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'La solicitud de gestor no existe.'
                    ], 404);
                }

                DB::table('tbl_solicitud_gestor')
                    ->where('id_solicitud_gestor', $id)
                    ->update([
                        'estado_solicitud_gestor' => 'aprobada',
                        'id_admin_revisa_fk' => $idAdmin,
                        'actualizado_solicitud_gestor' => Carbon::now()
                    ]);

                $this->asegurarRolUsuario((int) $solicitud->id_usuario_fk, 'gestor');
                $this->asegurarPerfilGestor((int) $solicitud->id_usuario_fk);
                $this->limpiarSesionesUsuario((int) $solicitud->id_usuario_fk);
            } else {
                $solicitud = DB::table('tbl_solicitud_arrendador')
                    ->where('id_solicitud_arrendador', $id)
                    ->first();

                if (!$solicitud) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'La solicitud de arrendador no existe.'
                    ], 404);
                }

                DB::table('tbl_solicitud_arrendador')
                    ->where('id_solicitud_arrendador', $id)
                    ->update([
                        'estado_solicitud_arrendador' => 'aprobada',
                        'id_admin_revisa_fk' => $idAdmin,
                        'actualizado_solicitud_arrendador' => Carbon::now()
                    ]);

                $this->asegurarRolUsuario((int) $solicitud->id_usuario_fk, 'arrendador');
                $this->limpiarSesionesUsuario((int) $solicitud->id_usuario_fk);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rechazar(Request $request, $id)
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($request->query('tipo', 'arrendador'));
        $idAdmin = $this->obtenerIdAdmin();

        if ($tipoSolicitud === 'gestor') {
            $actualizado = DB::table('tbl_solicitud_gestor')
                ->where('id_solicitud_gestor', $id)
                ->update([
                    'estado_solicitud_gestor' => 'rechazada',
                    'id_admin_revisa_fk' => $idAdmin,
                    'notas_solicitud_gestor' => $request->input('notas'),
                    'actualizado_solicitud_gestor' => Carbon::now()
                ]);

            if (!$actualizado) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud de gestor no existe.'
                ], 404);
            }
        } else {
            $actualizado = DB::table('tbl_solicitud_arrendador')
                ->where('id_solicitud_arrendador', $id)
                ->update([
                    'estado_solicitud_arrendador' => 'rechazada',
                    'id_admin_revisa_fk' => $idAdmin,
                    'notas_solicitud_arrendador' => $request->input('notas'),
                    'actualizado_solicitud_arrendador' => Carbon::now()
                ]);

            if (!$actualizado) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud de arrendador no existe.'
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada correctamente.'
        ]);
    }

    public function show(Request $request, $id)
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($request->query('tipo', 'arrendador'));

        if ($tipoSolicitud === 'gestor') {
            $solicitud = SolicitudGestor::with('usuario:id_usuario,nombre_usuario,email_usuario,telefono_usuario,creado_usuario')
                ->where('id_solicitud_gestor', $id)
                ->first();

            if (!$solicitud) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            return response()->json($this->formatearSolicitudGestor($solicitud));
        }

        $solicitud = SolicitudArrendador::with('usuario:id_usuario,nombre_usuario,email_usuario,telefono_usuario,creado_usuario')
            ->where('id_solicitud_arrendador', $id)
            ->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        return response()->json($this->formatearSolicitudArrendador($solicitud));
    }

    public function filtrar(Request $request)
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($request->input('tipo', 'all'));
        $solicitudes = $this->obtenerSolicitudesCombinadas(
            $request->input('estado'),
            $request->input('rango', 'mes'),
            $tipoSolicitud,
            $request->input('q'),
            $request->input('ciudad')
        );

        $solicitudesPaginadas = $this->paginarColeccion($solicitudes, 7, (int) $request->input('page', 1));

        return response()->json([
            'data' => $solicitudesPaginadas->items(),
            'total' => $solicitudesPaginadas->total(),
            'current_page' => $solicitudesPaginadas->currentPage(),
            'last_page' => $solicitudesPaginadas->lastPage(),
            'per_page' => $solicitudesPaginadas->perPage(),
            'from' => $solicitudesPaginadas->firstItem(),
            'to' => $solicitudesPaginadas->lastItem()
        ]);
    }

    public function getKpisStatistics(Request $request)
    {
        $tipoSolicitud = $this->resolverTipoSolicitud($request->input('tipo', 'all'));
        $solicitudes = $this->obtenerSolicitudesCombinadas(
            null,
            $request->input('rango', 'mes'),
            $tipoSolicitud,
            $request->input('q'),
            $request->input('ciudad')
        );

        return response()->json([
            'total' => $solicitudes->count(),
            'pendientes' => $solicitudes->where('estado_solicitud', 'pendiente')->count(),
            'aprobadas' => $solicitudes->where('estado_solicitud', 'aprobada')->count(),
            'rechazadas' => $solicitudes->where('estado_solicitud', 'rechazada')->count()
        ]);
    }

    public function getKpisSolicitudes(Request $request)
    {
        return $this->getKpisStatistics($request);
    }

    private function obtenerSolicitudesCombinadas(?string $estado, string $rango, ?string $tipoSolicitud = null, ?string $q = null, ?string $ciudad = null): Collection
    {
        $solicitudes = collect();

        if ($tipoSolicitud === null || $tipoSolicitud === 'all' || $tipoSolicitud === 'arrendador') {
            $solicitudes = $solicitudes->merge($this->obtenerSolicitudesArrendador($rango));
        }

        if ($tipoSolicitud === null || $tipoSolicitud === 'all' || $tipoSolicitud === 'gestor') {
            $solicitudes = $solicitudes->merge($this->obtenerSolicitudesGestor($rango));
        }

        if ($estado) {
            $solicitudes = $solicitudes->where('estado_solicitud', $estado);
        }

        if ($tipoSolicitud && $tipoSolicitud !== 'all') {
            $solicitudes = $solicitudes->where('tipo_solicitud', $tipoSolicitud);
        }

        if ($ciudad) {
            $ciudadBuscada = mb_strtolower(trim($ciudad));
            $solicitudes = $solicitudes->filter(function ($solicitud) use ($ciudadBuscada) {
                if (($solicitud->tipo_solicitud ?? '') !== 'arrendador') {
                    return false;
                }

                $direccion = mb_strtolower((string) ($solicitud->direccion_fiscal_solicitud ?? ''));

                return $direccion !== '' && str_contains($direccion, $ciudadBuscada);
            });
        }

        if ($q) {
            $busqueda = mb_strtolower(trim($q));
            $solicitudes = $solicitudes->filter(function ($solicitud) use ($busqueda) {
                $camposBusqueda = [
                    $solicitud->nombre_usuario ?? '',
                    $solicitud->email_usuario ?? '',
                    $solicitud->telefono_contacto ?? '',
                    $solicitud->tipo_arrendador_solicitud ?? '',
                    $solicitud->direccion_fiscal_solicitud ?? '',
                    $solicitud->descripcion_solicitud ?? '',
                    $solicitud->experiencia_solicitud ?? '',
                    $solicitud->notas_solicitud ?? '',
                    $solicitud->tipo_label ?? '',
                ];

                $texto = mb_strtolower(implode(' ', $camposBusqueda));

                return $busqueda === '' || str_contains($texto, $busqueda);
            });
        }

        return $solicitudes
            ->sortByDesc(function ($solicitud) {
                return $solicitud->actualizado_solicitud ?? $solicitud->creado_solicitud ?? null;
            })
            ->values();
    }

    private function obtenerSolicitudesArrendador(string $rango): Collection
    {
        $query = DB::table('tbl_solicitud_arrendador as s')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 's.id_usuario_fk')
            ->select(
                's.id_solicitud_arrendador as id_solicitud',
                DB::raw("'arrendador' as tipo_solicitud"),
                DB::raw("'Arrendador' as tipo_label"),
                'u.nombre_usuario',
                'u.email_usuario',
                'u.telefono_usuario',
                DB::raw('COALESCE(s.telefono_solicitud, u.telefono_usuario) as telefono_contacto'),
                's.fecha_nacimiento_solicitud',
                's.tipo_documento_solicitud',
                's.numero_documento_solicitud',
                's.iban_solicitud',
                's.titular_cuenta_solicitud',
                's.nif_solicitud',
                's.direccion_fiscal_solicitud',
                's.tipo_arrendador_solicitud',
                's.descripcion_solicitud',
                's.num_propiedades_previstas_solicitud',
                's.es_propietario_solicitud',
                's.acepta_terminos_solicitud',
                's.acepta_veracidad_solicitud',
                's.fecha_aceptacion_solicitud',
                's.estado_solicitud_arrendador as estado_solicitud',
                's.notas_solicitud_arrendador as notas_solicitud',
                's.creado_solicitud_arrendador as creado_solicitud',
                's.actualizado_solicitud_arrendador as actualizado_solicitud',
                DB::raw("COALESCE(u.stripe_status, 'inactive') as stripe_status"),
                DB::raw("'' as experiencia_solicitud")
            );

        $this->aplicarRangoFecha($query, 's.actualizado_solicitud_arrendador', $rango);

        return $query->orderByDesc('s.creado_solicitud_arrendador')->get();
    }

    private function obtenerSolicitudesGestor(string $rango): Collection
    {
        $query = DB::table('tbl_solicitud_gestor as s')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 's.id_usuario_fk')
            ->select(
                's.id_solicitud_gestor as id_solicitud',
                DB::raw("'gestor' as tipo_solicitud"),
                DB::raw("'Gestor' as tipo_label"),
                'u.nombre_usuario',
                'u.email_usuario',
                'u.telefono_usuario',
                DB::raw('COALESCE(u.telefono_usuario, "") as telefono_contacto'),
                DB::raw('NULL as fecha_nacimiento_solicitud'),
                DB::raw('NULL as tipo_documento_solicitud'),
                DB::raw('NULL as numero_documento_solicitud'),
                DB::raw('NULL as iban_solicitud'),
                DB::raw('NULL as titular_cuenta_solicitud'),
                DB::raw('NULL as nif_solicitud'),
                DB::raw('NULL as direccion_fiscal_solicitud'),
                DB::raw('NULL as tipo_arrendador_solicitud'),
                's.descripcion_solicitud',
                DB::raw('NULL as num_propiedades_previstas_solicitud'),
                DB::raw('NULL as es_propietario_solicitud'),
                's.acepta_terminos_solicitud',
                's.acepta_veracidad_solicitud',
                's.fecha_aceptacion_solicitud',
                's.estado_solicitud_gestor as estado_solicitud',
                's.notas_solicitud_gestor as notas_solicitud',
                's.creado_solicitud_gestor as creado_solicitud',
                's.actualizado_solicitud_gestor as actualizado_solicitud',
                DB::raw("'inactive' as stripe_status"),
                's.experiencia_solicitud'
            );

        $this->aplicarRangoFecha($query, 's.actualizado_solicitud_gestor', $rango);

        return $query->orderByDesc('s.creado_solicitud_gestor')->get();
    }

    private function aplicarRangoFecha($query, string $campoFecha, string $rango): void
    {
        switch ($rango) {
            case 'all':
                break;
            case '3meses':
                $query->where($campoFecha, '>=', Carbon::now()->subMonths(3));
                break;
            case 'anio':
                $query->whereYear($campoFecha, Carbon::now()->year);
                break;
            case 'mes':
            default:
                $query->whereMonth($campoFecha, Carbon::now()->month)
                    ->whereYear($campoFecha, Carbon::now()->year);
                break;
        }
    }

    private function paginarColeccion(Collection $solicitudes, int $porPagina, int $paginaActual): LengthAwarePaginator
    {
        $total = $solicitudes->count();
        $paginaActual = max(1, $paginaActual);
        $elementos = $solicitudes->slice(($paginaActual - 1) * $porPagina, $porPagina)->values();

        return new LengthAwarePaginator(
            $elementos,
            $total,
            $porPagina,
            $paginaActual,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]
        );
    }

    private function contarSolicitudesCombinadas(?string $estado, string $rango): int
    {
        return $this->obtenerSolicitudesCombinadas($estado, $rango)->count();
    }

    private function obtenerIdAdmin(): ?int
    {
        $idAdmin = Auth::id();

        if ($idAdmin) {
            return (int) $idAdmin;
        }

        return DB::table('tbl_usuario')
            ->where('email_usuario', 'admin@spotstay.com')
            ->value('id_usuario');
    }

    private function asegurarRolUsuario(int $idUsuario, string $slugRol): void
    {
        $idRol = DB::table('tbl_rol')
            ->where('slug_rol', $slugRol)
            ->value('id_rol');

        if (!$idRol) {
            return;
        }

        $tieneRol = DB::table('tbl_rol_usuario')
            ->where('id_usuario_fk', $idUsuario)
            ->where('id_rol_fk', $idRol)
            ->exists();

        if (!$tieneRol) {
            DB::table('tbl_rol_usuario')->insert([
                'id_usuario_fk' => $idUsuario,
                'id_rol_fk' => $idRol,
                'asignado_rol_usuario' => Carbon::now()
            ]);
        }
    }

    private function asegurarPerfilGestor(int $idUsuario): void
    {
        $existe = DB::table('tbl_usuario_gestor')
            ->where('id_usuario_gestor', $idUsuario)
            ->exists();

        if ($existe) {
            return;
        }

        DB::table('tbl_usuario_gestor')->insert([
            'id_usuario_gestor' => $idUsuario,
            'id_usuario_fk' => $idUsuario,
            'propiedades_gestionadas' => 0,
            'tareas_completadas' => 0,
            'creado_gestor' => Carbon::now(),
            'actualizado_gestor' => Carbon::now(),
        ]);
    }

    private function limpiarSesionesUsuario(int $idUsuario): void
    {
        DB::table('sessions')
            ->where('user_id', $idUsuario)
            ->delete();
    }

    private function resolverTipoSolicitud(?string $tipoSolicitud): string
    {
        return in_array($tipoSolicitud, ['gestor', 'arrendador'], true) ? $tipoSolicitud : 'arrendador';
    }

    private function formatearSolicitudArrendador($solicitud): array
    {
        return [
            'success' => true,
            'tipo_solicitud' => 'arrendador',
            'tipo_label' => 'Arrendador',
            'id_solicitud' => $solicitud->id_solicitud_arrendador,
            'nombre_usuario' => $solicitud->usuario?->nombre_usuario ?? '—',
            'email_usuario' => $solicitud->usuario?->email_usuario ?? '—',
            'telefono_usuario' => $solicitud->usuario?->telefono_usuario ?? '—',
            'telefono_contacto' => $solicitud->telefono_solicitud ?? $solicitud->usuario?->telefono_usuario ?? '—',
            'fecha_nacimiento_solicitud' => $solicitud->fecha_nacimiento_solicitud,
            'tipo_documento_solicitud' => $solicitud->tipo_documento_solicitud,
            'numero_documento_solicitud' => $solicitud->numero_documento_solicitud,
            'iban_solicitud' => $solicitud->iban_solicitud,
            'titular_cuenta_solicitud' => $solicitud->titular_cuenta_solicitud,
            'nif_solicitud' => $solicitud->nif_solicitud,
            'direccion_fiscal_solicitud' => $solicitud->direccion_fiscal_solicitud,
            'tipo_arrendador_solicitud' => $solicitud->tipo_arrendador_solicitud,
            'descripcion_solicitud' => $solicitud->descripcion_solicitud,
            'experiencia_solicitud' => null,
            'num_propiedades_previstas_solicitud' => $solicitud->num_propiedades_previstas_solicitud,
            'es_propietario_solicitud' => $solicitud->es_propietario_solicitud,
            'acepta_terminos_solicitud' => $solicitud->acepta_terminos_solicitud,
            'acepta_veracidad_solicitud' => $solicitud->acepta_veracidad_solicitud,
            'fecha_aceptacion_solicitud' => $solicitud->fecha_aceptacion_solicitud,
            'estado_solicitud' => $solicitud->estado_solicitud_arrendador,
            'notas_solicitud' => $solicitud->notas_solicitud_arrendador,
            'creado_solicitud' => $solicitud->creado_solicitud_arrendador,
            'actualizado_solicitud' => $solicitud->actualizado_solicitud_arrendador,
            'stripe_status' => $solicitud->usuario?->stripe_status ?? 'inactive',
        ];
    }

    private function formatearSolicitudGestor($solicitud): array
    {
        return [
            'success' => true,
            'tipo_solicitud' => 'gestor',
            'tipo_label' => 'Gestor',
            'id_solicitud' => $solicitud->id_solicitud_gestor,
            'nombre_usuario' => $solicitud->usuario?->nombre_usuario ?? '—',
            'email_usuario' => $solicitud->usuario?->email_usuario ?? '—',
            'telefono_usuario' => $solicitud->usuario?->telefono_usuario ?? '—',
            'telefono_contacto' => $solicitud->usuario?->telefono_usuario ?? '—',
            'fecha_nacimiento_solicitud' => null,
            'tipo_documento_solicitud' => null,
            'numero_documento_solicitud' => null,
            'iban_solicitud' => null,
            'titular_cuenta_solicitud' => null,
            'nif_solicitud' => null,
            'direccion_fiscal_solicitud' => null,
            'tipo_arrendador_solicitud' => null,
            'descripcion_solicitud' => $solicitud->descripcion_solicitud,
            'experiencia_solicitud' => $solicitud->experiencia_solicitud,
            'num_propiedades_previstas_solicitud' => null,
            'es_propietario_solicitud' => null,
            'acepta_terminos_solicitud' => $solicitud->acepta_terminos_solicitud,
            'acepta_veracidad_solicitud' => $solicitud->acepta_veracidad_solicitud,
            'fecha_aceptacion_solicitud' => $solicitud->fecha_aceptacion_solicitud,
            'estado_solicitud' => $solicitud->estado_solicitud_gestor,
            'notas_solicitud' => $solicitud->notas_solicitud_gestor,
            'creado_solicitud' => $solicitud->creado_solicitud_gestor,
            'actualizado_solicitud' => $solicitud->actualizado_solicitud_gestor,
            'stripe_status' => 'inactive',
        ];
    }
}
