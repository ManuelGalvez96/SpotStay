@extends('layouts.miembro')

@section('title', 'Inicio')

@section('content')
    <section class="seccion-mapa-previsualizacion">
        <div class="cabecera-seccion">
            <h1 class="titulo-principal">Explora propiedades destacadas</h1>
            <p class="descripcion-principal">Visualiza el mapa y ajusta los filtros para encontrar tu proximo hogar.</p>
        </div>
        <a class="mapa-previsualizacion" href="{{ url('/miembro/mapa') }}" aria-label="Ir al mapa completo de propiedades">
            <img class="mapa-previsualizacion-imagen" src="{{ asset('img/mapa-preview.png') }}" alt="Vista previa del mapa de propiedades" />
            <span class="mapa-previsualizacion-overlay">Abrir mapa completo</span>
        </a>
    </section>

    <section class="seccion-listado">
        <div class="contenedor-listado">
            <aside class="panel-filtros-miembro" id="panel-filtros-miembro">
                <form method="GET" action="{{ url('/miembro/inicio') }}">
                    <div class="buscador-filtros">
                        <label class="etiqueta-filtro" for="buscador-propiedades">Buscador</label>
                        <input type="text" id="buscador-propiedades" name="buscador" class="campo-filtro" placeholder="Ciudad o barrio" value="{{ request('buscador') }}" />
                    </div>

                    <div class="filtros-miembro">
                        <h2 class="titulo-filtros">Filtros</h2>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="precio-minimo">Rango de precio</label>
                            <div class="fila-campos">
                                <input type="number" id="precio-minimo" name="precio_minimo" class="campo-filtro" placeholder="Min" min="0" value="{{ request('precio_minimo') }}" />
                                <input type="number" id="precio-maximo" name="precio_maximo" class="campo-filtro" placeholder="Max" min="0" value="{{ request('precio_maximo') }}" />
                            </div>
                        </div>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="tipo-inmueble">Tipo de inmueble</label>
                            <select id="tipo-inmueble" name="tipo_inmueble" class="campo-filtro">
                                <option value="">Todos</option>
                                <option value="piso" {{ request('tipo_inmueble') === 'piso' ? 'selected' : '' }}>Piso</option>
                                <option value="casa" {{ request('tipo_inmueble') === 'casa' ? 'selected' : '' }}>Casa</option>
                                <option value="estudio" {{ request('tipo_inmueble') === 'estudio' ? 'selected' : '' }}>Estudio</option>
                                <option value="atico" {{ request('tipo_inmueble') === 'atico' ? 'selected' : '' }}>Atico</option>
                            </select>
                        </div>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="numero-habitaciones">Número de habitaciones</label>
                            <select id="numero-habitaciones" name="habitaciones" class="campo-filtro">
                                <option value="">Todas</option>
                                <option value="1" {{ request('habitaciones') === '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ request('habitaciones') === '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ request('habitaciones') === '3' ? 'selected' : '' }}>3</option>
                                <option value="4+" {{ request('habitaciones') === '4+' ? 'selected' : '' }}>4+</option>
                            </select>
                        </div>
                        <button class="boton-aplicar" type="submit">Aplicar filtros</button>
                    </div>
                </form>
            </aside>

            <div class="listado-propiedades">
                <div class="cabecera-listado">
                    <h2 class="titulo-listado">Propiedades para ti</h2>
                    <span class="contador-propiedades">
                        {{ $totalPropiedades }} resultados
                    </span>
                </div>

                <div class="grid-propiedades">
                    @forelse ($propiedades as $propiedad)
                    <a class="link-propiedad" href="{{ route('miembro.detalle_propiedad', ['id' => $propiedad->id_propiedad]) }}">
                        <article class="tarjeta-propiedad">
                            <div class="imagen-propiedad">
                                <span class="etiqueta-precio-tarjeta">
                                    {{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} €
                                </span>
                            </div>
                            <div class="contenido-propiedad">
                                <h3 class="titulo-propiedad">{{ $propiedad->titulo_propiedad }}</h3>
                                <p class="ubicacion-propiedad">{{ $propiedad->ciudad_propiedad }} · {{ $propiedad->direccion_propiedad }}</p>
                                <p class="precio-propiedad">{{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} € / mes</p>
                            </div>
                        </article>
                    </a>
                    @empty
                    <div class="estado-vacio">
                        <p>No hay propiedades disponibles en este momento.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection