@extends('layouts.arrendador')

@section('titulo', $propiedad->titulo_propiedad . ' - SpotStay')

@section('css')
    <style>
        .page-header {
            background: white;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .page-header h1 { font-size: 28px; margin-bottom: 5px; }
        .page-header p { color: #666; font-size: 14px; }
        
        .nav-links { margin-top: 15px; display: flex; gap: 15px; }
        .nav-links a { color: #0f4c81; text-decoration: none; font-size: 14px; }
        .nav-links a:hover { text-decoration: underline; }
        
        .property-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .gallery {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .gallery-main {
            width: 100%;
            height: 500px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 5px;
            padding: 10px;
            background: #fafafa;
        }
        
        .thumbnail { 
            width: 80px; 
            height: 80px; 
            background: #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            overflow: hidden;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .property-details {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .detail-item { }
        .detail-label { font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        .detail-value { font-size: 16px; font-weight: 500; }
        
        .description {
            grid-column: 1 / -1;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .description-title { font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 10px; }
        .description-text { font-size: 14px; line-height: 1.6; color: #555; }
        
        .rental-section {
            background: #f9f9f9;
            padding: 20px;
            border-left: 4px solid #0f4c81;
            grid-column: 1 / -1;
            margin-top: 20px;
        }
        
        .rental-title { font-size: 12px; text-transform: uppercase; color: #0f4c81; font-weight: 600; margin-bottom: 15px; }
        .rental-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .rental-item { font-size: 13px; }
        .rental-item-label { color: #999; margin-bottom: 3px; }
        .rental-item-value { font-weight: 500; }
        
        .actions {
            display: flex;
            gap: 10px;
            grid-column: 1 / -1;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #0f4c81; color: white; }
        .btn-primary:hover { background: #0c3b64; }
        
        .btn-secondary { background: #f0f0f0; color: #333; }
        .btn-secondary:hover { background: #e0e0e0; }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-borrador { background: #fff3cd; color: #856404; }
        .badge-publicada { background: #d4edda; color: #155724; }
        .badge-alquilada { background: #cce5ff; color: #004085; }
        .badge-inactiva { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            .detail-row { grid-template-columns: 1fr; }
            .detail-row.full { grid-column: 1 / -1; }
            .gallery-main { height: 300px; }
        }
    </style>
@endsection

@section('content')
<div class="property-container" style="padding-top: 0;">
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
