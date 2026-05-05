<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades del Arrendador - SpotStay</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/propiedades.css') }}" />
</head>
<body>
<div class="page-shell">
    <header class="page-hero">
        <div>
            <p class="eyebrow">Arrendador</p>
            <h1>Gestiona tus propiedades</h1>
            <p class="hero-copy">Crea, edita y publica inmuebles de tu portafolio.</p>
        </div>
        <div class="hero-lateral">
            <div class="hero-avatar">{{ $avatarInicial }}</div>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">← Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
        </div>
    </header>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="stats-grid">
        <div class="stat-card"><span>{{ $totales['totalPropiedades'] }}</span><small>Total</small></div>
        <div class="stat-card"><span>{{ $totales['publicadas'] }}</span><small>Publicadas</small></div>
        <div class="stat-card"><span>{{ $totales['alquiladas'] }}</span><small>Alquiladas</small></div>
        <div class="stat-card"><span>{{ $totales['inactivas'] }}</span><small>Inactivas</small></div>
    </section>

    <section class="content-grid">
        <div class="panel form-panel">
            <div class="panel-header">
                <h2>{{ $propiedadEditando ? 'Editar propiedad' : 'Nueva propiedad' }}</h2>
                @if ($propiedadEditando)
                    <a class="link-secondary" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId]) }}">Cancelar edición</a>
                @endif
            </div>

            <form method="POST" action="{{ route('arrendador.propiedades.store') }}" class="property-form" data-ajax-form="true" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_propiedad" value="{{ old('id_propiedad', $propiedadEditando->id_propiedad ?? '') }}" />
                <input type="hidden" name="arrendador_id" value="{{ $arrendadorId }}" />
                <input type="hidden" name="imagen-principal-indice" id="imagen-principal-indice" value="-1" />

                <div class="form-grid">
                    <label>
                        <span>Título</span>
                        <input type="text" name="titulo_propiedad" value="{{ old('titulo_propiedad', $propiedadEditando->titulo_propiedad ?? '') }}" required>
                    </label>
                    <label>
                        <span>Estado</span>
                        <select name="estado_propiedad" required>
                            @foreach (['borrador' => 'Borrador', 'publicada' => 'Publicada', 'alquilada' => 'Alquilada', 'inactiva' => 'Inactiva'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(old('estado_propiedad', $propiedadEditando->estado_propiedad ?? 'borrador') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="wide">
                        <span>Dirección</span>
                        <input type="text" name="direccion_propiedad" value="{{ old('direccion_propiedad', $propiedadEditando->direccion_propiedad ?? '') }}" required>
                    </label>
                    <label>
                        <span>Ciudad</span>
                        <input type="text" name="ciudad_propiedad" value="{{ old('ciudad_propiedad', $propiedadEditando->ciudad_propiedad ?? '') }}" required>
                    </label>
                    <label>
                        <span>Código postal</span>
                        <input type="text" name="codigo_postal_propiedad" value="{{ old('codigo_postal_propiedad', $propiedadEditando->codigo_postal_propiedad ?? '') }}" required>
                    </label>
                    <label>
                        <span>Latitud</span>
                        <input type="number" step="0.0000001" name="latitud_propiedad" value="{{ old('latitud_propiedad', $propiedadEditando->latitud_propiedad ?? '') }}">
                    </label>
                    <label>
                        <span>Longitud</span>
                        <input type="number" step="0.0000001" name="longitud_propiedad" value="{{ old('longitud_propiedad', $propiedadEditando->longitud_propiedad ?? '') }}">
                    </label>
                    <label>
                        <span>Precio mensual</span>
                        <input type="number" step="0.01" name="precio_propiedad" value="{{ old('precio_propiedad', $propiedadEditando->precio_propiedad ?? '') }}" required>
                    </label>
                    <label class="wide">
                        <span>Descripción</span>
                        <textarea name="descripcion_propiedad" rows="5">{{ old('descripcion_propiedad', $propiedadEditando->descripcion_propiedad ?? '') }}</textarea>
                    </label>
                    <label class="wide">
                        <span>Imágenes de la propiedad (máximo 10)</span>
                        <input type="file" name="imagenes_propiedad[]" id="imagenes-propiedad" accept="image/jpeg,image/png,image/webp" multiple>
                        <small class="input-help">Puedes subir hasta 10 imágenes (JPG, PNG, WEBP). Solo puedes anclar una como principal.</small>
                    </label>
                    <div id="contenedor-previa-imagenes" style="display: none; margin-top: 15px;">
                        <p class="input-help"><strong>Vista previa y seleccionar principal:</strong></p>
                        <div id="lista-previa-imagenes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 10px;"></div>
                    </div>
                </div>

                <button class="btn-primary" type="submit">{{ $propiedadEditando ? 'Guardar cambios' : 'Crear propiedad' }}</button>
            </form>
        </div>

        <div class="panel list-panel">
            <div class="panel-header">
                <h2>Mis propiedades</h2>
                <a class="link-secondary" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            </div>

            <div class="property-list">
                @forelse ($propiedades as $propiedad)
                    <article class="property-card">
                        <div>
                            <p class="property-title">{{ $propiedad->titulo_propiedad }}</p>
                            <p class="property-meta">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · {{ $propiedad->codigo_postal_propiedad }}</p>
                            <p class="property-meta">{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} €/mes · {{ $propiedad->total_inquilinos ?? 0 }} inquilinos activos</p>
                        </div>
                        <div class="property-actions">
                            <span class="badge badge-{{ $propiedad->estado_propiedad }}">{{ ucfirst($propiedad->estado_propiedad) }}</span>
                            <a class="mini-link" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}">Editar</a>
                            <button class="mini-link" type="button" onclick="abrirModalPropiedad({{ $propiedad->id_propiedad }}, {{ $arrendadorId }})">Ver</button>
                            <form method="POST" action="{{ route('arrendador.propiedades.estado', $propiedad->id_propiedad) }}" data-ajax-state-form="true">
                                @csrf
                                <input type="hidden" name="arrendador_id" value="{{ $arrendadorId }}" />
                                <button class="mini-button" type="submit" data-state-button="true">
                                    {{ $propiedad->estado_propiedad === 'publicada' ? 'Inactivar' : 'Publicar' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Aún no tienes propiedades creadas.</div>
                @endforelse
            </div>

            <div class="pagination-wrap">{{ $propiedades->withQueryString()->links() }}</div>
        </div>
    </section>
</div>

<div id="modal-propiedad" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="cerrarModalPropiedad()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-titulo">Detalles de la propiedad</h2>
            <button class="modal-close" type="button" onclick="cerrarModalPropiedad()">✕</button>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="spinner">Cargando...</div>
        </div>
    </div>
</div>

<style>
.modal {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    cursor: pointer;
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 1001;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.spinner {
    text-align: center;
    color: #999;
    padding: 40px;
}

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

.error {
    color: #d32f2f;
    text-align: center;
    padding: 20px;
}
</style>

<script src="{{ asset('js/arrendador/propiedades.js') }}"></script>
</body>
</html>
