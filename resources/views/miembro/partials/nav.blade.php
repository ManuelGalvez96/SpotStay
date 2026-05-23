<nav class="navegacion-horizontal">
    <div class="contenedor-nav">
        <ul class="lista-nav">
            {{-- @php($rutaFormularioGestor = \Illuminate\Support\Facades\Route::has('miembro.gestor.formulario') ? route('miembro.gestor.formulario') : null) --}}
            <li><a href="/miembro/inicio" class="enlace-nav {{ request()->is('miembro/inicio') ? 'activo' : '' }}"><i class="bi bi-house-door"></i> Inicio</a></li>

            @if (!$esArrendador)
            <li><a href="{{ route('miembro.arrendador.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.arrendador.formulario') ? 'activo' : '' }}"><i class="bi bi-plus-circle"></i> Conviertete en Arrendador</a></li>
            @endif
            
            @if (!$esGestor && !$esArrendador && !$esInquilino)
            <li><a href="{{ route('miembro.gestor.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.gestor.formulario') ? 'activo' : '' }}"><i class="bi bi-plus-circle"></i> Conviertete en Gestor</a></li>
            @endif

            <li><a href="{{ route('miembro.mensajes.index') }}" class="enlace-nav {{ request()->is('miembro/chat*') ? 'activo' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes</a></li>

            @if ($esInquilino)
            <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav {{ request()->is('inquilino/gestionar-propiedades') || request()->is('inquilino/propiedad/*') ? 'activo' : '' }}"><i class="bi bi-building-gear"></i> Gestionar</a></li>
            @endif

            @if($tienePagos)
            <li><a href="{{ route('inquilino.historial_pagos') }}" class="enlace-nav {{ request()->routeIs('inquilino.historial_pagos') ? 'activo' : '' }}"><i class="bi bi-receipt"></i> Mis Gastos</a></li>
            @endif

            @if ($esArrendador)
            <li><a href="{{ route('arrendador.dashboard') }}" class="enlace-nav {{ request()->is('arrendador*') ? 'activo' : '' }}"><i class="bi bi-person-workspace"></i> Panel Arrendador</a></li>
            @endif
        </ul>
    </div>

    @if ($esArrendador && request()->is('arrendador*'))
    <div class="sub-nav-arrendador">
        <div class="contenedor-sub-nav">
            <ul class="lista-sub-nav">
                <li><a href="{{ route('arrendador.dashboard') }}" class="enlace-sub-nav {{ request()->routeIs('arrendador.dashboard') ? 'activo' : '' }}"><i class="bi bi-grid"></i> Resumen</a></li>
                <li><a href="{{ route('arrendador.propiedades') }}" class="enlace-sub-nav {{ request()->is('arrendador/propiedades*') ? 'activo' : '' }}"><i class="bi bi-house"></i> Propiedades</a></li>
                <li><a href="{{ route('arrendador.solicitudes') }}" class="enlace-sub-nav {{ request()->is('arrendador/solicitudes*') ? 'activo' : '' }}"><i class="bi bi-inbox"></i> Solicitudes</a></li>
                <li><a href="{{ route('arrendador.inquilinos') }}" class="enlace-sub-nav {{ request()->is('arrendador/inquilinos*') ? 'activo' : '' }}"><i class="bi bi-people"></i> Inquilinos</a></li>
                <li><a href="{{ route('arrendador.contratos') }}" class="enlace-sub-nav {{ request()->is('arrendador/contratos*') ? 'activo' : '' }}"><i class="bi bi-file-text"></i> Contratos</a></li>
                <li><a href="{{ route('arrendador.incidencias') }}" class="enlace-sub-nav {{ request()->is('arrendador/incidencias*') ? 'activo' : '' }}"><i class="bi bi-exclamation-triangle"></i> Incidencias</a></li>
                <li><a href="{{ route('arrendador.gestor') }}" class="enlace-sub-nav {{ request()->is('arrendador/gestor*') ? 'activo' : '' }}"><i class="bi bi-person-gear"></i> Gestores</a></li>
                <li><a href="{{ route('arrendador.precios-gastos') }}" class="enlace-sub-nav {{ request()->is('arrendador/precios-gastos*') ? 'activo' : '' }}"><i class="bi bi-cash-coin"></i> Precios/Gastos</a></li>
            </ul>
        </div>
    </div>
    @endif
</nav>