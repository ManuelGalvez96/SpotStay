@extends('layouts.gestor')
@section('titulo', 'Incidencias - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/gestor/incidencias.css') }}">
@endsection

@section('content')
<div class="incidencias-shell">
    <section class="incidencias-head card-saas reveal">
        <div>
            <h1>Incidencias</h1>
            <p>Vista completa con filtros operativos.</p>
        </div>
        <a class="btn-volver" href="{{ url('/gestor/dashboard') }}">Volver al dashboard</a>
    </section>

    <section class="card-saas reveal filtros-card">
        <form method="GET" action="{{ url('/gestor/incidencias') }}" class="filtros-form">
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

            <div class="acciones-filtros">
                <button type="submit" class="btn-aplicar">Aplicar filtros</button>
                <a href="{{ url('/gestor/incidencias') }}" class="btn-limpiar">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="card-saas reveal">
        <header class="card-head">
            <h2>Resultados</h2>
            <span>{{ $incidencias->total() }} incidencias</span>
        </header>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Propiedad</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidencias as $incidencia)
                        @php
                            $estadoBadge = match($incidencia->estado_incidencia) {
                                'abierta' => 'badge-estado abierta',
                                'en_proceso' => 'badge-estado en-proceso',
                                'esperando' => 'badge-estado esperando',
                                'resuelta' => 'badge-estado resuelta',
                                default => 'badge-estado'
                            };

                            $prioridadNormalizada = strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia);
                        @endphp
                        <tr>
                            <td class="titulo-col">{{ $incidencia->titulo_incidencia }}</td>
                            <td>{{ $incidencia->titulo_propiedad }} · {{ $incidencia->direccion_propiedad }}</td>
                            <td><span class="{{ $estadoBadge }}">{{ str_replace('_', ' ', ucfirst($incidencia->estado_incidencia)) }}</span></td>
                            <td><span class="badge-prioridad prioridad-{{ $prioridadNormalizada }}">{{ ucfirst($prioridadNormalizada) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                            <td><a class="btn-ver" href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}">Ver</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="vacio-tabla">No hay incidencias con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidencias->lastPage() > 1)
            <div class="paginacion-simple">
                @if($incidencias->onFirstPage())
                    <span class="pagina-btn disabled">Anterior</span>
                @else
                    <a class="pagina-btn" href="{{ $incidencias->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="pagina-info">Página {{ $incidencias->currentPage() }} de {{ $incidencias->lastPage() }}</span>

                @if($incidencias->hasMorePages())
                    <a class="pagina-btn" href="{{ $incidencias->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="pagina-btn disabled">Siguiente</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection
