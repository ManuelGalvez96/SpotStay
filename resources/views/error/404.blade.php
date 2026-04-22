<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404</title>
    <link rel="stylesheet" href="{{ asset('css/error/style-404.css') }}">
</head>

<body>

    <div class="background-city blurred"></div>

    <div class="error-container" id="mainContainer">

        <div class="yeti-scene">
            <svg id="yeti-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <circle class="yeti-part" cx="62" cy="52" r="14" />
                <circle class="yeti-part" cx="138" cy="52" r="14" />

                <path class="yeti-part" d="M40,200 Q40,50 100,50 Q160,50 160,200 Z" />

                <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
                <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
                <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />

                <g id="face-group">
                    <g id="eye-group">
                        <circle class="eye eye-l" cx="82" cy="105" r="5" />
                        <circle class="eye eye-r" cx="118" cy="105" r="5" />

                        <path class="eyelid eyelid-l" d="M77 100 Q82 95 87 100" />
                        <path class="eyelid eyelid-r" d="M113 100 Q118 95 123 100" />
                    </g>

                    <circle class="mouth-o" cx="100" cy="132" r="6" />
                </g>

                <circle class="hand hand-l" cx="35" cy="160" r="19" />
                <circle class="hand hand-r" cx="165" cy="160" r="19" />
            </svg>

            <div class="sign-404">
                <span class="code">404</span>
                <span class="label">ERROR</span>
            </div>
        </div>

        <div class="error-content">
            <h1 class="error-title">Parece que te has perdido</h1>
            <p class="error-message">Nuestra mascota Empresaria ha buscado por toda la pagina, pero no ha encontrado lo que buscas.</p>

            @php
            $urlInicio = url('/login');
            if (auth()->check()) {
            $user = auth()->user();
            if ($user->roles()->where('slug_rol', 'admin')->exists()) {
            $urlInicio = url('/admin/dashboard');
            } elseif ($user->roles()->whereIn('slug_rol', ['miembro', 'inquilino'])->exists()) {
            $urlInicio = url('/miembro/inicio');
            }
            }
            @endphp

            <div class="action-buttons">
                <a href="{{ $urlInicio }}" class="btn btn-secondary">Volver al Inicio</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/error/script-404.js') }}"></script>
</body>

</html>