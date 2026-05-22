<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asesoría Legal - SpotStay</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/asesoria.css') }}">
    <link rel="stylesheet" href="{{ asset('css/arrendador/dashboard.css') }}">
</head>
<body>
    <x-arrendador.topbar :arrendadorId="$arrendadorId ?? null" :avatarInicial="$avatarInicial ?? 'A'" />

    <div class="dashboard-wrapper">
        <main class="main-content">
            <div class="hero-admin">
                <div class="hero-content">
                    <h1>Asesoría Legal</h1>
                    <p>Preguntas frecuentes sobre alquiler, vivienda y normativa</p>
                </div>
                <div class="hero-deco hero-deco-1"></div>
                <div class="hero-deco hero-deco-2"></div>
                <div class="hero-deco hero-deco-3"></div>
            </div>

            <div class="asesoria-grid">
                @foreach($categorias as $categoria)
                    <div class="card-admin card-con-franja">
                        <div class="card-franja"></div>
                        <div class="card-header-admin card-header-gradient">
                            <span><i class="bi {{ $categoria->icono }}"></i> {{ $categoria->nombre }}</span>
                        </div>
                        <div class="accordion" id="accordionCat{{ $categoria->id }}">
                            @foreach($categoria->articulos as $articulo)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#art{{ $articulo->id }}">
                                            {{ $articulo->titulo }}
                                        </button>
                                    </h2>
                                    <div id="art{{ $articulo->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionCat{{ $categoria->id }}">
                                        <div class="accordion-body">
                                            {!! $articulo->contenido !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous"></script>
</body>
</html>
