<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Solicitud Gestor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}?v=8" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    <link rel="stylesheet" href="{{ asset('css/shared/sweetalert-oso.css') }}" />
</head>

<body class="pagina-miembro">
    <main class="contenido-miembro">
        <section class="seccion-listado solicitud-seccion">
            <div class="solicitud-contenedor">
                <aside class="panel-filtros-miembro solicitud-panel">
                    <a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h1 class="titulo-filtros">Solicitud para convertirse en gestor</h1>
                    <p class="descripcion-filtros">Completa los datos para enviar tu solicitud de alta como gestor.</p>

                    @if (session('success'))
                        <div class="estado-vacio solicitud-alerta solicitud-alerta-ok">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="estado-vacio solicitud-alerta solicitud-alerta-error">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="estado-vacio solicitud-alerta solicitud-alerta-error">
                            <p>Revisa los campos del formulario.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('miembro.gestor.enviar') }}" class="filtros-miembro solicitud-formulario" id="formulario-solicitud-gestor" novalidate>
                        @csrf

                        <div class="grupo-filtro solicitud-columna-completa">
                            <div class="solicitud-resumen-datos">
                                <p class="solicitud-ayuda">Los datos personales se toman de tu perfil registrado.</p>
                                <ul class="solicitud-resumen-lista">
                                    <li><strong>Nombre:</strong> {{ auth()->user()->nombre_usuario ?? 'No disponible' }}</li>
                                    <li><strong>Email:</strong> {{ auth()->user()->email_usuario ?? 'No disponible' }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="etiqueta-filtro" for="descripcion-solicitud">Descripcion de la solicitud</label>
                            <textarea class="campo-filtro" id="descripcion-solicitud" name="descripcion_solicitud" rows="4">{{ old('descripcion_solicitud') }}</textarea>
                            <small id="error-descripcion-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="etiqueta-filtro" for="experiencia-solicitud">Experiencia previa</label>
                            <textarea class="campo-filtro" id="experiencia-solicitud" name="experiencia_solicitud" rows="3">{{ old('experiencia_solicitud') }}</textarea>
                            <small id="error-experiencia-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="solicitud-check-label">
                                <input type="checkbox" name="acepta_terminos_solicitud" id="acepta-terminos-solicitud" value="1" {{ old('acepta_terminos_solicitud') ? 'checked' : '' }}>
                                <span>Acepto los terminos y condiciones</span>
                            </label>
                            <small id="error-acepta-terminos-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="solicitud-check-label">
                                <input type="checkbox" name="acepta_veracidad_solicitud" id="acepta-veracidad-solicitud" value="1" {{ old('acepta_veracidad_solicitud') ? 'checked' : '' }}>
                                <span>Declaro que los datos proporcionados son veraces</span>
                            </label>
                            <small id="error-acepta-veracidad-solicitud" class="solicitud-error"></small>
                        </div>

                        <button class="boton-aplicar solicitud-boton btn-login-desabilitado" id="boton-enviar-solicitud" type="submit" disabled>Enviar solicitud</button>
                    </form>
                </aside>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('js/miembro/sweetalert_oso.js') }}"></script>
    <script src="{{ asset('js/miembro/solicitud_gestor.js') }}"></script>
</body>

</html>
