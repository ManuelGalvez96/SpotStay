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
    <link rel="stylesheet" href="{{ asset('css/arrendador/gestor.css') }}" />
</head>
<body>
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Gestor inmobiliario</h1>
            <p class="subtitulo">Define quien gestiona cada propiedad y guarda los cambios al instante.</p>
        </div>
        <div class="acciones-cabecera">
            <div class="avatar">{{ $avatarInicial }}</div>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
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
                        <div class="muted">{{ $propiedad->email_gestor ?: 'Sin email' }}</div>
                    </td>
                    <td>
                        <form
                            class="form-gestor"
                            data-form-gestor="true"
                            action="{{ route('arrendador.gestor.actualizar', ['id' => $propiedad->id_propiedad, 'arrendador_id' => $arrendadorId]) }}"
                            method="POST"
                        >
                            @csrf
                            <select name="id_gestor_fk" required>
                                @foreach ($gestoresDisponibles as $gestor)
                                    <option value="{{ $gestor->id_usuario }}" @selected((int) $propiedad->id_gestor_fk === (int) $gestor->id_usuario)>
                                        {{ $gestor->nombre_usuario }}{{ $gestor->email_usuario ? ' - ' . $gestor->email_usuario : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-guardar"><span class="texto-boton">Guardar</span></button>
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

<script src="{{ asset('js/arrendador/gestor.js') }}"></script>
</body>
</html>
