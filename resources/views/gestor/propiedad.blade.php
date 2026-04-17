@extends('layouts.gestor')
@section('titulo', 'Detalle de propiedad - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/propiedad.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>{{ $propiedad->titulo_propiedad }}</h1>
        <p>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · CP {{ $propiedad->codigo_postal_propiedad }}</p>
    </div>
    <div class="hero-actions">
        <a href="{{ url('/gestor/propiedades') }}" class="btn-volver-propiedades">← Volver a propiedades</a>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
</div>

<div class="central-grid detalle-grid">
    <div class="card-admin">
        <div class="card-header-admin"><span>Información de la propiedad</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Estado</span><span>{{ ucfirst($propiedad->estado_propiedad) }}</span></div>
            <div class="detalle-dato"><span class="label">Precio</span><span>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</span></div>
            <div class="detalle-dato"><span class="label">Gestor asignado</span><span>{{ $propiedad->nombre_gestor }}</span></div>
            <div class="detalle-dato"><span class="label">Descripción</span><span>{{ $propiedad->descripcion_propiedad ?: 'Sin descripción' }}</span></div>
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin"><span>Arrendador</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Nombre</span><span>{{ $propiedad->nombre_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Email</span><span>{{ $propiedad->email_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Teléfono</span><span>{{ $propiedad->telefono_arrendador ?: 'No disponible' }}</span></div>
        </div>
    </div>
</div>

<div class="inferior-grid detalle-grid-inferior">
    <div class="card-admin" id="alquileres-activos">
        <div class="card-header-admin"><span>Alquileres activos</span></div>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>INQUILINO</th>
                    <th>EMAIL</th>
                    <th>INICIO</th>
                    <th>FIN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alquileresActivos as $alquiler)
                    <tr>
                        <td>{{ $alquiler->nombre_inquilino }}</td>
                        <td>{{ $alquiler->email_inquilino }}</td>
                        <td>{{ \Carbon\Carbon::parse($alquiler->fecha_inicio_alquiler)->format('d/m/Y') }}</td>
                        <td>{{ $alquiler->fecha_fin_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->format('d/m/Y') : 'Indefinido' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="tabla-vacia">No hay alquileres activos para esta propiedad.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin"><span>Incidencias recientes</span></div>
        <div class="resumen-incidencias">
            <div class="resumen-pill">Abiertas: <strong>{{ $totalesIncidencia['abiertas'] }}</strong></div>
            <div class="resumen-pill">En proceso: <strong>{{ $totalesIncidencia['en_proceso'] }}</strong></div>
            <div class="resumen-pill">Resueltas: <strong>{{ $totalesIncidencia['resueltas'] }}</strong></div>
        </div>
        <table class="tabla-admin tabla-incidencias-detalle">
            <thead>
                <tr>
                    <th>TÍTULO</th>
                    <th>ESTADO</th>
                    <th>PRIORIDAD</th>
                    <th>FECHA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidenciasRecientes as $incidencia)
                    <tr>
                        <td>{{ $incidencia->titulo_incidencia }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</td>
                        <td>{{ ucfirst($incidencia->prioridad_incidencia) }}</td>
                        <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                        <td><a href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}" class="link-ver-todos">Abrir</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tabla-vacia">No hay incidencias registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
