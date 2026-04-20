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
    <div class="card-admin">
        <div class="card-header-admin">
            <span>Filtros</span>
        </div>

        <form method="GET" action="{{ url('/gestor/incidencias') }}" class="form-filtros-admin" id="incidenciasFiltrosForm">
            @if(($propiedadId ?? 0) > 0)
                <input type="hidden" name="propiedad_id" value="{{ $propiedadId }}">
            @endif
            <input type="text" name="titulo" value="{{ $titulo }}" placeholder="Filtrar por título">
            <input type="text" name="propiedad" value="{{ $propiedad }}" placeholder="Filtrar por propiedad">

            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="abierta" {{ $estado === 'abierta' ? 'selected' : '' }}>Nuevas</option>
                <option value="en_proceso" {{ $estado === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                <option value="esperando" {{ $estado === 'esperando' ? 'selected' : '' }}>Esperando</option>
                <option value="resuelta" {{ $estado === 'resuelta' ? 'selected' : '' }}>Resueltas</option>
            </select>

            <select name="prioridad">
                <option value="">Todas las prioridades</option>
                <option value="alta" {{ $prioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ $prioridad === 'media' ? 'selected' : '' }}>Media</option>
                <option value="baja" {{ $prioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="urgente" {{ $prioridad === 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>

            <input type="date" name="fecha" value="{{ $fecha }}">

        </form>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
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

<div class="incidencias-tabla-wrap" id="incidenciasTablaWrap">
    <div class="card-admin">
        <div class="card-header-admin">
            <span>Listado de incidencias</span>
            <a href="{{ url('/gestor/dashboard') }}" class="link-ver-todos">Volver al dashboard →</a>
        </div>

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
                            'resuelta' => 'activo',
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
                        <td><a class="link-ver-todos" href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tabla-vacia">No hay incidencias con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($incidencias->lastPage() > 1)
            <div class="paginacion-admin">
                @if($incidencias->onFirstPage())
                    <span class="pagina-btn-admin disabled">Anterior</span>
                @else
                    <a class="pagina-btn-admin" href="{{ $incidencias->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="pagina-info-admin">Página {{ $incidencias->currentPage() }} de {{ $incidencias->lastPage() }}</span>

                @if($incidencias->hasMorePages())
                    <a class="pagina-btn-admin" href="{{ $incidencias->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="pagina-btn-admin disabled">Siguiente</span>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/gestor/incidencias-filtros.js') }}"></script>
@endsection
