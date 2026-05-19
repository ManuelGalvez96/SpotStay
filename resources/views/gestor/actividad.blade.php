@extends('layouts.gestor')
@section('titulo', 'Actividad reciente - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
    <style>
        .filtros-avanzados {
            padding: 16px;
            border-bottom: 1px solid #F3F4F6;
        }
        .filtros-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }
        .filtro-grupo {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }
        .filtro-grupo label {
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filtro-grupo input,
        .filtro-grupo select {
            padding: 6px 10px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 13px;
            background: #fff;
            color: #374151;
            min-width: 140px;
            transition: border-color 0.15s;
        }
        .filtro-grupo input:focus,
        .filtro-grupo select:focus {
            border-color: #035498;
            outline: none;
            box-shadow: 0 0 0 3px rgba(3,84,152,0.1);
        }
        .filtro-grupo input[type="search"] {
            min-width: 200px;
        }
        .btn-filtrar {
            padding: 6px 16px;
            background: #035498;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s;
        }
        .btn-filtrar:hover {
            background: #02437a;
        }
        .btn-limpiar {
            padding: 6px 16px;
            background: transparent;
            color: #6B7280;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-limpiar:hover {
            background: #F3F4F6;
            color: #374151;
        }
        .filtros-activos {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
            align-items: center;
        }
        .filtro-activo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            background: #EFF6FF;
            color: #035498;
            border: 1px solid #BFDBFE;
        }
        .filtro-activo-badge i {
            font-size: 10px;
        }
        .grupo-filtros {
            padding: 8px 16px 12px;
        }
        .grupo-filtros + .grupo-filtros {
            border-top: 1px solid #F3F4F6;
        }
        .grupo-label {
            font-size: 11px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .filtros-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .filtro-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            background: #F3F4F6;
            color: #374151;
            border: 1px solid transparent;
            transition: all 0.15s;
        }
        .filtro-pill:hover {
            background: #E5E7EB;
            color: #111827;
            text-decoration: none;
        }
        .filtro-pill.activo {
            border-color: currentColor;
            color: #fff;
        }
        .filtro-pill .pill-conteo {
            background: rgba(0,0,0,0.12);
            border-radius: 999px;
            padding: 0 8px;
            font-size: 11px;
            font-weight: 600;
        }
        .filtro-pill.activo .pill-conteo {
            background: rgba(255,255,255,0.25);
        }
        .timeline-pagination {
            padding: 16px 24px;
            border-top: 1px solid #F3F4F6;
        }
        .timeline-pagination .pagination {
            margin: 0;
        }
        .timeline-empty {
            padding: 40px 24px;
            text-align: center;
            color: #9CA3AF;
        }
        .timeline-empty i {
            font-size: 40px;
            display: block;
            margin-bottom: 12px;
        }
    </style>
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

    <form method="GET" action="{{ route('gestor.actividad') }}" class="filtros-avanzados">
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
            <div class="filtro-grupo">
                <label>&nbsp;</label>
                <button type="submit" class="btn-filtrar">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
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

    <div class="card-filtros-body">
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

<div class="card-admin card-con-franja" style="margin-top:16px;">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>Timeline de actividad</span>
        @if($actividades->total() > 0)
            <span class="badge-contador">{{ $actividades->total() }} resultados</span>
        @endif
    </div>

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
</div>
@endsection
