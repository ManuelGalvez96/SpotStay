@extends('layouts.miembro')
 
@section('title', 'Detalle propiedad')
 
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endsection
 
@section('content')
    @if ($propiedad)
        <section class="detalle-cabecera">
            <a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
            <h1 class="detalle-titulo">{{ $propiedad->titulo_propiedad }}</h1>
        </section>

        <section class="detalle-seccion detalle-collage">
            @if (isset($fotosPropiedad) && $fotosPropiedad->count() > 0)
                @php
                    $fotoPrincipal = $fotosPropiedad->first();
                    $fotosSecundarias = $fotosPropiedad->slice(1, 4);
                @endphp

                <div class="collage-grid">
                    <div class="collage-principal">
                        <img src="{{ asset('storage/' . $fotoPrincipal->ruta_foto) }}" alt="Imagen principal de la propiedad" />
                    </div>

                    <div class="collage-secundarias">
                        @foreach ($fotosSecundarias as $foto)
                            <div class="collage-miniatura">
                                <img src="{{ asset('storage/' . $foto->ruta_foto) }}" alt="Imagen de la propiedad" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <span>Esta propiedad aun no tiene imagenes.</span>
            @endif
        </section>

        <section class="detalle-resumen">
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Ubicacion</span>
                <span class="detalle-resumen-valor">{{ $propiedad->ciudad_propiedad ?? 'N/D' }}</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Direccion</span>
                <span class="detalle-resumen-valor">{{ $propiedad->direccion_completa ?? 'N/D' }}</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Precio</span>
                <span class="detalle-resumen-valor">{{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} €</span>
            </div>
        </section>

        <section class="detalle-resumen">
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Tipo</span>
                <span class="detalle-resumen-valor">{{ $propiedad->tipo_propiedad ?? 'N/D' }}</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Habitaciones</span>
                <span class="detalle-resumen-valor">{{ $propiedad->habitaciones_propiedad ?? 'N/D' }}</span>
            </div>
            <div class="detalle-resumen-item">
                <span class="detalle-resumen-etiqueta">Metros cuadrados</span>
                <span class="detalle-resumen-valor">{{ $propiedad->metros_cuadrados_propiedad ?? 'N/D' }}</span>
            </div>
        </section>

        <section class="detalle-info">
            <div class="detalle-descripcion">
                <h2>Descripcion</h2>
                <p>
                    {{ $propiedad->descripcion_propiedad ?? 'Sin descripcion disponible.' }}
                </p>
            </div>
            <div class="detalle-contacto">
                <h2>Detalles de contacto</h2>
                <div class="contacto-lista">
                    <div class="contacto-item">
                        <i class="bi bi-person contacto-icono" aria-hidden="true"></i>
                        <div class="contacto-texto">
                            <span class="contacto-etiqueta">Arrendador</span>
                            <p class="contacto-info">{{ $arrendador->nombre_usuario ?? 'N/D' }}</p>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <i class="bi bi-envelope contacto-icono" aria-hidden="true"></i>
                        <div class="contacto-texto">
                            <span class="contacto-etiqueta">Correo</span>
                            <p class="contacto-info">{{ $arrendador->email_usuario ?? 'N/D' }}</p>
                        </div>
                    </div>

                    <div class="contacto-item">
                        <i class="bi bi-telephone contacto-icono" aria-hidden="true"></i>
                        <div class="contacto-texto">
                            <span class="contacto-etiqueta">Telefono</span>
                            <p class="contacto-info">{{ $arrendador->telefono_usuario ?? 'N/D' }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('miembro.mensajes.iniciar', ['id' => $id]) }}" method="POST">
                    @csrf
                    <button class="boton-contacto" type="submit">Contactar por chat <i class="bi bi-chat-dots"></i></button>
                </form>

                <form action="{{ route('miembro.solicitud_alquiler.store', ['id' => $id]) }}" method="POST">
                    @csrf
                    <button class="boton-aplicar boton-contacto" type="submit">Alquilar <i class="bi bi-house"></i></button>
                </form>
            </div>
        </section>

        <section class="detalle-seccion detalle-mapa">
            <h2 class="detalle-mapa-titulo">Ubicacion de la propiedad</h2>
            @if (!empty($propiedad->latitud_propiedad) && !empty($propiedad->longitud_propiedad))
                <div
                    id="mapa-detalle"
                    data-lat="{{ $propiedad->latitud_propiedad }}"
                    data-lng="{{ $propiedad->longitud_propiedad }}"
                    data-titulo="{{ $propiedad->titulo_propiedad }}"
                    data-direccion="{{ $propiedad->direccion_completa ?? $propiedad->direccion_propiedad ?? '' }}">
                </div>
            @else
                <p class="detalle-mapa-vacio">No hay coordenadas disponibles para esta propiedad.</p>
            @endif
        </section>

        <section class="detalle-seccion detalle-similares">
            <span>Carrousel de propiedades similares</span>
        </section>
    @else
        <div class="estado-vacio">
            <p>No se encontro la propiedad solicitada.</p>
        </div>
    @endif
@endsection
 
@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endsection