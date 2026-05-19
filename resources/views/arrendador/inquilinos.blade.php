@extends('layouts.arrendador')

@section('titulo', 'Inquilinos - Arrendador')

@section('css')
<link rel="stylesheet" href="{{ asset('css/arrendador/inquilinos.css') }}" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Información de inquilinos</h1>
            <p class="subtitulo">Consulta datos de contacto y alquileres activos.</p>
        </div>
    </header>

    <section class="kpis">
        <article class="kpi"><span>{{ $totales['inquilinos'] }}</span><small>Inquilinos activos</small></article>
        <article class="kpi"><span>{{ $totales['alquileres_activos'] }}</span><small>Alquileres activos</small></article>
        <article class="kpi"><span>{{ $totales['propiedades_ocupadas'] }}</span><small>Propiedades ocupadas</small></article>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Propiedades</th>
                    <th>Inicio reciente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquilinos as $inquilino)
                <tr>
                    <td>{{ $inquilino->nombre_usuario }}</td>
                    <td>{{ $inquilino->email_usuario }}</td>
                    <td>{{ $inquilino->telefono_usuario ?? 'Sin teléfono' }}</td>
                    <td>{{ $inquilino->total_propiedades }}</td>
                    <td>{{ $inquilino->fecha_inicio_reciente ? \Carbon\Carbon::parse($inquilino->fecha_inicio_reciente)->format('d/m/Y') : '-' }}</td>
                    <td>
                        <button class="btn-detalle" data-ver-inquilino="{{ $inquilino->id_usuario }}" data-arrendador="{{ $arrendadorId }}">Ver detalle</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No hay inquilinos activos para este arrendador.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $inquilinos->withQueryString()->links() }}</div>
    </section>
</div>

<div class="modal" id="modalInquilino" hidden>
    <div class="modal-contenido">
        <button class="cerrar" id="cerrarModalInquilino">×</button>
        <h2 id="tituloInquilino">Detalle de inquilino</h2>
        <p id="datosInquilino" class="muted"></p>
        <div id="listaPropiedades"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/inquilinos.js') }}"></script>
@endsection