<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Detalle propiedad</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
</head>

<body class="pagina-miembro">
    <header class="encabezado-miembro" id="encabezado-miembro">
        <div class="contenedor-encabezado-miembro">
            <div class="marca-miembro">
                <img src="/img/logo.png" alt="SpotStay" />
            </div>
            <div class="acciones-miembro"></div>
        </div>
    </header>

    <main class="contenido-miembro contenido-detalle">
        @if ($propiedad)
        <section class="detalle-cabecera">
            <a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <h1 class="detalle-titulo">{{ $propiedad->titulo_propiedad }}</h1>
        </section>

        <section class="detalle-seccion detalle-collage">
            @if (isset($fotosPropiedad) && $fotosPropiedad->count() > 0)
            @php
            $fotoPrincipal = $fotosPropiedad->first();
            $fotosSecundarias = $fotosPropiedad->slice(1, 4);
            @endphp

            <div class="collage-grid">
                <div class="collage-principal">
                    <img
                        src="{{ asset('storage/' . $fotoPrincipal->ruta_foto) }}"
                        alt="Imagen principal de la propiedad" />
                </div>

                <div class="collage-secundarias">
                    @foreach ($fotosSecundarias as $foto)
                    <div class="collage-miniatura">
                        <img
                            src="{{ asset('storage/' . $foto->ruta_foto) }}"
                            alt="Imagen de la propiedad" />
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <span>Esta propiedad aun no tiene imagenes.</span>
            @endif
        </section>

        <section class="detalle-resumen">
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Dueño</span>
                <span class="detalle-resumen-valor">Pendiente</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Precio</span>
                <span class="detalle-resumen-valor">
                    {{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} €
                </span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Habitaciones</span>
                <span class="detalle-resumen-valor">N/D</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Metros cuadrados</span>
                <span class="detalle-resumen-valor">N/D</span>
            </div>
        </section>

        <section class="detalle-info">
            <div class="detalle-descripcion">
                <h2>Descripcion</h2>
                <p>
                    {{ $propiedad->descripcion_propiedad ?? 'Sin descripcion disponible.' }}
                </p>
            </div>
            <div class="detalle-extra">
                <h2>Mas detalles de la vivienda</h2>
            </div>
        </section>

        <section class="detalle-contacto">
            <button class="boton-contacto" type="button">
                Boton para contactar <i class="bi bi-chat-left-text"></i>
            </button>
        </section>

        <section class="detalle-seccion detalle-mapa">
            <span>Mapa con la ubicacion de la propiedad</span>
        </section>

        <section class="detalle-seccion detalle-similares">
            <span>Carrousel de propiedades similares</span>
        </section>
        @else
        <div class="estado-vacio">
            <p>No se encontro la propiedad solicitada.</p>
        </div>
        @endif
    </main>
</body>

</html>