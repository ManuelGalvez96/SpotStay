<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Solicitud de Alquiler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
</head>
<body class="pagina-miembro">
    <main class="contenido-miembro">
        <section class="seccion-listado solicitud-seccion">
            <div class="solicitud-contenedor">
                <aside class="panel-filtros-miembro solicitud-panel">
                    <h1 class="titulo-filtros">Formulario de solicitud de alquiler</h1>
                    <p class="descripcion-filtros">Rellena tus datos y envia la solicitud.</p>

                    <form action="{{ route('solicitud-alquiler.store') }}" method="POST" class="filtros-miembro solicitud-formulario">
                        @csrf

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="nombre">Nombre</label>
                            <input class="campo-filtro" type="text" id="nombre" name="nombre" required>
                        </div>

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="email">Correo electronico</label>
                            <input class="campo-filtro" type="email" id="email" name="email" required>
                        </div>

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="telefono">Numero de telefono</label>
                            <input class="campo-filtro" type="tel" id="telefono" name="telefono">
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="etiqueta-filtro" for="mensaje">Mensaje</label>
                            <textarea class="campo-filtro" id="mensaje" name="mensaje" rows="4"></textarea>
                        </div>

                        <button class="boton-aplicar solicitud-boton" type="submit">Enviar solicitud</button>
                    </form>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>