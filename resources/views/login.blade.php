<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <title>Iniciar Sesión - SpotStay</title>
</head>

<body>
    <form class="form" method="POST" action="{{ url('/login') }}">
        @csrf

        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo-form">
        <div class="flex-column">
            <label for="email-usuario">Correo electrónico</label>
        </div>
        <div class="inputForm">
            <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg">
                <g id="capa-email" data-name="Capa Email">
                    <path d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z"></path>
                </g>
            </svg>
            <input type="email" id="email-usuario" name="email" class="input" placeholder="Introduce tu correo electrónico" value="{{ old('email') }}">
        </div>
        <span id="error-email" class="error-mensaje">@error('email') {{ $message }} @enderror</span>



        <div class="flex-column">
            <label for="password-usuario">Contraseña</label>
        </div>
        <div class="inputForm">
            <svg height="17" viewBox="-64 0 512 512" width="17" xmlns="http://www.w3.org/2000/svg" style="overflow: visible;">
                <path d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0"></path>
                <path d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.1875 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.1875 16-16 16zm0 0"></path>
            </svg>
            <input type="password" id="password-usuario" name="password" class="input" placeholder="Introduce tu contraseña">
            <span id="ver-password" style="cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                </svg>
            </span>
        </div>
        </div>
        <span id="error-password" class="error-mensaje">@error('password') {{ $message }} @enderror</span>



        <div class="flex-row" style="justify-content: flex-end;">
            <span class="span">¿Has olvidado tu contraseña?</span>
        </div>

        <button type="submit" class="button-submit" id="boton-enviar">Iniciar sesión</button>

        {{-- Contenedor de errores y mensajes debajo del botón --}}
        @if ($errors->any())
        <div class="error-contenedor">
            <ul class="error-lista">
                @foreach ($errors->all() as $error)
                <li class="error-mensaje">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('error'))
        <div class="error-contenedor error-mensaje">
            {!! session('error') !!}
        </div>
        @endif

        @if(session('status'))
        <div class="status-contenedor">
            <div class="status-mensaje">
                {!! session('status') !!}
            </div>
        </div>
        @endif


        <p class="p">¿No tienes cuenta? <a href="{{ url('/register') }}" class="span">Registrarse</a></p>
    </form>
</body>
<script src="{{ asset('js/login.js') }}"></script>

</html> -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SpotStay | Iniciar Sesión</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        /* Estilos para errores y validaciones (se mantienen en el Blade por petición del usuario) */
        .error-mensaje {
            color: #ff4d4d;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
            text-align: left;
        }

        .disponibilidad-mensaje {
            color: #1AA068;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
            text-align: left;
        }

        .btn-login-desabilitado {
            opacity: 0.5;
            cursor: not-allowed !important;
            filter: grayscale(1);
        }
    </style>
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
            <span class="logo-text">SpotStay</span>
            <span class="subtitle">Luxury Rentals Management</span>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="Email Corporativo" oninput="handleMove(this.value)" onblur="resetFace()">
                </div>
                <div class="input-wrapper">
                    <input type="password" id="pass" name="password" placeholder="Contraseña" onfocus="checkState(this); container.classList.add('hiding-pass')" onblur="container.classList.remove('hiding-pass')">
                    <button class="toggle-pass" type="button" onmousedown="handleToggle(event, 'pass')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                        </svg>
                    </button>
                </div>
                <button type="submit" class="btn-submit">Iniciar Sesión</button>
            </form>
            <a href="{{ url('/register') }}" class="nav-link">¿No tienes cuenta? <b>Regístrate aquí</b></a>
        </div>
    </div>
    <script>
        const container = document.getElementById('mainContainer');
        const face = document.getElementById('face-group');

        function checkState(el) {
            el.type === 'text' ? container.classList.add('peek-active') : container.classList.remove('peek-active');
        }

        function handleToggle(e, id) {
            e.preventDefault();
            const i = document.getElementById(id);
            i.type = i.type === 'password' ? 'text' : 'password';
            checkState(i);
            i.focus();
        }

        function handleMove(v) {
            face.style.transform = `translateX(${Math.min(Math.max((v.length - 12) * 0.6, -8), 8)}px)`;
        }

        function resetFace() {
            face.style.transform = `translateX(0px)`;
        }
    </script>
</body>

</html>