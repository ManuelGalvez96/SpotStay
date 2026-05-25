@extends('layouts.gestor')
@section('titulo', 'Actividad reciente - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/actividad.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Actividad reciente</h1>
        <p>Todas las novedades de tus propiedades asignadas</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="card-admin card-con-franja">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>Filtros</span>
    </div>

    <form method="GET" action="{{ route('gestor.actividad') }}" class="filtros-avanzados" id="actividadFiltrosForm">
        @if($tipo)
            <input type="hidden" name="tipo" value="{{ $tipo }}">
        @endif
        <div class="filtros-row">
            <div class="filtro-grupo">
                <label for="buscar">Buscar</label>
                <input type="search" name="buscar" id="buscar" placeholder="Texto en título o mensaje..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="filtro-grupo">
                <label for="desde">Desde</label>
                <input type="date" name="desde" id="desde" value="{{ request('desde') }}">
            </div>
            <div class="filtro-grupo">
                <label for="hasta">Hasta</label>
                <input type="date" name="hasta" id="hasta" value="{{ request('hasta') }}">
            </div>
            <div class="filtro-grupo">
                <label for="propiedad">Propiedad</label>
                <select name="propiedad" id="propiedad">
                    <option value="">Todas las propiedades</option>
                    @foreach($propiedades as $p)
                        <option value="{{ $p->id_propiedad }}" @selected($propiedadId == $p->id_propiedad)>
                            {{ $p->titulo_propiedad }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="orden">Orden</label>
                <select name="orden" id="orden">
                    <option value="mas_nuevos" @selected($orden === 'mas_nuevos')>Más nuevos</option>
                    <option value="mas_antiguos" @selected($orden === 'mas_antiguos')>Más antiguos</option>
                </select>
            </div>
            @if($filtrosActivos->isNotEmpty())
                <div class="filtro-grupo">
                    <label>&nbsp;</label>
                    <a href="{{ route('gestor.actividad') }}" class="btn-limpiar">
                        <i class="bi bi-x-lg"></i> Limpiar
                    </a>
                </div>
            @endif
        </div>

        @if($filtrosActivos->isNotEmpty())
            <div class="filtros-activos">
                @foreach($filtrosActivos as $key => $val)
                    @php
                        $label = match ($key) {
                            'tipo' => ($tiposInfo[$val]['label'] ?? $val),
                            'propiedad' => $propiedades->firstWhere('id_propiedad', $val)?->titulo_propiedad ?? "Propiedad #{$val}",
                            'buscar' => "\"{$val}\"",
                            'desde' => "Desde {$val}",
                            'hasta' => "Hasta {$val}",
                            'orden' => $val === 'mas_antiguos' ? 'Más antiguos' : null,
                            default => $val,
                        };
                    @endphp
                    @if($label)
                        <span class="filtro-activo-badge">
                            <i class="bi bi-funnel"></i> {{ $label }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </form>

    <div id="actividadFiltrosWrap">
        @foreach($grupos as $grupoLabel => $grupoTipos)
            @php
                $grupoConteo = collect($grupoTipos)->sum(fn($tk) => $conteos[$tk] ?? 0);
            @endphp
            @if($grupoConteo > 0)
                <div class="grupo-filtros">
                    <div class="grupo-label">{{ $grupoLabel }}</div>
                    <div class="filtros-pills">
                        @foreach($grupoTipos as $tipoKey)
                            @php $conteo = $conteos[$tipoKey] ?? 0; @endphp
                            @if($conteo > 0)
                                <a href="{{ request()->fullUrlWithQuery(['tipo' => $tipoKey, 'page' => null]) }}"
                                   class="filtro-pill @if($tipo === $tipoKey) activo" style="background:{{ $tiposInfo[$tipoKey]['color'] }};color:#fff; @endif">
                                    <i class="bi bi-{{ $tiposInfo[$tipoKey]['icono'] }}"></i>
                                    {{ $tiposInfo[$tipoKey]['label'] }}
                                    <span class="pill-conteo">{{ $conteo }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<div class="card-admin card-con-franja mt-16px">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>Timeline de actividad</span>
        @if($actividades->total() > 0)
            <span class="badge-contador">{{ $actividades->total() }} resultados</span>
        @endif
    </div>

<div id="actividadTimelineWrap">
    <div class="timeline">
        <div class="timeline-linea"></div>
        @forelse($actividades as $notificacion)
            @php
                $icono = $notificacion->icono_notificacion ?? 'circle-fill';
                $color = $notificacion->color_notificacion ?? '#035498';
                $url = $notificacion->url_notificacion ?? '#';
            @endphp
            <a href="{{ $url }}" class="timeline-link">
                <div class="timeline-item">
                    <div class="timeline-punto" style="background:{{ $color }};">
                        <i class="bi bi-{{ $icono }}"></i>
                    </div>
                    <div class="timeline-contenido">
                        <p class="timeline-texto">{{ $notificacion->titulo_notificacion ?? 'Actualización' }}</p>
                        <span class="timeline-desc">{{ $notificacion->mensaje_notificacion ?? '' }}</span>
                        <span class="timeline-hora">{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="timeline-empty">
                <i class="bi bi-inbox"></i>
                <p>No hay actividad registrada con los filtros seleccionados.</p>
            </div>
        @endforelse
    </div>

    @if($actividades->hasPages())
        <div class="timeline-pagination">
            {{ $actividades->links() }}
        </div>
    @endif

    @if($actividades->lastPage() > 1)
        <div class="paginacion-cargar-mas"
             data-current-page="{{ $actividades->currentPage() }}"
             data-last-page="{{ $actividades->lastPage() }}">
            @if($actividades->hasMorePages())
                <button class="btn-cargar-mas" data-next-url="{{ $actividades->nextPageUrl() }}" type="button">
                    Cargar más ({{ $actividades->currentPage() }} / {{ $actividades->lastPage() }})
                </button>
            @else
                <span class="cargar-mas-fin">Toda la actividad cargada</span>
            @endif
        </div>
    @endif
</div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/gestor/actividad-filtros.js') }}"></script>
@endsection
