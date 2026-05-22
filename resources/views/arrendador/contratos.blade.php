@extends('layouts.arrendador')

@section('titulo', 'Contratos Digitales - Arrendador')

@section('css')
<link rel="stylesheet" href="{{ asset('css/arrendador/contratos.css') }}" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Contratos digitales</h1>
            <p class="subtitulo">Gestiona la firma de tus contratos activos.</p>
        </div>
    </header>

    <section class="kpis">
        <article class="kpi"><span>{{ $totales['total'] }}</span><small>Total</small></article>
        <article class="kpi"><span>{{ $totales['firmados'] }}</span><small>Firmados</small></article>
        <article class="kpi"><span>{{ $totales['pendientes'] }}</span><small>Pendientes</small></article>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Propiedad</th>
                    <th>Inquilino</th>
                    <th>Firma inquilino</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contratos as $contrato)
                    @php
                        $estado = strtolower($contrato->estado_contrato ?? 'pendiente');
                        $firmadoInquilino = (bool) ($contrato->firmado_inquilino ?? false);
                    @endphp
                    <tr>
                        <td>#{{ $contrato->id_contrato }}<br><span class="muted">Alquiler #{{ $contrato->id_alquiler }}</span></td>
                        <td>
                            <strong>{{ $contrato->titulo_propiedad }}</strong><br>
                            <span class="muted">{{ $contrato->direccion_propiedad }}</span>
                        </td>
                        <td>{{ $contrato->nombre_inquilino }}</td>
                        <td>
                            {{ $firmadoInquilino ? 'Firmado' : 'Pendiente' }}
                            @if ($firmadoInquilino && $contrato->fecha_firma_inquilino)
                                <br><span class="muted">{{ \Carbon\Carbon::parse($contrato->fecha_firma_inquilino)->format('d/m/Y H:i') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="estado estado-{{ $estado }}" id="estado-{{ $contrato->id_contrato }}">{{ ucfirst($estado) }}</span>
                        </td>
                        <td>
                            <div class="acciones" data-acciones="{{ $contrato->id_contrato }}">
                                {{-- Funcionalidad de firma eliminada: no mostrar botón "Firmar" --}}

                                @if (!empty($contrato->url_pdf_contrato) && (!isset($contrato->pdf_disponible) || $contrato->pdf_disponible))
                                    <a class="btn-ver" href="{{ route('arrendador.contratos.descargar-pdf', ['id' => $contrato->id_contrato, 'arrendador_id' => $arrendadorId]) }}">Ver Contrato</a>
                                @endif

                                <form method="POST" action="{{ route('arrendador.contratos.subir-pdf', ['id' => $contrato->id_contrato, 'arrendador_id' => $arrendadorId]) }}" enctype="multipart/form-data" class="form-subir-pdf" style="display:inline">
                                    @csrf
                                    <input type="file" name="pdf_contrato" accept=".pdf" required style="display:none" id="pdf-input-{{ $contrato->id_contrato }}">
                                    <button type="button" class="btn-subir-pdf" data-contrato="{{ $contrato->id_contrato }}">Subir Contrato</button>
                                </form> 
                            </div>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="7">No hay contratos disponibles para este arrendador.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $contratos->withQueryString()->links() }}</div>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/contratos.js') }}"></script>
@endsection