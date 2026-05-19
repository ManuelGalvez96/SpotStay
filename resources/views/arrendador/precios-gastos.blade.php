<<<<<<< HEAD
@extends('layouts.arrendador')

@section('titulo', 'Precios y gastos - Arrendador')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/precios-gastos.css') }}" />
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
    <title>Precios y gastos - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/arrendador/precios-gastos.css') }}" />
</head>
<body>
<x-arrendador.topbar :arrendadorId="$arrendadorId" :avatarInicial="$avatarInicial" />
<div class="pagina">
    <header class="cabecera">
>>>>>>> 52478275de7aa6d1501b5e44374c8587a11d8ebf
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Precios y gastos</h1>
            <p class="subtitulo">Configura el precio por propiedad de forma independiente.</p>
        </div>
    </header>

    <section class="kpis">
        <article class="kpi"><span>{{ $totalPropiedades }}</span><small>Propiedades totales</small></article>
        <article class="kpi"><span>{{ number_format($precioMedio, 2, ',', '.') }} €</span><small>Precio medio mensual</small></article>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
            <tr>
                <th>Propiedad</th>
                <th>Estado</th>
                <th>Configuración</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($propiedades as $propiedad)
                <tr>
                    <td>
                        <strong>{{ $propiedad->titulo_propiedad }}</strong>
                        <div class="muted">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</div>
                    </td>
                    <td><span class="estado">{{ ucfirst($propiedad->estado_propiedad) }}</span></td>
                    <td>
                        <form class="form-precios" data-form-precios="true" action="{{ route('arrendador.precios-gastos.actualizar', ['id' => $propiedad->id_propiedad, 'arrendador_id' => $arrendadorId]) }}" method="POST">
                            @csrf
                            <div class="campo-precio">
                                <label>Precio mensual</label>
                                <div class="input-prefijo">
                                    <span>EUR</span>
                                    <input type="number" step="0.01" min="0" name="precio_propiedad" value="{{ old('precio_propiedad', $propiedad->precio_propiedad) }}" required>
                                </div>
                            </div>
                            <div class="acciones-formulario">
                                <div class="resumen-mensual" data-resumen-mensual>
                                    <small>Total mensual estimado</small>
                                    <strong data-total-mensual>--</strong>
                                    <span class="muted" data-estado-gastos>El total se calcula con el precio mensual.</span>
                                </div>
                                <button type="submit" class="btn-guardar">
                                    <span class="texto-boton">Guardar cambios</span>
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No tienes propiedades todavía. Primero publica una en el módulo de propiedades.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $propiedades->withQueryString()->links() }}</div>
    </section>
</div>

<div id="toastPrecios" class="toast" hidden></div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/precios-gastos.js') }}"></script>
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
