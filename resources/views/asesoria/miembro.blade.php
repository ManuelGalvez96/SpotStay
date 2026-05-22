@extends('layouts.miembro')
@section('title', 'Asesoría Legal - SpotStay')
@section('body-class', 'pagina-asesoria')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/gestor/asesoria.css') }}">
    <style>
        .hero-admin {
            background: linear-gradient(135deg, #035498, #1AA068);
            padding: 36px 40px;
            border-radius: 0 0 20px 20px;
            position: relative;
            overflow: hidden;
            margin: 0 0 24px 0;
        }
        .hero-content { position: relative; z-index: 2; }
        .hero-admin h1 {
            color: white; font-size: 26px; font-weight: 700; margin: 0;
        }
        .hero-admin p {
            color: rgba(255, 255, 255, 0.7); font-size: 14px; margin-top: 4px; margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Asesoría Legal</h1>
        <p>Preguntas frecuentes sobre alquiler, vivienda y normativa</p>
    </div>
</div>

<div class="asesoria-grid">
    @foreach($categorias as $categoria)
        <a href="{{ route('miembro.asesoria.categoria', $categoria->slug) }}" class="asesoria-card">
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
