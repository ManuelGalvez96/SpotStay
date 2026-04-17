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
        <h1>Propiedades asignadas</h1>
        <p>Gestiona el estado de tus propiedades y accede al detalle de cada una</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="resumen-kpis-gestor">
    <div class="resumen-kpis-label">
        <strong>{{ $totalAsignadas }}</strong> propiedades asignadas
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

<div class="card-admin filtros-card-gestor">
    <div class="card-header-admin">
        <span>Filtros de propiedades</span>
    </div>

    <form method="GET" action="{{ url('/gestor/propiedades') }}" class="filtros-propiedades" id="propiedadesFiltrosForm">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título, dirección o arrendador">

        <select name="estado">
            <option value="">Todos los estados</option>
            <option value="publicada" {{ $estado === 'publicada' ? 'selected' : '' }}>Publicada</option>
            <option value="alquilada" {{ $estado === 'alquilada' ? 'selected' : '' }}>Alquilada</option>
            <option value="inactiva" {{ $estado === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
        </select>

        <input type="text" name="ciudad" value="{{ $ciudad }}" placeholder="Filtrar por ciudad">

        <select name="operativo">
            <option value="" {{ $operativo === '' ? 'selected' : '' }}>Operativa: todas</option>
            <option value="criticas" {{ $operativo === 'criticas' ? 'selected' : '' }}>Con incidencias críticas</option>
            <option value="sin_alquiler" {{ $operativo === 'sin_alquiler' ? 'selected' : '' }}>Sin alquiler activo</option>
            <option value="estables" {{ $operativo === 'estables' ? 'selected' : '' }}>Estables</option>
        </select>

    </form>
</div>

<div class="card-admin tabla-propiedades-card" id="propiedadesTablaCard">
    <div class="card-header-admin">
        <span>{{ $propiedades->total() }} propiedades encontradas</span>
    </div>

    @php
        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
    @endphp

    <table class="tabla-admin">
        <thead>
            <tr>
                <th>
                    <a class="th-sort {{ $sort === 'titulo_propiedad' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'titulo_propiedad', 'dir' => $sort === 'titulo_propiedad' ? $nextDir : 'asc']) }}">
                        PROPIEDAD
                    </a>
                </th>
                <th>ARRENDADOR</th>
                <th class="th-salud">
                    <a class="th-sort {{ $sort === 'incidencias_criticas' ? 'activo' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'incidencias_criticas', 'dir' => $sort === 'incidencias_criticas' ? $nextDir : 'desc']) }}">
                        SALUD
                    </a>
                    <span class="salud-help-wrap">
                        <button type="button" class="salud-help-square" aria-label="Mostrar leyenda del indicador de salud">
                            i
                        </button>
                        <span class="salud-legend-box" role="tooltip">
                            <strong>Leyenda de salud</strong>
                            <span class="salud-legend-row"><b class="salud-legend-tag salud-legend-verde">Verde:</b> sin incidencias activas</span>
                            <span class="salud-legend-row"><b class="salud-legend-tag salud-legend-amarillo">Amarillo:</b> con incidencias activas</span>
                            <span class="salud-legend-row"><b class="salud-legend-tag salud-legend-rojo">Rojo:</b> con incidencias urgentes</span>
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
                        'inactiva' => 'rechazado',
                        default => 'pendiente'
                    };

                    $salud = 'verde';
                    if ((int) $propiedad->total_incidencias_criticas > 0) {
                        $salud = 'rojo';
                    } elseif ((int) $propiedad->total_incidencias_activas > 0) {
                        $salud = 'amarillo';
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
                        <span class="salud-chip salud-{{ $salud }}">
                            <span class="salud-punto"></span>
                            {{ ucfirst($salud) }}
                        </span>
                    </td>
                    <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst($propiedad->estado_propiedad) }}</span></td>
                    <td>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</td>
                    <td>{{ $propiedad->total_incidencias_activas }}</td>
                    <td>{{ $propiedad->total_alquileres_activos }}</td>
                    <td>
                        <div class="acciones-rapidas">
                            <a href="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad) }}" class="link-ver-todos">Detalle</a>
                            <a href="{{ url('/gestor/incidencias?propiedad_id=' . $propiedad->id_propiedad) }}" class="link-secundario">Incidencias</a>
                            <a href="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad . '#alquileres-activos') }}" class="link-secundario">Alquileres</a>
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
@endsection
