@extends($layout)
@section('titulo', 'Asesoría Legal - SpotStay')
@section('title', 'Asesoría Legal - SpotStay')
@section('body-class', 'pagina-asesoria')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/gestor/asesoria.css') }}">
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/gestor/asesoria.css') }}">
@endsection
@section('content')
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
            @include('asesoria._buscador')
            @foreach($categorias as $categoria)
                <a href="{{ route($routePrefix . '.asesoria.categoria', $categoria->slug) }}" class="asesoria-card">
                    <div class="asesoria-card-icono">
                        <i class="bi {{ $categoria->icono }}"></i>
                    </div>
                    <div class="asesoria-card-contenido">
                        <h3>{{ $categoria->nombre }}</h3>
                        <span class="asesoria-card-count">{{ $categoria->articulos_count }} {{ $categoria->articulos_count === 1 ? 'artículo' : 'artículos' }}</span>
                    </div>
                    <i class="bi bi-chevron-right asesoria-card-flecha"></i>
                </a>
            @endforeach
        </div>
    </main>
</div>
@endsection
