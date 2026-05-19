@extends('layouts.gestor')
@section('titulo', 'Incidencias - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/incidencias.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Gestión de incidencias del gestor</h1>
        <p>Filtra, prioriza y entra al detalle de cada incidencia operativa</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="central-grid incidencias-filtros-wrap" id="incidenciasFiltrosWrap">
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient" onclick="toggleFiltros()" style="cursor:pointer;">
            <span><i class="bi bi-funnel"></i> Filtros de incidencias</span>
            <span class="filtros-toggle" id="filtrosToggleIcon"><i class="bi bi-chevron-down"></i></span>
        </div>

        <div id="filtrosBody">
        <form method="GET" action="{{ route('gestor.incidencias') }}" class="form-filtros-admin" id="incidenciasFiltrosForm">
            @if(($propiedadId ?? 0) > 0)
                <input type="hidden" name="propiedad_id" value="{{ $propiedadId }}">
            @endif
            <input type="text" name="titulo" value="{{ $titulo }}" placeholder="Filtrar por título">
            <input type="text" name="propiedad" value="{{ $propiedad }}" placeholder="Filtrar por propiedad">

            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="abierta" {{ $estado === 'abierta' ? 'selected' : '' }}>Nuevas</option>
                <option value="en_proceso" {{ $estado === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                <option value="esperando_decision" {{ $estado === 'esperando_decision' ? 'selected' : '' }}>Esperando decisión</option>
                <option value="esperando_pago" {{ $estado === 'esperando_pago' ? 'selected' : '' }}>Esperando pago</option>
                <option value="resuelta" {{ $estado === 'resuelta' ? 'selected' : '' }}>Resueltas</option>
                <option value="cerrada" {{ $estado === 'cerrada' ? 'selected' : '' }}>Cerradas</option>
            </select>

            <select name="prioridad">
                <option value="">Todas las prioridades</option>
                <option value="alta" {{ $prioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ $prioridad === 'media' ? 'selected' : '' }}>Media</option>
                <option value="baja" {{ $prioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="urgente" {{ $prioridad === 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>

            <input type="date" name="fecha" value="{{ $fecha }}">

            <div class="acciones-filtros-admin acciones-filtros-mobile">
                <button type="submit" class="btn-aplicar-admin">Filtrar</button>
                <a href="{{ route('gestor.incidencias') }}" class="btn-limpiar-admin">Limpiar</a>
            </div>
        </form>
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Resumen de resultados</span>
        </div>
        <div class="resumen-filtros">
            <p><strong>{{ $incidencias->total() }}</strong> incidencias encontradas</p>
            <p>Estado: <span>{{ $estado !== '' ? ucfirst(str_replace('_', ' ', $estado)) : 'Todos' }}</span></p>
            <p>Prioridad: <span>{{ $prioridad !== '' ? ucfirst($prioridad) : 'Todas' }}</span></p>
            <p>Fecha: <span>{{ $fecha !== '' ? $fecha : 'Cualquier fecha' }}</span></p>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="mensaje-estado mensaje-error" data-flash-error="{{ session('error') }}">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="mensaje-estado mensaje-ok" data-flash-success="{{ session('success') }}">{{ session('success') }}</div>
@endif

<div class="incidencias-tabla-wrap" id="incidenciasTablaWrap">
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Listado de incidencias</span>
            <a href="{{ route('gestor.dashboard') }}" class="link-ver-todos">Volver al dashboard →</a>
        </div>

        <!-- Vista desktop: tabla -->
        <div class="incidencias-tabla-desktop">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>TÍTULO</th>
                        <th>PROPIEDAD</th>
                        <th>ARRENDADOR</th>
                        <th>ESTADO</th>
                        <th>PRIORIDAD</th>
                        <th>FECHA</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidencias as $incidencia)
                        @php
                            $prioridad = strtolower($incidencia->prioridad_incidencia);
                            $badgePrioridad = $prioridad === 'urgente' ? 'alta' : $prioridad;
                            $badgeEstado = match($incidencia->estado_incidencia) {
                                'abierta' => 'pendiente',
                                'en_proceso' => 'activo',
                                'esperando_decision' => 'pendiente',
                                'esperando_pago' => 'pendiente',
                                'resuelta' => 'activo',
                                'cerrada' => 'activo',
                                default => 'rechazado'
                            };
                        @endphp
                        <tr>
                            <td>{{ $incidencia->titulo_incidencia }}</td>
                            <td>{{ $incidencia->direccion_propiedad }}</td>
                            <td>{{ $incidencia->nombre_arrendador }}</td>
                            <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span></td>
                            <td><span class="badge-prioridad badge-prioridad-{{ $badgePrioridad }}">{{ ucfirst($prioridad) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                            <td><a class="link-ver-todos" href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}">Ver</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="tabla-vacia">No hay incidencias con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista mobile: cards -->
        <div class="incidencias-lista-mobile">
            @forelse($incidencias as $incidencia)
                @php
                    $prioridad = strtolower($incidencia->prioridad_incidencia);
                    $badgePrioridad = $prioridad === 'urgente' ? 'alta' : $prioridad;
                    $badgeEstado = match($incidencia->estado_incidencia) {
                        'abierta' => 'pendiente',
                        'en_proceso' => 'activo',
                        'esperando_decision' => 'pendiente',
                        'esperando_pago' => 'pendiente',
                        'resuelta' => 'activo',
                        'cerrada' => 'activo',
                        default => 'rechazado'
                    };
                @endphp
                <a href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}" class="incidencia-card">
                    <div class="incidencia-card-header">
                        <span class="incidencia-card-titulo">{{ $incidencia->titulo_incidencia }}</span>
                        <span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
                    </div>
                    <p class="incidencia-card-dir">{{ $incidencia->direccion_propiedad }}</p>
                    <p class="incidencia-card-persona">{{ $incidencia->nombre_arrendador }}</p>
                    <div class="incidencia-card-divider"></div>
                    <div class="incidencia-card-footer">
                        <div class="incidencia-card-meta">
                            <span class="incidencia-card-prioridad">Prioridad: <strong>{{ ucfirst($prioridad) }}</strong></span>
                            <span class="incidencia-card-fecha">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</span>
                        </div>
                        <span class="btn-revisar">Ver detalle →</span>
                    </div>
                </a>
            @empty
                <p class="tarjeta-vacia">No hay incidencias con los filtros seleccionados.</p>
            @endforelse
        </div>

        @if($incidencias->lastPage() > 1)
            <div class="paginacion-admin paginacion-cargar-mas"
                 data-current-page="{{ $incidencias->currentPage() }}"
                 data-last-page="{{ $incidencias->lastPage() }}">
                @if($incidencias->hasMorePages())
                    <button class="btn-cargar-mas" data-next-url="{{ $incidencias->nextPageUrl() }}" type="button">
                        Cargar más ({{ $incidencias->currentPage() }} / {{ $incidencias->lastPage() }})
                    </button>
                @else
                    <span class="cargar-mas-fin">Todas las incidencias cargadas</span>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/gestor/incidencias-filtros.js') }}"></script>
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
