<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Solicitud Arrendador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
</head>

<body class="pagina-miembro">
    <main class="contenido-miembro">
        <section class="seccion-listado solicitud-seccion">
            <div class="solicitud-contenedor">
                <aside class="panel-filtros-miembro solicitud-panel">
                    <a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h1 class="titulo-filtros">Solicitud para convertirse en arrendador</h1>
                    <p class="descripcion-filtros">Completa los datos para enviar tu solicitud de alta como arrendador.</p>

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

                    <form method="POST" action="{{ route('miembro.arrendador.enviar') }}" class="filtros-miembro solicitud-formulario" id="formulario-solicitud-arrendador" novalidate>
                        @csrf

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="telefono-solicitud">Telefono de contacto</label>
                            <input class="campo-filtro" id="telefono-solicitud" name="telefono_solicitud" type="text" value="{{ old('telefono_solicitud') }}" placeholder="+34 600123456">
                            <small id="error-telefono-solicitud" class="solicitud-error"></small>
                            <small class="solicitud-ayuda">Formato: +34 600123456</small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="fecha-nacimiento-solicitud">Fecha de nacimiento</label>
                            <input class="campo-filtro" id="fecha-nacimiento-solicitud" name="fecha_nacimiento_solicitud" type="date" value="{{ old('fecha_nacimiento_solicitud') }}">
                            <small id="error-fecha-nacimiento-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="tipo-documento-solicitud">Tipo de documento</label>
                            <select class="campo-filtro" id="tipo-documento-solicitud" name="tipo_documento_solicitud">
                                <option value="">Selecciona una opcion</option>
                                <option value="DNI" {{ old('tipo_documento_solicitud') === 'DNI' ? 'selected' : '' }}>DNI</option>
                                <option value="NIE" {{ old('tipo_documento_solicitud') === 'NIE' ? 'selected' : '' }}>NIE</option>
                                <option value="PASAPORTE" {{ old('tipo_documento_solicitud') === 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                            <small id="error-tipo-documento-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="numero-documento-solicitud">Numero de documento</label>
                            <input class="campo-filtro" id="numero-documento-solicitud" name="numero_documento_solicitud" type="text" value="{{ old('numero_documento_solicitud') }}">
                            <small id="error-numero-documento-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="iban-solicitud">IBAN</label>
                            <input class="campo-filtro" id="iban-solicitud" name="iban_solicitud" type="text" value="{{ old('iban_solicitud') }}">
                            <small id="error-iban-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="titular-cuenta-solicitud">Titular de la cuenta</label>
                            <input class="campo-filtro" id="titular-cuenta-solicitud" name="titular_cuenta_solicitud" type="text" value="{{ old('titular_cuenta_solicitud') }}">
                            <small id="error-titular-cuenta-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="nif-solicitud">NIF</label>
                            <input class="campo-filtro" id="nif-solicitud" name="nif_solicitud" type="text" value="{{ old('nif_solicitud') }}">
                            <small id="error-nif-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-doble">
                            <label class="etiqueta-filtro" for="direccion-fiscal-solicitud">Direccion fiscal</label>
                            <input class="campo-filtro" id="direccion-fiscal-solicitud" name="direccion_fiscal_solicitud" type="text" value="{{ old('direccion_fiscal_solicitud') }}">
                            <small id="error-direccion-fiscal-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="tipo-arrendador-solicitud">Tipo de arrendador</label>
                            <select class="campo-filtro" id="tipo-arrendador-solicitud" name="tipo_arrendador_solicitud">
                                <option value="">Selecciona una opcion</option>
                                <option value="particular" {{ old('tipo_arrendador_solicitud') === 'particular' ? 'selected' : '' }}>Particular</option>
                                <option value="empresa" {{ old('tipo_arrendador_solicitud') === 'empresa' ? 'selected' : '' }}>Empresa</option>
                            </select>
                            <small id="error-tipo-arrendador-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="num-propiedades-previstas-solicitud">Numero de propiedades previstas</label>
                            <input class="campo-filtro" id="num-propiedades-previstas-solicitud" name="num_propiedades_previstas_solicitud" type="number" value="{{ old('num_propiedades_previstas_solicitud') }}">
                            <small id="error-num-propiedades-previstas-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="etiqueta-filtro" for="descripcion-solicitud">Descripcion de la solicitud</label>
                            <textarea class="campo-filtro" id="descripcion-solicitud" name="descripcion_solicitud" rows="4">{{ old('descripcion_solicitud') }}</textarea>
                            <small id="error-descripcion-solicitud" class="solicitud-error"></small>
                        </div>

                        <div class="grupo-filtro solicitud-columna-completa">
                            <label class="solicitud-check-label">
                                <input type="checkbox" name="es_propietario_solicitud" value="1" {{ old('es_propietario_solicitud') ? 'checked' : '' }}>
                                <span>Soy propietario de al menos una vivienda</span>
                            </label>
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
    <script src="{{ asset('js/miembro/solicitud_arrendador.js') }}"></script>
</body>

</html>