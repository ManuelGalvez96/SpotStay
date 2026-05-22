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
@endsection
