@extends('layouts.miembro')

@section('title', 'Gestionar Propiedades')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inquilino/gestionar_propiedades.css') }}" />
@endsection

@section('content')
    <section class="seccion-gestion-inquilino">
        <div class="cabecera-seccion">
            <h1 class="titulo-principal">Gestión de tus Propiedades</h1>
            <p class="descripcion-principal">Desde aquí puedes consultar el estado de tus alquileres activos, pagos e incidencias.</p>
        </div>

        <!-- KPI GRID (Dinamizado) -->
        <div class="kpi-grid-inquilino">
            <div class="kpi-card-inquilino">
                <div class="kpi-icon-inquilino primario">
                    <i class="bi bi-key"></i>
                </div>
                <div class="kpi-datos-inquilino">
                    <span class="kpi-numero">{{ $totalContratos }}</span>
                    <span class="kpi-etiqueta">Contratos Activos</span>
                </div>
            </div>
            <div class="kpi-card-inquilino">
                <div class="kpi-icon-inquilino advertencia">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="kpi-datos-inquilino">
                    <span class="kpi-numero">{{ $totalIncidencias }}</span>
                    <span class="kpi-etiqueta">Incidencias En Proceso</span>
                </div>
            </div>
        </div>

        <!-- LISTADO DE PROPIEDADES (Dinamizado y Filtrable) -->
        <div class="listado-propiedades-gestion">
            <div class="cabecera-listado-gestion">
                <h2 class="titulo-listado">Mis Alquileres Actuales</h2>
            </div>

            <div class="filtros-gestion-container">
                <div class="grupo-filtro">
                    <i class="bi bi-search"></i>
                    <input type="text" id="busqueda-nombre" placeholder="Buscar por nombre..." class="input-filtro">
                </div>
                <div class="custom-select-wrapper" id="custom-select-ciudad">
                    <div class="grupo-filtro select-trigger">
                        <i class="bi bi-geo-alt"></i>
                        <span class="selected-value">Todas las ciudades</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </div>
                    <ul class="select-options-list">
                        <li data-value="" class="option-item selected">Todas las ciudades</li>
                        @foreach($ciudades as $ciudad)
                        <li data-value="{{ $ciudad }}" class="option-item">{{ $ciudad }}</li>
                        @endforeach
                    </ul>
                    <input type="hidden" id="filtro-ciudad-valor" value="">
                </div>
            </div>

            <div class="grid-propiedades-gestion" id="contenedor-grid-propiedades">
                @include('inquilino.partials.grid_propiedades')
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/inquilino/comun.js') }}"></script>
    <script src="{{ asset('js/inquilino/vista_gestionar_propiedades.js') }}"></script>
@endsection