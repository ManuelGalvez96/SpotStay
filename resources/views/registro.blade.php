<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/registro.css') }}">
    <title>Registro - SpotStay</title>
</head>

<body>
    <form class="form form-registro" method="POST" action="{{ url('/register') }}">
        @csrf

        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo-form">

        <h2 style="text-align: center; color: #2d79f3; margin-bottom: 20px;">Crea tu cuenta</h2>

        <div class="grid-campos">
            <!-- Grupo: Nombre -->
            <div class="field-group">
                <div class="flex-column">
                    <label for="nombre-usuario">Nombre completo</label>
                </div>
                <div class="inputForm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                    </svg>
                    <input type="text" id="nombre-usuario" name="nombre" class="input" placeholder="Tu nombre" value="{{ old('nombre') }}">
                </div>
                <span id="error-nombre" class="error-mensaje">@error('nombre') {{ $message }} @enderror</span>
            </div>


            <!-- Grupo: Email -->
            <div class="field-group">
                <div class="flex-column">
                    <label for="email-usuario">Correo electrónico</label>
                </div>
                <div class="inputForm">
                    <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg">
                        <path d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"></path>
                    </svg>
                    <input type="email" id="email-usuario" name="email" class="input" placeholder="Tu email" value="{{ old('email') }}">
                </div>
                <span id="error-email" class="error-mensaje">@error('email') {{ $message }} @enderror</span>
                <span id="disponibilidad-email" class="status-mensaje"></span>
            </div>


            <!-- Grupo: Password -->
            <div class="field-group">
                <div class="flex-column">
                    <label for="password-usuario">Contraseña</label>
                </div>
                <div class="inputForm">
                    <svg height="17" viewBox="-64 0 512 512" width="17" xmlns="http://www.w3.org/2000/svg" style="overflow: visible;">
                        <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
                        <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.1875 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.1875 16-16 16zm0 0"></path>
                    </svg>
                    <input type="password" id="password-usuario" name="password" class="input" placeholder="Mínimo 6 caracteres">
                    <svg id="toggle-password" viewBox="0 0 576 512" height="1em" xmlns="http://www.w3.org/2000/svg" style="cursor: pointer;">
                        <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                    </svg>
                </div>
                <span id="error-password" class="error-mensaje">@error('password') {{ $message }} @enderror</span>
            </div>

            <div class="field-group">
                <div class="flex-column">
                    <label for="password-confirmation-usuario">Confirmar contraseña</label>
                </div>
                <div class="inputForm">
                    <svg height="17" viewBox="-64 0 512 512" width="17" xmlns="http://www.w3.org/2000/svg" style="overflow: visible;">
                        <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
                        <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.1875 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.1875 16-16 16zm0 0"></path>
                    </svg>
                    <input type="password" id="password-confirmation-usuario" name="password_confirmation" class="input" placeholder="Repite tu contraseña">
                    <svg id="toggle-password-confirmation" viewBox="0 0 576 512" height="1em" xmlns="http://www.w3.org/2000/svg" style="cursor: pointer;">
                        <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                    </svg>
                </div>
                <span id="error-password-confirmation" class="error-mensaje"></span>
            </div>

        </div>

        <button type="submit" class="button-submit" id="boton-enviar" disabled>Registrarse</button>


        {{-- Contenedor de errores globales --}}
        @if ($errors->any())
        <div class="error-contenedor">
            <ul class="error-lista">
                @foreach ($errors->all() as $error)
                <li class="error-mensaje">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p class="p">¿Ya tienes cuenta? <a href="{{ url('/login') }}" class="span">Iniciar sesión</a></p>
    </form>

    <script src="{{ asset('js/registro.js') }}"></script>
</body>

</html>