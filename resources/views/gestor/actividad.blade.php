@extends('layouts.gestor')
@section('titulo', 'Actividad reciente - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
    <style>
        .filtros-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 16px;
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
        <span>Filtrar por tipo</span>
    </div>
    <div class="filtros-pills">
        <a href="{{ route('gestor.actividad') }}" class="filtro-pill @if(!$tipo) activo" style="background:#035498;color:#fff; @endif">
            Todas
            <span class="pill-conteo">{{ $actividades->total() }}</span>
        </a>
        @foreach($tiposInfo as $tipoKey => $info)
            @php $conteo = $conteos[$tipoKey] ?? 0; @endphp
            @if($conteo > 0)
                <a href="{{ route('gestor.actividad', ['tipo' => $tipoKey]) }}"
                   class="filtro-pill @if($tipo === $tipoKey) activo" style="background:{{ $info['color'] }};color:#fff; @endif">
                    <i class="bi bi-{{ $info['icono'] }}"></i>
                    {{ $info['label'] }}
                    <span class="pill-conteo">{{ $conteo }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>

<div class="card-admin card-con-franja" style="margin-top:16px;">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient">
        <span>Timeline de actividad</span>
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
                <p>No hay actividad registrada.</p>
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
