<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrar Propiedad</title>
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="pagina-miembro">
    <main class="contenido-miembro">
        <section class="seccion-mapa-previsualizacion registro-propiedad-panel">
            <div class="cabecera-seccion">
                <a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>
                <h1 class="titulo-principal">Registrar propiedad</h1>
                <p class="descripcion-principal">Completa los datos y selecciona la ubicacion en el mapa.</p>
            </div>

            <form action="" method="POST" id="form-registrar-propiedad" class="form-registro-propiedad">
                @csrf

                <label for="titulo" class="etiqueta-filtro">Titulo de la propiedad</label>
                <input type="text" id="titulo" name="titulo" class="campo-filtro" required>

                <label for="descripcion" class="etiqueta-filtro">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="campo-filtro"></textarea>

                <div class="fila-campos">
                    <div class="grupo-filtro">
                        <label for="precio" class="etiqueta-filtro">Precio</label>
                        <input type="number" id="precio" name="precio" class="campo-filtro" min="0" step="0.01" required>
                    </div>
                    <div class="grupo-filtro">
                        <label for="ciudad" class="etiqueta-filtro">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" class="campo-filtro" required>
                    </div>
                </div>

                <div class="grupo-filtro">
                    <label class="etiqueta-filtro" for="mapa-registro">Ubicacion en el mapa</label>
                    <div id="mapa-registro" class="mapa-registro-propiedad"></div>
                </div>

                <div class="fila-campos">
                    <div class="grupo-filtro">
                        <label for="latitud_propiedad" class="etiqueta-filtro">Latitud</label>
                        <input type="number" step="0.0000001" id="latitud_propiedad" name="latitud_propiedad" class="campo-filtro" readonly required>
                    </div>
                    <div class="grupo-filtro">
                        <label for="longitud_propiedad" class="etiqueta-filtro">Longitud</label>
                        <input type="number" step="0.0000001" id="longitud_propiedad" name="longitud_propiedad" class="campo-filtro" readonly required>
                    </div>
                </div>
                <div class="grupo-filtro">
                    <label for="direccion_propiedad" class="etiqueta-filtro">Direccion</label>
                    <input type="text" id="direccion_propiedad" name="direccion_propiedad" class="campo-filtro" readonly required>
                </div>

                <button type="submit" class="boton-aplicar">Registrar propiedad</button>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/miembro/registrar_propiedad.js') }}"></script>
</body>
</html>