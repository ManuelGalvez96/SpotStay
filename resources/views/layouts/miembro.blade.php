<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SpotStay | @yield('title', 'Tu hogar ideal')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}?v=8" />
    @yield('styles')
</head>

<body class="pagina-miembro @yield('body-class')">

    @include('miembro.partials.header')
    @include('miembro.partials.nav')

    @if (session('success') || session('error') || session('info'))
        <div class="container pt-3">
                <div class="d-flex justify-content-center">
                    @if (session('success'))
                        <div class="alert alert-success text-center mx-auto mb-1" style="max-width:60%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:1.05rem; padding:.5rem 1rem; line-height:1.2;">
                            {!! session('success') !!}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger text-center mx-auto mb-1" style="max-width:60%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:1.05rem; padding:.5rem 1rem; line-height:1.2;">
                            {!! session('error') !!}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info text-center mx-auto mb-1" style="max-width:60%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:1.05rem; padding:.5rem 1rem; line-height:1.2;">
                            {!! session('info') !!}
                        </div>
                    @endif
                </div>
        </div>
    @endif

    <main class="contenido-miembro">
        @if(session('info'))
            <div class="container mt-3">
                <div class="alert alert-info mb-0">
                    {{ session('info') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger mb-0">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script src="{{ asset('js/miembro/miembro.js') }}?v=3"></script>
</body>

</html>