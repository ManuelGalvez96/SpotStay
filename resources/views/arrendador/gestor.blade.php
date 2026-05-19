<<<<<<< HEAD
@extends('layouts.arrendador')

@section('titulo', 'Gestor inmobiliario - Arrendador')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/gestor.css') }}" />
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
    <title>Gestor inmobiliario - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/arrendador/gestor.css') }}" />
</head>
<body>
<x-arrendador.topbar :arrendadorId="$arrendadorId" :avatarInicial="$avatarInicial" />
<div class="pagina">
    <header class="cabecera">
>>>>>>> 52478275de7aa6d1501b5e44374c8587a11d8ebf
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Gestor inmobiliario</h1>
            <p class="subtitulo">Define quien gestiona cada propiedad y guarda los cambios al instante.</p>
        </div>
    </header>

    <section class="kpis">
        <article class="kpi"><span>{{ $totalPropiedades }}</span><small>Propiedades totales</small></article>
        <article class="kpi"><span>{{ $conGestorExterno }}</span><small>Con gestor externo</small></article>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
            <tr>
                <th>Propiedad</th>
                <th>Estado</th>
                <th>Gestor actual</th>
                <th>Asignar gestor</th>
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
                        <strong data-nombre-gestor>{{ $propiedad->nombre_gestor ?: 'Sin gestor' }}</strong>
                        <div class="muted" data-email-gestor>{{ $propiedad->email_gestor ?: 'Sin email' }}</div>
                    </td>
                    <td>
                        <form
                            class="form-gestor"
                            data-form-gestor="true"
                            action="{{ route('arrendador.gestor.actualizar', ['id' => $propiedad->id_propiedad, 'arrendador_id' => $arrendadorId]) }}"
                            method="POST"
                        >
                            @csrf
                            <select name="id_gestor_fk">
                                <option value="" @selected(empty($propiedad->id_gestor_fk))>Sin gestor asignado</option>
                                @foreach ($gestoresDisponibles as $gestor)
                                    <option value="{{ $gestor->id_usuario }}" @selected((int) $propiedad->id_gestor_fk === (int) $gestor->id_usuario)>
                                        {{ $gestor->nombre_usuario }}{{ $gestor->email_usuario ? ' - ' . $gestor->email_usuario : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="acciones-gestor">
                                <button type="submit" class="btn-guardar"><span class="texto-boton">Guardar</span></button>
                                <button type="button" class="btn-desasignar" data-desasignar-gestor="true">Desasignar</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No tienes propiedades todavía. Primero crea propiedades para poder asignar gestores.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $propiedades->withQueryString()->links() }}</div>
    </section>
</div>

<div id="toastGestor" class="toast" hidden></div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/gestor.js') }}"></script>
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
