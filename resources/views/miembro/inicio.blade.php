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
    
    @if ($mostrarAnuncios)
        @include('miembro.partials.anuncio')
    @endif

    <section class="seccion-listado">
        <div class="contenedor-listado">
            <aside class="panel-filtros-miembro" id="panel-filtros-miembro">
                <form method="GET" action="{{ url('/miembro/inicio') }}" id="form-filtros-inicio">
                    <div class="filtros-miembro">
                        <h2 class="titulo-filtros">Filtros</h2>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="ciudad-propiedad">Ciudad</label>
                            <select id="ciudad-propiedad" name="ciudad" class="campo-filtro">
                                <option value="">Todas</option>
                                @foreach ($ciudades as $ciudad)
                                    <option value="{{ $ciudad }}" {{ request('ciudad') === $ciudad ? 'selected' : '' }}>{{ $ciudad }}</option>
                                @endforeach
                            </select>
                        </div>

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
                                <option value="4+" {{ request('habitaciones') === '4+' ? 'selected' : '' }}>4 o más</option>
                            </select>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="banos-propiedad">Baños</label>
                            <select id="banos-propiedad" name="banos" class="campo-filtro">
                                <option value="">Todos</option>
                                <option value="1" {{ request('banos') === '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ request('banos') === '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ request('banos') === '3' ? 'selected' : '' }}>3</option>
                                <option value="4+" {{ request('banos') === '4+' ? 'selected' : '' }}>4 o más</option>
                            </select>
                        </div>

                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro">Metros cuadrados</label>
                            <div class="fila-campos">
                                <input type="number" id="metros-minimo" name="metros_minimo" class="campo-filtro" placeholder="Min" min="0" value="{{ request('metros_minimo') }}" />
                                <input type="number" id="metros-maximo" name="metros_maximo" class="campo-filtro" placeholder="Max" min="0" value="{{ request('metros_maximo') }}" />
                            </div>
                        </div>

                        <div class="grupo-filtro filtros-propiedad-grid">
                            <select id="amueblado-propiedad" name="amueblado" class="campo-filtro">
                                <option value="">Amueblado</option>
                                <option value="1" {{ request('amueblado') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('amueblado') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="terraza-propiedad" name="terraza" class="campo-filtro">
                                <option value="">Terraza</option>
                                <option value="1" {{ request('terraza') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('terraza') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="piscina-propiedad" name="piscina" class="campo-filtro">
                                <option value="">Piscina</option>
                                <option value="1" {{ request('piscina') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('piscina') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="garaje-propiedad" name="garaje" class="campo-filtro">
                                <option value="">Garaje</option>
                                <option value="1" {{ request('garaje') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('garaje') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="ascensor-propiedad" name="ascensor" class="campo-filtro">
                                <option value="">Ascensor</option>
                                <option value="1" {{ request('ascensor') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('ascensor') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="aire-acondicionado-propiedad" name="aire_acondicionado" class="campo-filtro">
                                <option value="">Aire acondicionado</option>
                                <option value="1" {{ request('aire_acondicionado') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('aire_acondicionado') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="calefaccion-propiedad" name="calefaccion" class="campo-filtro">
                                <option value="">Calefacción</option>
                                <option value="1" {{ request('calefaccion') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('calefaccion') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            <select id="trastero-propiedad" name="trastero" class="campo-filtro">
                                <option value="">Trastero</option>
                                <option value="1" {{ request('trastero') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('trastero') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <button class="boton-aplicar" type="button" id="boton-borrar-filtros">Borrar filtros</button>

                        @if ($mostrarAnuncios)
                            @include('miembro.partials.anuncio')
                        @endif
                    </div>
                </form>
            </aside>

            <div class="listado-propiedades">
                <div class="cabecera-listado">
                    <h2 class="titulo-listado">Propiedades para ti</h2>
                    <span class="contador-propiedades" id="contador-propiedades">
                        {{ $totalPropiedades }} resultados
                    </span>
                </div>

                <div class="grid-propiedades" id="grid-propiedades">
                    @forelse ($propiedades as $propiedad)
                        <a class="link-propiedad" href="{{ route('miembro.detalle_propiedad', ['id' => $propiedad->id_propiedad]) }}">
                            <article class="tarjeta-propiedad">
                                <div class="imagen-propiedad">
                                    @if (!empty($propiedad->ruta_foto))
                                        <img src="{{ asset('img/' . $propiedad->ruta_foto) }}" alt="Foto" style="width:100%;height:100%;object-fit:cover;">
                                    @endif
                                    <span class="etiqueta-precio-tarjeta">
                                        {{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} €
                                    </span>
                                </div>
                                <div class="contenido-propiedad">
                                    <h3 class="titulo-propiedad">{{ $propiedad->titulo_propiedad }}</h3>
                                    <p class="ubicacion-propiedad">{{ $propiedad->ciudad_propiedad }} · {{ $propiedad->direccion_propiedad }}</p>
                                    <br>
                                    <div class="detalle-propiedad-card">
                                        <span class="detalle-item-propiedad">
                                            <i class="bi bi-rulers"></i>
                                            {{ $propiedad->metros_cuadrados_propiedad ?? '---' }} m²
                                        </span>
                                        <span class="detalle-item-propiedad">
                                            <i class="bi bi-door-open"></i>
                                            {{ $propiedad->habitaciones_propiedad ?? '---' }} hab.
                                        </span>
                                        <span class="detalle-item-propiedad">
                                            <i class="bi bi-droplet"></i>
                                            {{ $propiedad->banos_propiedad ?? '---' }} baños
                                        </span>
                                    </div>
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