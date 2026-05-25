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
<div class="hero-admin">
    <div class="hero-content">
        <h1>Asesoría Legal</h1>
        <p>Preguntas frecuentes sobre alquiler, vivienda y normativa</p>
</div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="asesoria-back-wrap">
    <a href="{{ route($routePrefix . '.asesoria') }}" class="asesoria-back-link">
        <span class="asesoria-back-icono"><i class="bi bi-arrow-left"></i></span>
        Todas las categorías
    </a>
</div>

<div class="asesoria-grid">
    <div class="asesoria-buscador">
        <i class="bi bi-search asesoria-buscador-icono"></i>
        <input type="text" class="asesoria-buscador-input" placeholder="Buscar artículos…" data-search-endpoint="{{ route($routePrefix . '.asesoria.buscar') }}">
        <div class="asesoria-sugerencias"></div>
    </div>
    <h2 class="asesoria-categoria-titulo"><i class="bi {{ $categoria->icono }}"></i> {{ $categoria->nombre }}</h2>
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
@include('asesoria.partials.chatbot-widget', [
    'chatbotIniciarUrl' => route($routePrefix . '.asesoria.chatbot.iniciar'),
    'chatbotMensajeUrl' => route($routePrefix . '.asesoria.chatbot.mensaje'),
    'chatbotHistorialUrl' => route($routePrefix . '.asesoria.chatbot.historial'),
])
@endsection

@section('scripts')
    <script src="{{ asset('js/gestor/asesoria-buscador.js') }}"></script>
    <script src="{{ asset('js/gestor/asesoria-categoria.js') }}"></script>
@endsection
