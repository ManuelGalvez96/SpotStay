@extends($layout)
@section('titulo', $categoria->nombre . ' - Asesoría Legal - SpotStay')
@section('title', $categoria->nombre . ' - Asesoría Legal - SpotStay')
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
                <a href="{{ route($routePrefix . '.asesoria') }}" class="asesoria-back-link"><i class="bi bi-arrow-left"></i> Todas las categorías</a>
                <h1><i class="bi {{ $categoria->icono }}"></i> {{ $categoria->nombre }}</h1>
            </div>
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
        </div>

        <div class="asesoria-grid">
            <div class="card-admin card-con-franja">
                <div class="card-franja"></div>
                <div class="accordion" id="accordionCat">
                    @foreach($categoria->articulos as $articulo)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#art{{ $articulo->id }}">
                                    {{ $articulo->titulo }}
                                </button>
                            </h2>
                            <div id="art{{ $articulo->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionCat">
                                <div class="accordion-body">
                                    {!! $articulo->contenido !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
