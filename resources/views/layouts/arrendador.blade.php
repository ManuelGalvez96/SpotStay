<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <title>@yield('titulo', 'Arrendador - SpotStay')</title>

    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}?v=8">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}?v=2">
    @yield('css')
</head>

<body class="pagina-miembro @yield('body-class')">
    @php
    $arrendadorIdNav = $arrendadorId ?? request('arrendador_id');
    @endphp

    @include('miembro.partials.header')
    @include('miembro.partials.nav')

    <main class="contenido-miembro">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/shared/swal-oso.js') }}"></script>

    @yield('scripts')
</body>

</html>