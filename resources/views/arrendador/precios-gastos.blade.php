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
    <link rel="stylesheet" href="{{ asset('css/arrendador/precios-gastos.css') }}" />
</head>
<body>
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Precios y gastos</h1>
            <p class="subtitulo">Configura importes y gastos por propiedad de forma independiente.</p>
        </div>
        <div class="acciones-cabecera">
            <div class="avatar">{{ $avatarInicial }}</div>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
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
                            <div class="campo-gastos">
                                <label>Gastos</label>
                                <textarea name="gastos_propiedad" rows="2" placeholder='Ej: {"agua":30,"luz":45} o texto libre'>{{ old('gastos_propiedad', $propiedad->gastos_propiedad) }}</textarea>
                                <small class="muted">Puedes usar JSON o texto simple.</small>
                            </div>
                            <div class="acciones-formulario">
                                <div class="resumen-mensual" data-resumen-mensual>
                                    <small>Total mensual estimado</small>
                                    <strong data-total-mensual>--</strong>
                                    <span class="muted" data-estado-gastos>Completa los campos para calcular.</span>
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

<script src="{{ asset('js/arrendador/precios-gastos.js') }}"></script>
</body>
</html>
