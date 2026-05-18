@extends('layouts.miembro')

@section('title', 'Mis Gastos y Pagos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inquilino/gastos.css') }}?v=2" />
@endsection

@section('content')
<div id="data-session" data-exito="{{ session('success') }}" data-error="{{ session('error') }}" style="display:none;"></div>
<section class="contenido-miembro seccion-gastos-pagos" data-historial-url="{{ route('inquilino.verificar_pagos_pdf') }}">
    <!-- CABECERA PREMIUM -->
    <div class="cabecera-seccion d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="titulo-principal">Mis Gastos y Pagos</h1>
            <p class="descripcion-principal">Gestiona tus mensualidades, suministros e incidencias de forma centralizada.</p>
        </div>
        <div class="filtro-propiedad mt-3 mt-md-0">
            <form action="{{ route('inquilino.historial_pagos') }}" method="GET" id="form-filtro-propiedad">
                <select name="propiedad_id" class="form-select select-premium" id="filtro-propiedad-pagos">
                    <option value="">Todas mis propiedades</option>
                    @foreach($propiedades as $prop)
                        <option value="{{ $prop->id_propiedad }}" {{ $propiedad_seleccionada == $prop->id_propiedad ? 'selected' : '' }}>
                            {{ $prop->titulo_propiedad }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- KPI GRID (Dinamizado) -->
    <div class="kpi-gastos-grid">
        <div class="kpi-pago-card">
            <div class="kpi-pago-icon azul">
                <i class="bi bi-credit-card"></i>
            </div>
            <div class="kpi-pago-info">
                <span class="label">Total a Pagar</span>
                <span class="valor">{{ number_format($total_pendiente, 2, ',', '.') }}€</span>
            </div>
        </div>

        <div class="kpi-pago-card">
            <div class="kpi-pago-icon rojo">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="kpi-pago-info">
                <span class="label">Importe Atrasado</span>
                <span class="valor">{{ number_format($total_atrasado, 2, ',', '.') }}€</span>
            </div>
        </div>

        <div class="kpi-pago-card accion-pagar-todo {{ $propiedad_seleccionada ? '' : 'd-none oculto' }}" id="btn-pagar-todo">
            <div class="kpi-pago-icon accion">
                <i class="bi bi-lightning-fill"></i>
            </div>
            <div class="kpi-pago-info">
                <span class="label">Acción Rápida</span>
                <span class="valor">Pagar Todo Ahora</span>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR DE LISTADOS -->
    <div class="listado-propiedades contenedor-listados-gastos">
        <!-- TABS -->
        <div class="tabs-gastos">
            <button class="tab-btn active" data-tab="pendientes">
                <i class="bi bi-clock-history"></i> Pendientes de Pago
                <span class="badge-count">{{ count($pendientes) }}</span>
            </button>
            <button class="tab-btn" data-tab="historial">
                <i class="bi bi-journal-check"></i> Historial de Pagos
            </button>
        </div>

        <!-- CONTENIDO TAB PENDIENTES -->
        <div class="tab-content active" id="pendientes">
            <div class="lista-gastos-items">
                @forelse($pendientes as $item)
                <div class="gasto-item-row" data-id="{{ $item['id'] }}" data-tipo="{{ $item['tipo'] }}">
                    <div class="item-icon-circle {{ $item['color'] }}">
                        <i class="bi {{ $item['icono'] }}"></i>
                    </div>
                    <div class="item-info">
                        <span class="concepto">{{ $item['concepto'] }}</span>
                        <span class="desc">{{ $item['descripcion'] }}</span>
                    </div>
                    <div class="item-vencimiento">
                        <span class="date">{{ \Carbon\Carbon::parse($item['fecha_vencimiento'])->format('d/m/Y') }}</span>
                        @if($item['estado'] === 'atrasado')
                            <span class="status-text atrasado">Hace {{ \Carbon\Carbon::parse($item['fecha_vencimiento'])->diffInDays() }} días</span>
                        @else
                            <span class="status-text pendiente">Vence pronto</span>
                        @endif
                    </div>
                    <div class="item-status">
                        <span class="badge-estado {{ $item['estado'] }}">{{ ucfirst($item['estado']) }}</span>
                    </div>
                    <div class="item-importe">
                        {{ number_format($item['importe'], 2, ',', '.') }}€
                    </div>
                    <div class="item-accion">
                        <button class="btn-pagar-item" onclick="iniciarPago('{{ $item['tipo'] }}', {{ $item['id'] }})">Pagar</button>
                    </div>
                </div>
                @empty
                <div class="mensaje-vacio">
                    <i class="bi bi-check-circle"></i>
                    <p>No tienes pagos pendientes. ¡Estás al día!</p>
                </div>
                @endforelse
            </div>
            
            <div class="footer-listado">
                <span>Mostrando {{ count($pendientes) }} recibos pendientes</span>
                <a href="#" class="enlace-descargar"><i class="bi bi-file-earmark-zip"></i> Descargar facturas (.zip)</a>
            </div>
        </div>

        <!-- CONTENIDO TAB HISTORIAL -->
        <div class="tab-content" id="historial">
            <div class="lista-gastos-items" id="historial-pagos-lista">
                <div class="mensaje-vacio" id="historial-pagos-cargando">
                    <i class="bi bi-hourglass-split"></i>
                    <p>Cargando historial de pagos...</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/miembro/sweetalert_oso.js') }}"></script>
    <script src="{{ asset('js/inquilino/gastos.js') }}?v=2"></script>
    <link rel="stylesheet" href="{{ asset('css/shared/sweetalert-oso.css') }}" />
@endsection
