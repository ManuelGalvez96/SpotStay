@extends('layouts.arrendador')

@section('titulo', $propiedad->titulo_propiedad . ' - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/propiedad-ver.css') }}">
@endsection

@section('content')
<div class="property-container pt-0">
    <div class="page-header">
        <h1>{{ $propiedad->titulo_propiedad }}</h1>
        <p>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · {{ $propiedad->codigo_postal_propiedad }}</p>
        <div class="nav-links">
            <a href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId]) }}">← Volver a propiedades</a>
            <a href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}">Editar</a>
        </div>
    </div>

    @if (count($fotos) > 0)
    <div class="gallery">
        <div class="gallery-main" id="galeria-principal">
            <img src="{{ asset('img/' . $fotos->first()->ruta_foto) }}" alt="{{ $propiedad->titulo_propiedad }}" />
        </div>
        @if (count($fotos) > 1)
        <div class="gallery-thumbnails">
            @foreach ($fotos as $foto)
            <div class="thumbnail" onclick="cambiarGaleria('{{ asset('img/' . $foto->ruta_foto) }}')">
                <img src="{{ asset('img/' . $foto->ruta_foto) }}" alt="Miniatura" />
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @else
    <div class="gallery">
        <div class="gallery-main">
            <span>Sin imágenes</span>
        </div>
    </div>
    @endif

    <div class="property-details">
        <div class="detail-row">
            <div class="detail-item">
                <div class="detail-label">Estado</div>
                <div class="detail-value">
                    <span class="badge badge-{{ $propiedad->estado_propiedad }}">{{ ucfirst($propiedad->estado_propiedad) }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Precio</div>
                <div class="detail-value">{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} €/mes</div>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <div class="detail-label">Ciudad</div>
                <div class="detail-value">{{ $propiedad->ciudad_propiedad }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Código Postal</div>
                <div class="detail-value">{{ $propiedad->codigo_postal_propiedad }}</div>
            </div>
        </div>

        @if ($propiedad->descripcion_propiedad)
        <div class="description">
            <div class="description-title">Descripción</div>
            <div class="description-text">{{ $propiedad->descripcion_propiedad }}</div>
        </div>
        @endif

        @if ($alquilerActivo)
        <div class="rental-section">
            <div class="rental-title">Alquiler Activo</div>
            <div class="rental-info">
                <div class="rental-item">
                    <div class="rental-item-label">Inquilino</div>
                    <div class="rental-item-value">{{ $alquilerActivo->nombre_inquilino }}</div>
                </div>
                <div class="rental-item">
                    <div class="rental-item-label">Email Inquilino</div>
                    <div class="rental-item-value">{{ $alquilerActivo->email_inquilino }}</div>
                </div>
                <div class="rental-item">
                    <div class="rental-item-label">Inicio</div>
                    <div class="rental-item-value">{{ \Carbon\Carbon::parse($alquilerActivo->fecha_inicio_alquiler)->format('d/m/Y') }}</div>
                </div>
                <div class="rental-item">
                    <div class="rental-item-label">Precio Alquiler</div>
                    <div class="rental-item-value">{{ $alquilerActivo->precio_alquiler ? number_format((float) $alquilerActivo->precio_alquiler, 2, ',', '.') . ' €' : 'N/A' }}</div>
                </div>
            </div>
        </div>
        @endif

        <div class="actions">
            <a href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}" class="btn btn-primary">Editar Propiedad</a>
            <a href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId]) }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function cambiarGaleria(src) {
        document.getElementById('galeria-principal').innerHTML = '<img src="' + src + '" alt="Propiedad" />';
    }
</script>
@endsection