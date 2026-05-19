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

    <div class="card-admin filtros-card-gestor card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient" onclick="toggleFiltros()" style="cursor:pointer;">
            <span><i class="bi bi-funnel"></i> Filtros de propiedades</span>
            <span class="filtros-toggle" id="filtrosToggleIcon"><i class="bi bi-chevron-down"></i></span>
        </div>

        <div id="filtrosBody">
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

            @if($proximoVencimiento > 0)
                <input type="hidden" name="proximo_vencimiento" value="{{ $proximoVencimiento }}">
            @endif

            <div class="acciones-filtros-mobile">
                <button type="submit" class="btn-aplicar-admin">Filtrar</button>
                <a href="{{ route('gestor.propiedades') }}" class="btn-limpiar-admin">Limpiar</a>
            </div>
        </form>
        </div>
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
        <span>{{ $propiedades->total() }} propiedades encontradas
            @if($proximoVencimiento > 0)
                <span class="badge-estado badge-pendiente" style="margin-left:8px;font-weight:500;">Próximas a vencer ({{ $proximoVencimiento }} días)</span>
            @endif
        </span>
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

    <!-- Vista mobile: cards -->
    <div class="propiedades-lista-mobile">
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
            <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="propiedad-card">
                <div class="propiedad-card-header">
                    <span class="propiedad-card-titulo">{{ $propiedad->titulo_propiedad }}</span>
                    <span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst($propiedad->estado_propiedad) }}</span>
                </div>
                <p class="propiedad-card-dir">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                <p class="propiedad-card-persona">{{ $propiedad->nombre_arrendador }}</p>
                <div class="propiedad-card-divider"></div>
                <div class="propiedad-card-footer">
                    <div class="propiedad-card-meta">
                        <span class="propiedad-card-precio">{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</span>
                        <span class="propiedad-card-incidencias">{{ $propiedad->total_incidencias_activas }} incidencias</span>
                    </div>
                    <span class="pagos-chip pagos-{{ $estadoPagos }}">
                        <span class="pagos-punto"></span>
                        {{ $textoPagos }}
                    </span>
                </div>
            </a>
        @empty
            <p class="tarjeta-vacia">No tienes propiedades asignadas con esos filtros.</p>
        @endforelse
    </div>

    @if($propiedades->lastPage() > 1)
        <div class="paginacion-admin paginacion-cargar-mas"
             data-current-page="{{ $propiedades->currentPage() }}"
             data-last-page="{{ $propiedades->lastPage() }}">
            @if($propiedades->hasMorePages())
                <button class="btn-cargar-mas" data-next-url="{{ $propiedades->nextPageUrl() }}" type="button">
                    Cargar más ({{ $propiedades->currentPage() }} / {{ $propiedades->lastPage() }})
                </button>
            @else
                <span class="cargar-mas-fin">Todas las propiedades cargadas</span>
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

    const filtrosBody = document.getElementById('filtrosBody');
    if (filtrosBody && window.innerWidth <= 768) {
        filtrosBody.classList.add('collapsed');
    }
});

function toggleFiltros() {
    const body = document.getElementById('filtrosBody');
    const icon = document.getElementById('filtrosToggleIcon');
    if (body && icon) {
        body.classList.toggle('collapsed');
        icon.classList.toggle('rotated');
    }
}
</script>
@endsection
