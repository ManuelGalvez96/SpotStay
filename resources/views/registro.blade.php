<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SpotStay | Registro Corporativo</title>
    <link rel="stylesheet" href="{{ asset('css/registro.css') }}">
</head>

<body>

    <div class="background-city"></div>

    <div class="login-card" id="mainContainer">
        <div class="yeti-wrapper">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <circle class="yeti-part" cx="62" cy="52" r="14" />
                <circle class="yeti-part" cx="138" cy="52" r="14" />
                <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
                <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
                <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
                <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
                <g id="face-group">
                    <circle cx="82" cy="105" r="5" fill="#000" />
                    <circle cx="118" cy="105" r="5" fill="#000" />
                    <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <circle class="hand hand-l" cx="48" cy="180" r="19" />
                <circle class="hand hand-r" cx="152" cy="180" r="19" />
            </svg>
        </div>

        <div class="form-content">
            <span class="logo-text">Crea tu cuenta</span>
            <span class="subtitle">Únete al equipo de SpotStay</span>

            {{-- Alertas Globales de Laravel --}}
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="grid-container">
                    <div class="contenedor-entrada full-width">
                        <div class="input-wrapper">
                            <input type="text" id="nombre-usuario" name="nombre" placeholder="Nombre Completo" value="{{ old('nombre') }}">
                        </div>
                        <span id="error-nombre" class="error-mensaje"></span>
                    </div>

                    <div class="contenedor-entrada">
                        <div class="input-wrapper">
                            <input type="email" id="email-usuario" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}">
                        </div>
                        <span id="error-email" class="error-mensaje"></span>
                        <span id="disponibilidad-email" class="disponibilidad-mensaje"></span>
                    </div>

                    <div class="contenedor-entrada">
                        <div class="input-wrapper">
                            <input type="tel" id="telefono-usuario" name="telefono" placeholder="Teléfono" value="{{ old('telefono') }}">
                        </div>
                        <span id="error-telefono" class="error-mensaje"></span>
                        <span id="disponibilidad-telefono" class="disponibilidad-mensaje"></span>
                    </div>

                    <div class="contenedor-entrada">
                        <div class="input-wrapper">
                            <input type="password" id="password-usuario" name="password" placeholder="Contraseña">
                            <button class="toggle-pass" type="button" id="ver-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg>
                            </button>
                        </div>
                        <span id="error-password" class="error-mensaje"></span>
                    </div>

                    <div class="contenedor-entrada">
                        <div class="input-wrapper">
                            <input type="password" id="password-confirmation-usuario" name="password_confirmation" placeholder="Confirmar Contraseña">
                            <button class="toggle-pass" type="button" id="ver-password-confirmacion">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                </svg>
                            </button>
                        </div>
                        <span id="error-password-confirmation" class="error-mensaje"></span>
                    </div>

                    <div class="full-width">
                        <button class="btn-submit btn-login-desabilitado" id="boton-enviar" disabled>Registrarse</button>
                    </div>
                </div>
            </form>
            <a href="{{ url('login') }}" class="nav-link">¿Ya tienes cuenta? <b>Inicia sesión</b></a>
        </div>
    </div>

    <script src="{{ asset('js/registro.js') }}"></script>

</body>

</html>