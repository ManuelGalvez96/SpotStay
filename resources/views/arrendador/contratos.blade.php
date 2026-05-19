<<<<<<< HEAD
@extends('layouts.arrendador')

@section('titulo', 'Contratos Digitales - Arrendador')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/contratos.css') }}" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
=======
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos Digitales - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/arrendador/contratos.css') }}" />
</head>
<body>
<x-arrendador.topbar :arrendadorId="$arrendadorId" :avatarInicial="$avatarInicial" />
<div class="pagina">
    <header class="cabecera">
>>>>>>> 52478275de7aa6d1501b5e44374c8587a11d8ebf
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
                    <th>Firma arrendador</th>
                    <th>Firma inquilino</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contratos as $contrato)
                    @php
                        $estado = strtolower($contrato->estado_contrato ?? 'pendiente');
                        $firmadoArrendador = (bool) ($contrato->firmado_arrendador ?? false);
                        $firmadoInquilino = (bool) ($contrato->firmado_inquilino ?? false);
                    @endphp
                    <tr>
                        <td>#{{ $contrato->id_contrato }}<br><span class="muted">Alquiler #{{ $contrato->id_alquiler }}</span></td>
                        <td>
                            <strong>{{ $contrato->titulo_propiedad }}</strong><br>
                            <span class="muted">{{ $contrato->direccion_propiedad }}</span>
                        </td>
                        <td>{{ $contrato->nombre_inquilino }}</td>
                        <td id="firma-arrendador-{{ $contrato->id_contrato }}">
                            {{ $firmadoArrendador ? 'Firmado' : 'Pendiente' }}
                            @if ($firmadoArrendador && $contrato->fecha_firma_arrendador)
                                <br><span class="muted">{{ \Carbon\Carbon::parse($contrato->fecha_firma_arrendador)->format('d/m/Y H:i') }}</span>
                            @endif
                        </td>
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
                                @if (!$firmadoArrendador)
                                    <button
                                        class="btn-firmar"
                                        data-firmar-arrendador="{{ $contrato->id_contrato }}"
                                        data-arrendador="{{ $arrendadorId }}"
                                    >
                                        Firmar
                                    </button>
                                @else
                                    <span class="muted">Sin acciones</span>
                                @endif

                                @if (!empty($contrato->url_pdf_contrato))
                                    <a class="btn-ver" href="{{ route('arrendador.contratos.descargar-pdf', ['id' => $contrato->id_contrato, 'arrendador_id' => $arrendadorId]) }}" target="_blank">Ver Contrato</a>
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
                    <tr><td colspan="7">No hay contratos disponibles para este arrendador.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $contratos->withQueryString()->links() }}</div>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/contratos.js') }}"></script>
<<<<<<< HEAD
@endsection
=======
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
<script src="{{ asset('js/admin/layout.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/shared/swal-oso.js') }}"></script>
</body>
</html>
>>>>>>> 52478275de7aa6d1501b5e44374c8587a11d8ebf
