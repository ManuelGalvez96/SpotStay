@extends('layouts.admin')
@php
    $modoEdicion = isset($propiedadEditando) && $propiedadEditando;
@endphp

@section('titulo', $modoEdicion ? 'Editar propiedad — SpotStay' : 'Nueva propiedad — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades-crear.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>{{ $modoEdicion ? 'Editar propiedad' : 'Nueva propiedad' }}</h1>
        <p>{{ $modoEdicion ? 'Actualiza los datos de una propiedad existente' : 'Da de alta una propiedad desde administración' }}</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="crear-wrap">
    <div class="toolbar-admin crear-toolbar">
        <div class="toolbar-izquierda">
            <div class="crear-hint">
                <strong>{{ $modoEdicion ? 'Edición de propiedad' : 'Alta manual de propiedad' }}</strong>
                <span>{{ $modoEdicion ? 'Modifica los datos y guarda los cambios.' : 'Introduce los datos y asígnala a un arrendador existente.' }}</span>
            </div>
        </div>
        <div class="toolbar-derecha">
            <a href="/admin/propiedades" class="btn-exportar">
                <i class="bi bi-arrow-left"></i>
                <span>Volver a propiedades</span>
            </a>
        </div>
    </div>

    <div class="kpi-grid-pequeno crear-kpis">
        <div class="kpi-mini">
            <div class="kpi-mini-icono kpi-mini-azul"><i class="bi bi-house-add"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">Nueva alta</span>
                <span class="kpi-mini-label">Registro admin</span>
            </div>
        </div>
        <div class="kpi-mini">
            <div class="kpi-mini-icono kpi-mini-verde"><i class="bi bi-shield-check"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">Validación</span>
                <span class="kpi-mini-label">Campos obligatorios</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="error-lista">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-admin card-crear">
        <div class="card-header-admin card-header-crear">
            <div class="card-header-title-crear">
                <i class="bi bi-house-add"></i>
                <h2>{{ $modoEdicion ? 'Formulario de edición' : 'Formulario de propiedad' }}</h2>
            </div>
            <span class="card-header-sub-crear">{{ $modoEdicion ? 'Edición' : 'Alta manual' }}</span>
        </div>
        <form action="{{ $modoEdicion ? '/admin/propiedades/' . $propiedadEditando->id_propiedad . '/editar' : '/admin/propiedades/crear' }}" method="POST" class="form-grid" id="formCrearPropiedad">
            @csrf

            <div class="campo-full">
                <label for="titulo">Título</label>
                <input id="titulo" name="titulo" type="text" value="{{ old('titulo', $modoEdicion ? $propiedadEditando->titulo_propiedad : '') }}">
                <small class="error-mensaje" id="errorTituloPropiedad"></small>
            </div>

            <div>
                <label for="calle">Calle</label>
                <input id="calle" name="calle" type="text" value="{{ old('calle', $modoEdicion ? $propiedadEditando->calle_propiedad : '') }}">
                <small class="error-mensaje" id="errorCallePropiedad"></small>
            </div>

            <div>
                <label for="numero">Número</label>
                <input id="numero" name="numero" type="text" value="{{ old('numero', $modoEdicion ? $propiedadEditando->numero_propiedad : '') }}">
                <small class="error-mensaje" id="errorNumeroPropiedad"></small>
            </div>

            <div>
                <label for="piso">Piso</label>
                <input id="piso" name="piso" type="text" value="{{ old('piso', $modoEdicion ? $propiedadEditando->piso_propiedad : '') }}">
            </div>

            <div>
                <label for="puerta">Puerta</label>
                <input id="puerta" name="puerta" type="text" value="{{ old('puerta', $modoEdicion ? $propiedadEditando->puerta_propiedad : '') }}">
            </div>

            <div>
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" name="ciudad" type="text" value="{{ old('ciudad', $modoEdicion ? $propiedadEditando->ciudad_propiedad : '') }}">
                <small class="error-mensaje" id="errorCiudadPropiedad"></small>
            </div>

            <div>
                <label for="codigo_postal">Código postal</label>
                <input id="codigo_postal" name="codigo_postal" type="text" value="{{ old('codigo_postal', $modoEdicion ? $propiedadEditando->codigo_postal_propiedad : '') }}">
                <small class="error-mensaje" id="errorCodigoPostalPropiedad"></small>
            </div>

            <div>
                <label for="precio">Precio mensual</label>
                <input id="precio" name="precio" type="number" min="0" step="0.01" value="{{ old('precio', $modoEdicion ? $propiedadEditando->precio_propiedad : '') }}">
                <small class="error-mensaje" id="errorPrecioPropiedad"></small>
            </div>

            <div>
                <label for="tipo">Tipo de propiedad</label>
                <select id="tipo" name="tipo">
                    <option value="">Seleccionar tipo...</option>
                    <option value="piso" {{ old('tipo', $modoEdicion ? $propiedadEditando->tipo_propiedad : '') === 'piso' ? 'selected' : '' }}>Piso</option>
                    <option value="casa" {{ old('tipo', $modoEdicion ? $propiedadEditando->tipo_propiedad : '') === 'casa' ? 'selected' : '' }}>Casa</option>
                    <option value="estudio" {{ old('tipo', $modoEdicion ? $propiedadEditando->tipo_propiedad : '') === 'estudio' ? 'selected' : '' }}>Estudio</option>
                    <option value="chalet" {{ old('tipo', $modoEdicion ? $propiedadEditando->tipo_propiedad : '') === 'chalet' ? 'selected' : '' }}>Chalet</option>
                </select>
            </div>

            <div>
                <label for="habitaciones">Habitaciones</label>
                <select id="habitaciones" name="habitaciones">
                    <option value="">Seleccionar...</option>
                    <option value="1" {{ old('habitaciones', $modoEdicion ? $propiedadEditando->habitaciones_propiedad : '') === '1' ? 'selected' : '' }}>1</option>
                    <option value="2" {{ old('habitaciones', $modoEdicion ? $propiedadEditando->habitaciones_propiedad : '') === '2' ? 'selected' : '' }}>2</option>
                    <option value="3" {{ old('habitaciones', $modoEdicion ? $propiedadEditando->habitaciones_propiedad : '') === '3' ? 'selected' : '' }}>3</option>
                    <option value="4" {{ old('habitaciones', $modoEdicion ? $propiedadEditando->habitaciones_propiedad : '') === '4' ? 'selected' : '' }}>4</option>
                    <option value="4+" {{ old('habitaciones', $modoEdicion ? $propiedadEditando->habitaciones_propiedad : '') === '4+' ? 'selected' : '' }}>4+</option>
                </select>
            </div>

            <div>
                <label for="metros">Metros cuadrados</label>
                <input id="metros" name="metros" type="number" min="1" value="{{ old('metros', $modoEdicion ? $propiedadEditando->metros_cuadrados_propiedad : '') }}">
            </div>

            <div>
                <label for="banos">Baños</label>
                <select id="banos" name="banos">
                    <option value="">Seleccionar...</option>
                    <option value="1" {{ old('banos', $modoEdicion ? $propiedadEditando->banos_propiedad : '') == '1' ? 'selected' : '' }}>1</option>
                    <option value="2" {{ old('banos', $modoEdicion ? $propiedadEditando->banos_propiedad : '') == '2' ? 'selected' : '' }}>2</option>
                    <option value="3" {{ old('banos', $modoEdicion ? $propiedadEditando->banos_propiedad : '') == '3' ? 'selected' : '' }}>3</option>
                    <option value="3+" {{ old('banos', $modoEdicion ? $propiedadEditando->banos_propiedad : '') === '3+' ? 'selected' : '' }}>3+</option>
                </select>
            </div>

            <div>
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="publicada" {{ old('estado', $modoEdicion ? $propiedadEditando->estado_propiedad : 'publicada') === 'publicada' ? 'selected' : '' }}>Publicada</option>
                    <option value="alquilada" {{ old('estado', $modoEdicion ? $propiedadEditando->estado_propiedad : 'publicada') === 'alquilada' ? 'selected' : '' }}>Alquilada</option>
                    <option value="borrador" {{ old('estado', $modoEdicion ? $propiedadEditando->estado_propiedad : 'publicada') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="inactiva" {{ old('estado', $modoEdicion ? $propiedadEditando->estado_propiedad : 'publicada') === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                </select>
                <small class="error-mensaje" id="errorEstadoPropiedad"></small>
            </div>

            <div class="campo-full">
                <label for="arrendador_email">Email del arrendador</label>
                <input id="arrendador_email" name="arrendador_email" type="email" value="{{ old('arrendador_email', $modoEdicion ? $propiedadEditando->email_arrendador : '') }}">
                <small class="error-mensaje" id="errorEmailArrendadorPropiedad"></small>
            </div>

            <div class="campo-full">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $modoEdicion ? $propiedadEditando->descripcion_propiedad : '') }}</textarea>
            </div>

            <div class="campo-full">
                <fieldset class="extras-fieldset">
                    <legend><strong>Extras de la propiedad</strong></legend>
                    <div class="extras-grid">
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="amueblado" {{ $modoEdicion && $propiedadEditando->amueblado_propiedad ? 'checked' : '' }}>
                            <span>Amueblado</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="piscina" {{ $modoEdicion && $propiedadEditando->piscina_propiedad ? 'checked' : '' }}>
                            <span>Piscina</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="terraza" {{ $modoEdicion && $propiedadEditando->terraza_propiedad ? 'checked' : '' }}>
                            <span>Terraza</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="garaje" {{ $modoEdicion && $propiedadEditando->garaje_propiedad ? 'checked' : '' }}>
                            <span>Garaje</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="ascensor" {{ $modoEdicion && $propiedadEditando->ascensor_propiedad ? 'checked' : '' }}>
                            <span>Ascensor</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="aire_acondicionado" {{ $modoEdicion && $propiedadEditando->aire_acondicionado_propiedad ? 'checked' : '' }}>
                            <span>Aire acondicionado</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="calefaccion" {{ $modoEdicion && $propiedadEditando->calefaccion_propiedad ? 'checked' : '' }}>
                            <span>Calefacción</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="extras[]" value="trastero" {{ $modoEdicion && $propiedadEditando->trastero_propiedad ? 'checked' : '' }}>
                            <span>Trastero</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="campo-full">
                <label for="adicional">Otros extras (especificar)</label>
                <textarea id="adicional" name="adicional" rows="2" placeholder="Ej: Jardín privado, Gimnasio, Entrada independiente...">{{ old('adicional', $modoEdicion ? $propiedadEditando->adicional_propiedad : '') }}</textarea>
            </div>

            <div class="acciones-form campo-full">
                <a href="/admin/propiedades" class="btn-exportar">Cancelar</a>
                <button type="submit" class="btn-primario">
                    <i class="bi bi-check-lg"></i>
                    <span>{{ $modoEdicion ? 'Guardar cambios' : 'Guardar propiedad' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/admin/propiedades-crear.js') }}"></script>
@endsection
