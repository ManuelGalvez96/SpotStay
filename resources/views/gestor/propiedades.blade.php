@extends('layouts.gestor')
@section('titulo', 'Propiedades asignadas - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/propiedades.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/propiedades.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Propiedades gestionadas</h1>
        <p>Gestiona el estado de tus propiedades y accede al detalle de cada una</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="resumen-kpis-gestor">
    <div class="resumen-kpis-label">
        <strong>{{ $totalAsignadas }}</strong> propiedades gestionadas
    </div>

    <div class="kpi-grid-pequeno gestor-kpis" id="propiedadesKpiGrid">
        <div class="kpi-mini kpi-clickable" data-filter-key="estado" data-filter-value="publicada">
            <div class="kpi-mini-icono kpi-mini-verde"><i class="bi bi-megaphone"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">{{ $totalPublicadas }}</span>
                <span class="kpi-mini-label">Publicadas</span>
            </div>
        </div>

        <div class="kpi-mini kpi-clickable" data-filter-key="estado" data-filter-value="alquilada">
            <div class="kpi-mini-icono kpi-mini-naranja"><i class="bi bi-key"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">{{ $totalAlquiladas }}</span>
                <span class="kpi-mini-label">Alquiladas</span>
            </div>
        </div>

        <div class="kpi-mini kpi-clickable" data-filter-key="operativo" data-filter-value="criticas">
            <div class="kpi-mini-icono kpi-mini-rojo"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $totalConCriticas }}</span>
                <span class="kpi-mini-label">Con incidencias críticas</span>
            </div>
        </div>

        <div class="kpi-mini kpi-clickable" data-filter-key="operativo" data-filter-value="sin_alquiler">
            <div class="kpi-mini-icono kpi-mini-azul"><i class="bi bi-person-x"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">{{ $totalSinAlquiler }}</span>
                <span class="kpi-mini-label">Sin alquiler activo</span>
            </div>
        </div>
    </div>
</div>

<div class="card-admin filtros-card-gestor card-con-franja">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>Filtros de propiedades</span>
    </div>

    <form method="GET" action="{{ route('gestor.propiedades') }}" class="filtros-propiedades" id="propiedadesFiltrosForm">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título, dirección o arrendador">

        <select name="estado">
            <option value="">Todos los estados</option>
            <option value="publicada" {{ $estado === 'publicada' ? 'selected' : '' }}>Publicada</option>
            <option value="alquilada" {{ $estado === 'alquilada' ? 'selected' : '' }}>Alquilada</option>
            <option value="inactiva" {{ $estado === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
        </select>

        <input type="text" name="ciudad" value="{{ $ciudad }}" placeholder="Filtrar por ciudad">

        <select name="estado_pagos">
            <option value="" {{ $estadoPagos === '' ? 'selected' : '' }}>Estado de pagos: todos</option>
            <option value="al_dia" {{ $estadoPagos === 'al_dia' ? 'selected' : '' }}>Al día</option>
            <option value="pendiente" {{ $estadoPagos === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="atrasado" {{ $estadoPagos === 'atrasado' ? 'selected' : '' }}>Atrasado</option>
        </select>

    </form>
</div>

@if(session('error'))
    <div class="mensaje-estado mensaje-error" data-flash-error="{{ session('error') }}">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="mensaje-estado mensaje-ok" data-flash-success="{{ session('success') }}">{{ session('success') }}</div>
@endif

<div class="card-admin tabla-propiedades-card card-con-franja" id="propiedadesTablaCard">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>{{ $propiedades->total() }} propiedades encontradas</span>
    </div>

    @php
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
    @endphp

    <!-- Vista desktop: tabla -->
    <div class="propiedades-tabla-desktop">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>
                        <a class="th-sort {{ $sort === 'titulo_propiedad' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'titulo_propiedad', 'dir' => $sort === 'titulo_propiedad' ? $nextDir : 'asc']) }}">
                            PROPIEDAD
                        </a>
                    </th>
                    <th>ARRENDADOR</th>
                    <th class="th-pagos">
                        <span class="th-sort activo">PAGOS</span>
                        <span class="pagos-help-wrap">
                            <button type="button" class="pagos-help-square" aria-label="Mostrar leyenda del indicador de pagos">
                                i
                            </button>
                            <span class="pagos-legend-box" role="tooltip">
                                <strong>Leyenda de pagos</strong>
                                <span class="pagos-legend-row"><b class="pagos-legend-tag pagos-legend-verde">Verde:</b> todo pagado</span>
                                <span class="pagos-legend-row"><b class="pagos-legend-tag pagos-legend-amarillo">Amarillo:</b> hay algún pago pendiente</span>
                                <span class="pagos-legend-row"><b class="pagos-legend-tag pagos-legend-rojo">Rojo:</b> hay algún pago atrasado</span>
                            </span>
                        </span>
                    </th>
                    <th>ESTADO</th>
                    <th>
                        <a class="th-sort {{ $sort === 'precio_propiedad' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'precio_propiedad', 'dir' => $sort === 'precio_propiedad' ? $nextDir : 'asc']) }}">
                            PRECIO
                        </a>
                    </th>
                    <th>
                        <a class="th-sort {{ $sort === 'incidencias_activas' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'incidencias_activas', 'dir' => $sort === 'incidencias_activas' ? $nextDir : 'desc']) }}">
                            INCIDENCIAS ACTIVAS
                        </a>
                    </th>
                    <th>
                        <a class="th-sort {{ $sort === 'alquileres_activos' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'alquileres_activos', 'dir' => $sort === 'alquileres_activos' ? $nextDir : 'desc']) }}">
                            ALQUILERES ACTIVOS
                        </a>
                    </th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($propiedades as $propiedad)
                    @php
                        $badgeEstado = match($propiedad->estado_propiedad) {
                            'publicada' => 'pendiente',
                            'alquilada' => 'activo',
                            'inactiva' => 'inactiva',
                            default => 'pendiente'
                        };

                        $estadoPagos = 'verde';
                        $textoPagos = 'Al día';

                        if ((int) $propiedad->total_pagos_atrasados > 0) {
                            $estadoPagos = 'rojo';
                            $textoPagos = 'Atrasado';
                        } elseif ((int) $propiedad->total_pagos_pendientes > 0) {
                            $estadoPagos = 'amarillo';
                            $textoPagos = 'Pendiente';
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="propiedad-col">
                                <p class="propiedad-nombre">{{ $propiedad->titulo_propiedad }}</p>
                                <p class="propiedad-meta">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                            </div>
                        </td>
                        <td>{{ $propiedad->nombre_arrendador }}</td>
                        <td>
                            <span class="pagos-chip pagos-{{ $estadoPagos }}">
                                <span class="pagos-punto"></span>
                                {{ $textoPagos }}
                            </span>
                        </td>
                        <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst($propiedad->estado_propiedad) }}</span></td>
                        <td>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</td>
                        <td>{{ $propiedad->total_incidencias_activas }}</td>
                        <td>{{ $propiedad->total_alquileres_activos }}</td>
                    <td>
                        <div class="acciones-rapidas">
                            <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="link-ver-todos">Detalle</a>
                            @if(!empty($permisosPropiedades[$propiedad->id_propiedad] ?? null) && $permisosPropiedades[$propiedad->id_propiedad]->incidencias)
                                <a href="{{ route('gestor.incidencias', ['propiedad_id' => $propiedad->id_propiedad]) }}" class="link-secundario">Incidencias</a>
                            @else
                                <span class="link-secundario link-deshabilitado" title="Sin permiso de incidencias">Incidencias</span>
                            @endif
                            <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}#alquileres-activos" class="link-secundario">Alquileres</a>
                        </div>
                    </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="tabla-vacia">No tienes propiedades asignadas con esos filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Vista mobile: lista compacta -->
    <div class="propiedades-lista-mobile">
        @forelse($propiedades as $propiedad)
            @php
                $iniciales = strtoupper(substr($propiedad->titulo_propiedad, 0, 2));
                $badgeEstado = match($propiedad->estado_propiedad) {
                    'publicada' => 'pendiente',
                    'alquilada' => 'activo',
                    'inactiva' => 'inactiva',
                    default => 'pendiente'
                };
                $estadoPagos = 'verde';
                $textoPagos = 'Al día';
                if ((int) $propiedad->total_pagos_atrasados > 0) {
                    $estadoPagos = 'rojo';
                    $textoPagos = 'Atrasado';
                } elseif ((int) $propiedad->total_pagos_pendientes > 0) {
                    $estadoPagos = 'amarillo';
                    $textoPagos = 'Pendiente';
                }
            @endphp
            <div class="solicitud-item">
                <div class="solicitud-avatar" style="background:#035498;">{{ $iniciales }}</div>
                <div class="solicitud-info">
                    <p class="solicitud-nombre">{{ $propiedad->titulo_propiedad }}</p>
                    <p class="solicitud-ciudad">{{ $propiedad->direccion_propiedad }}</p>
                </div>
                <div class="solicitud-meta">
                    <span class="pagos-chip pagos-{{ $estadoPagos }}">
                        <span class="pagos-punto"></span>
                        {{ $textoPagos }}
                    </span>
                    <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="btn-revisar">Detalle →</a>
                    @if(!empty($permisosPropiedades[$propiedad->id_propiedad] ?? null) && $permisosPropiedades[$propiedad->id_propiedad]->incidencias)
                        <a href="{{ route('gestor.incidencias', ['propiedad_id' => $propiedad->id_propiedad]) }}" class="link-secundario">Incidencias</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="tarjeta-vacia">No tienes propiedades asignadas con esos filtros.</p>
        @endforelse
    </div>

    @if($propiedades->lastPage() > 1)
        <div class="paginacion-admin">
            @if($propiedades->onFirstPage())
                <span class="pagina-btn-admin disabled">Anterior</span>
            @else
                <a class="pagina-btn-admin" href="{{ $propiedades->previousPageUrl() }}">Anterior</a>
            @endif

            <span class="pagina-info-admin">Página {{ $propiedades->currentPage() }} de {{ $propiedades->lastPage() }}</span>

            @if($propiedades->hasMorePages())
                <a class="pagina-btn-admin" href="{{ $propiedades->nextPageUrl() }}">Siguiente</a>
            @else
                <span class="pagina-btn-admin disabled">Siguiente</span>
            @endif
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/gestor/propiedades-filtros.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');
    if (flashSuccess && flashSuccess.dataset.flashSuccess && window.swalSuccess) {
        swalSuccess('Éxito', flashSuccess.dataset.flashSuccess);
    }
    if (flashError && flashError.dataset.flashError && window.swalError) {
        swalError('Error', flashError.dataset.flashError);
    }
});
</script>
@endsection
