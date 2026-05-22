@extends('layouts.gestor')
@section('titulo', 'Asesoría Legal - SpotStay')

@section('css')
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

<div class="asesoria-grid">
    @include('asesoria._buscador')
    @foreach($categorias as $categoria)
        <a href="{{ route('gestor.asesoria.categoria', $categoria->slug) }}" class="asesoria-card">
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
@endsection
