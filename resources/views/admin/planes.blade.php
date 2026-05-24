@extends('layouts.admin')

@section('titulo', 'Planes — SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/configuracion.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/shared/swal-oso.js') }}"></script>
    <script src="{{ asset('js/admin/planes.js') }}"></script>
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Planes</h1>
        <p>Consulta y edita los planes disponibles en la plataforma</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-card-checklist"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $planes->count() }}</span>
            <span class="kpi-mini-label">Planes totales</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-person-badge"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $planes->where('rol_destino', 'arrendador')->count() }}</span>
            <span class="kpi-mini-label">Planes para arrendador</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $planes->where('activo_plan', true)->count() }}</span>
            <span class="kpi-mini-label">Planes activos</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-currency-euro"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ number_format($planes->avg('precio_plan') ?? 0, 2, ',', '.') }}</span>
            <span class="kpi-mini-label">Precio medio</span>
        </div>
    </div>
</div>

<div class="card-admin">
    <div class="tabla-header">
        <span class="info-paginacion">Edita nombre, slug, rol destino, precio, propiedades y estado de cada plan</span>
    </div>

    <div class="configuracion-admin-body">
        <section class="configuracion-card-seccion planes-seccion" id="crear-plan">
            <div class="configuracion-cabecera-seccion">
                <span class="configuracion-icono icono-verde"><i class="bi bi-plus-circle"></i></span>
                <h2>Crear plan</h2>
            </div>

            <p class="planes-intro">
                Rellena los datos para crear un nuevo plan desde administración.
            </p>

            <form action="{{ route('admin.planes.crear') }}" method="POST" class="plan-form plan-form-creacion">
                @csrf

                <div class="plan-form-grid">
                    <div class="campo-plan">
                        <label for="crear_nombre_plan">Nombre</label>
                        <input type="text" id="crear_nombre_plan" name="nombre_plan" value="{{ old('nombre_plan') }}" maxlength="50" placeholder="Ej. Premium">
                        <small class="error-mensaje" id="errorNombrePlan"></small>
                    </div>

                    <div class="campo-plan">
                        <label for="crear_slug_plan">Slug</label>
                        <input type="text" id="crear_slug_plan" name="slug_plan" value="{{ old('slug_plan') }}" maxlength="30" placeholder="ej. premium">
                        <small class="error-mensaje" id="errorSlugPlan"></small>
                    </div>

                    <div class="campo-plan">
                        <label for="crear_rol_destino">Rol destino</label>
                        <select id="crear_rol_destino" name="rol_destino">
                            <option value="miembro" {{ old('rol_destino') === 'miembro' ? 'selected' : '' }}>Miembro</option>
                            <option value="arrendador" {{ old('rol_destino', 'arrendador') === 'arrendador' ? 'selected' : '' }}>Arrendador</option>
                            <option value="inquilino" {{ old('rol_destino') === 'inquilino' ? 'selected' : '' }}>Inquilino</option>
                            <option value="gestor" {{ old('rol_destino') === 'gestor' ? 'selected' : '' }}>Gestor</option>
                        </select>
                        <small class="error-mensaje" id="errorRolPlan"></small>
                    </div>

                    <div class="campo-plan">
                        <label for="crear_precio_plan">Precio</label>
                        <input type="number" step="0.01" min="0" id="crear_precio_plan" name="precio_plan" value="{{ old('precio_plan') }}" placeholder="0.00">
                        <small class="error-mensaje" id="errorPrecioPlan"></small>
                    </div>

                    <div class="campo-plan">
                        <label for="crear_max_propiedades_plan">Máx. propiedades</label>
                        <input type="number" min="0" max="255" id="crear_max_propiedades_plan" name="max_propiedades_plan" value="{{ old('max_propiedades_plan', 1) }}">
                        <small class="error-mensaje" id="errorMaxPropiedadesPlan"></small>
                    </div>

                    <div class="campo-plan campo-plan-ancho">
                        <label for="crear_descripcion_plan">Descripción</label>
                        <textarea id="crear_descripcion_plan" name="descripcion_plan" rows="3" placeholder="Describe qué incluye el plan">{{ old('descripcion_plan') }}</textarea>
                    </div>

                    <div class="campo-plan campo-plan-check">
                        <label class="checkbox-plan">
                            <input type="checkbox" name="activo_plan" value="1" {{ old('activo_plan', true) ? 'checked' : '' }}>
                            <span>Crear plan activo</span>
                        </label>
                    </div>
                </div>

                <div class="plan-form-footer">
                    <button type="submit" class="btn-guardar-plan">
                        Crear plan
                    </button>
                </div>
            </form>
        </section>

        <section class="configuracion-card-seccion planes-seccion">
            <div class="configuracion-cabecera-seccion">
                <span class="configuracion-icono icono-azul"><i class="bi bi-card-checklist"></i></span>
                <h2>Listado de planes</h2>
            </div>

            <p class="planes-intro">
                Aquí puedes revisar la ficha completa de cada plan y modificar sus propiedades cuando necesites ajustar precios, capacidades o estado.
            </p>

            <div class="planes-grid">
                @foreach($planes as $plan)
                    <article class="plan-card {{ $plan->activo_plan ? 'plan-activo' : 'plan-inactivo' }}">
                        <div class="plan-card-header">
                            <div>
                                <span class="plan-id">Plan #{{ $plan->id_plan }}</span>
                                <h3>{{ $plan->nombre_plan }}</h3>
                                <p class="plan-slug">/{{ $plan->slug_plan }}</p>
                            </div>
                            <span class="plan-badge {{ $plan->activo_plan ? 'plan-badge-activo' : 'plan-badge-inactivo' }}">
                                {{ $plan->activo_plan ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        <div class="plan-meta-grid">
                            <div>
                                <span class="plan-meta-label">Rol destino</span>
                                <strong>{{ ucfirst($plan->rol_destino) }}</strong>
                            </div>
                            <div>
                                <span class="plan-meta-label">Precio</span>
                                <strong>€ {{ number_format($plan->precio_plan, 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="plan-meta-label">Máx. propiedades</span>
                                <strong>{{ $plan->max_propiedades_plan }}</strong>
                            </div>
                            <div>
                                <span class="plan-meta-label">Creado</span>
                                <strong>{{ optional($plan->creado_plan)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div>
                                <span class="plan-meta-label">Actualizado</span>
                                <strong>{{ optional($plan->actualizado_plan)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                        </div>

                        <p class="plan-descripcion">{{ $plan->descripcion_plan ?: 'Sin descripción' }}</p>

                        <form id="form-actualizar-{{ $plan->id_plan }}" action="{{ route('admin.planes.actualizar', $plan->id_plan) }}" method="POST" class="plan-form">
                            @csrf

                            <div class="plan-form-grid">
                                <div class="campo-plan">
                                    <label for="nombre_plan_{{ $plan->id_plan }}">Nombre</label>
                                    <input type="text" id="nombre_plan_{{ $plan->id_plan }}" name="nombre_plan" value="{{ old('nombre_plan', $plan->nombre_plan) }}" maxlength="50">
                                </div>

                                <div class="campo-plan">
                                    <label for="slug_plan_{{ $plan->id_plan }}">Slug</label>
                                    <input type="text" id="slug_plan_{{ $plan->id_plan }}" name="slug_plan" value="{{ old('slug_plan', $plan->slug_plan) }}" maxlength="30">
                                </div>

                                <div class="campo-plan">
                                    <label for="rol_destino_{{ $plan->id_plan }}">Rol destino</label>
                                    <select id="rol_destino_{{ $plan->id_plan }}" name="rol_destino">
                                        <option value="miembro" {{ old('rol_destino', $plan->rol_destino) === 'miembro' ? 'selected' : '' }}>Miembro</option>
                                        <option value="arrendador" {{ old('rol_destino', $plan->rol_destino) === 'arrendador' ? 'selected' : '' }}>Arrendador</option>
                                        <option value="inquilino" {{ old('rol_destino', $plan->rol_destino) === 'inquilino' ? 'selected' : '' }}>Inquilino</option>
                                        <option value="gestor" {{ old('rol_destino', $plan->rol_destino) === 'gestor' ? 'selected' : '' }}>Gestor</option>
                                    </select>
                                </div>

                                <div class="campo-plan">
                                    <label for="precio_plan_{{ $plan->id_plan }}">Precio</label>
                                    <input type="number" step="0.01" min="0" id="precio_plan_{{ $plan->id_plan }}" name="precio_plan" value="{{ old('precio_plan', $plan->precio_plan) }}">
                                </div>

                                <div class="campo-plan">
                                    <label for="max_propiedades_plan_{{ $plan->id_plan }}">Máx. propiedades</label>
                                    <input type="number" min="0" max="255" id="max_propiedades_plan_{{ $plan->id_plan }}" name="max_propiedades_plan" value="{{ old('max_propiedades_plan', $plan->max_propiedades_plan) }}">
                                </div>

                                <div class="campo-plan campo-plan-ancho">
                                    <label for="descripcion_plan_{{ $plan->id_plan }}">Descripción</label>
                                    <textarea id="descripcion_plan_{{ $plan->id_plan }}" name="descripcion_plan" rows="3">{{ old('descripcion_plan', $plan->descripcion_plan) }}</textarea>
                                </div>

                                <div class="campo-plan campo-plan-check">
                                    <label class="checkbox-plan">
                                        <input type="checkbox" name="activo_plan" value="1" {{ old('activo_plan', $plan->activo_plan) ? 'checked' : '' }}>
                                        <span>Plan activo</span>
                                    </label>
                                </div>
                            </div>
                            <div class="plan-form-footer">
                                <div style="display:flex;gap:0.5rem;align-items:center;">
                                    <button type="submit" class="btn-guardar-plan">Guardar cambios</button>
                                    <button type="button" class="btn-guardar-plan btn-eliminar-plan" data-plan-id="{{ $plan->id_plan }}" aria-label="Eliminar plan">Eliminar</button>
                                </div>
                            </div>
                        </form>

                        {{-- Formulario de eliminación oculto (se envía desde el botón anterior) --}}
                        <form id="form-eliminar-{{ $plan->id_plan }}" action="{{ route('admin.planes.eliminar', $plan->id_plan) }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>
<div id="planes-messages"
    style="display:none"
    data-success="{{ session('mensaje_exito_plan') ? e(session('mensaje_exito_plan')) : '' }}"
    data-error="{{ session('mensaje_error_plan') ? e(session('mensaje_error_plan')) : ($errors->any() ? e($errors->first()) : '') }}">
</div>

@endsection