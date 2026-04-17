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

<div class="kpi-grid-pequeno gestor-kpis">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul"><i class="bi bi-house"></i></div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalAsignadas }}</span>
            <span class="kpi-mini-label">Asignadas</span>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde"><i class="bi bi-megaphone"></i></div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalPublicadas }}</span>
            <span class="kpi-mini-label">Publicadas</span>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja"><i class="bi bi-key"></i></div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalAlquiladas }}</span>
            <span class="kpi-mini-label">Alquiladas</span>
        </div>
    </div>
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo"><i class="bi bi-pencil-square"></i></div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalBorrador }}</span>
            <span class="kpi-mini-label">Borrador</span>
        </div>
    </div>
</div>

<div class="card-admin filtros-card-gestor">
    <div class="card-header-admin">
        <span>Filtros de propiedades</span>
    </div>

    <form method="GET" action="{{ url('/gestor/propiedades') }}" class="filtros-propiedades">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título, dirección o arrendador">

        <select name="estado">
            <option value="">Todos los estados</option>
            <option value="publicada" {{ $estado === 'publicada' ? 'selected' : '' }}>Publicada</option>
            <option value="alquilada" {{ $estado === 'alquilada' ? 'selected' : '' }}>Alquilada</option>
            <option value="borrador" {{ $estado === 'borrador' ? 'selected' : '' }}>Borrador</option>
            <option value="inactiva" {{ $estado === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
        </select>

        <input type="text" name="ciudad" value="{{ $ciudad }}" placeholder="Filtrar por ciudad">

        <div class="acciones-filtros-propiedades">
            <button type="submit" class="btn-aplicar-admin">Aplicar</button>
            <a href="{{ url('/gestor/propiedades') }}" class="btn-limpiar-admin">Limpiar</a>
        </div>
    </form>
</div>

<div class="card-admin tabla-propiedades-card">
    <div class="card-header-admin">
        <span>{{ $propiedades->total() }} propiedades encontradas</span>
    </div>

    <table class="tabla-admin">
        <thead>
            <tr>
                <th>PROPIEDAD</th>
                <th>ARRENDADOR</th>
                <th>ESTADO</th>
                <th>PRECIO</th>
                <th>INCIDENCIAS ACTIVAS</th>
                <th>ALQUILERES ACTIVOS</th>
                <th>ACCIÓN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($propiedades as $propiedad)
                @php
                    $badgeEstado = match($propiedad->estado_propiedad) {
                        'publicada' => 'pendiente',
                        'alquilada' => 'activo',
                        'borrador' => 'rechazado',
                        'inactiva' => 'rechazado',
                        default => 'pendiente'
                    };
                @endphp
                <tr>
                    <td>
                        <div class="propiedad-col">
                            <p class="propiedad-nombre">{{ $propiedad->titulo_propiedad }}</p>
                            <p class="propiedad-meta">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                        </div>
                    </td>
                    <td>{{ $propiedad->nombre_arrendador }}</td>
                    <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst($propiedad->estado_propiedad) }}</span></td>
                    <td>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</td>
                    <td>{{ $propiedad->total_incidencias_activas }}</td>
                    <td>{{ $propiedad->total_alquileres_activos }}</td>
                    <td><a href="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad) }}" class="link-ver-todos">Ver detalle →</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="tabla-vacia">No tienes propiedades asignadas con esos filtros.</td>
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
