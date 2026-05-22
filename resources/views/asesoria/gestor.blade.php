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
