<nav class="navegacion-horizontal">
    <div class="contenedor-nav">
        <ul class="lista-nav">
            {{-- @php($rutaFormularioGestor = \Illuminate\Support\Facades\Route::has('miembro.gestor.formulario') ? route('miembro.gestor.formulario') : null) --}}
            <li><a href="/miembro/inicio" class="enlace-nav {{ request()->is('miembro/inicio') ? 'activo' : '' }}" title="Inicio"><i class="bi bi-house-door"></i> <span class="nav-texto">Inicio</span></a></li>

            @if (!$esArrendador)
            <li><a href="{{ route('miembro.arrendador.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.arrendador.formulario') ? 'activo' : '' }}" title="Conviertete en Arrendador"><i class="bi bi-plus-circle"></i> <span class="nav-texto">Conviertete en Arrendador</span></a></li>
            @endif
            
            @if (!$esGestor)
            <li><a href="{{ route('miembro.gestor.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.gestor.formulario') ? 'activo' : '' }}" title="Conviertete en Gestor"><i class="bi bi-plus-circle"></i> <span class="nav-texto">Conviertete en Gestor</span></a></li>
            @endif

            <li><a href="{{ route('miembro.mensajes.index') }}" class="enlace-nav {{ request()->is('miembro/chat*') ? 'activo' : '' }}" title="Mensajes"><i class="bi bi-chat-dots"></i> <span class="nav-texto">Mensajes</span></a></li>

            @if ($esInquilino)
            <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav {{ request()->is('inquilino/gestionar-propiedades') || request()->is('inquilino/propiedad/*') ? 'activo' : '' }}" title="Gestionar"><i class="bi bi-building-gear"></i> <span class="nav-texto">Gestionar</span></a></li>
            @endif

            @if($tienePagos)
            <li><a href="{{ route('inquilino.historial_pagos') }}" class="enlace-nav {{ request()->routeIs('inquilino.historial_pagos') ? 'activo' : '' }}" title="Mis Gastos"><i class="bi bi-receipt"></i> <span class="nav-texto">Mis Gastos</span></a></li>
            @endif
            <li><a href="{{ route('miembro.asesoria') }}" class="enlace-nav {{ request()->is('*/asesoria*') ? 'activo' : '' }}" title="Asesoría Legal"><i class="bi bi-bank2"></i> <span class="nav-texto">Asesoría Legal</span></a></li>

            @if ($esArrendador)
            <li><a href="{{ route('arrendador.dashboard') }}" class="enlace-nav {{ request()->is('arrendador*') ? 'activo' : '' }}" title="Panel Arrendador"><i class="bi bi-person-workspace"></i> <span class="nav-texto">Panel Arrendador</span></a></li>
            @endif
        </ul>
    </div>

    @if ($esArrendador && request()->is('arrendador*'))
    <div class="sub-nav-arrendador">
        <div class="contenedor-sub-nav">
            <ul class="lista-sub-nav">
                <li><a href="{{ route('arrendador.dashboard') }}" class="enlace-sub-nav {{ request()->routeIs('arrendador.dashboard') ? 'activo' : '' }}" title="Resumen"><i class="bi bi-grid"></i> <span class="nav-texto">Resumen</span></a></li>
                <li><a href="{{ route('arrendador.propiedades') }}" class="enlace-sub-nav {{ request()->is('arrendador/propiedades*') ? 'activo' : '' }}" title="Propiedades"><i class="bi bi-house"></i> <span class="nav-texto">Propiedades</span></a></li>
                <li><a href="{{ route('arrendador.solicitudes') }}" class="enlace-sub-nav {{ request()->is('arrendador/solicitudes*') ? 'activo' : '' }}" title="Solicitudes"><i class="bi bi-inbox"></i> <span class="nav-texto">Solicitudes</span></a></li>
                <li><a href="{{ route('arrendador.inquilinos') }}" class="enlace-sub-nav {{ request()->is('arrendador/inquilinos*') ? 'activo' : '' }}" title="Inquilinos"><i class="bi bi-people"></i> <span class="nav-texto">Inquilinos</span></a></li>
                <li><a href="{{ route('arrendador.contratos') }}" class="enlace-sub-nav {{ request()->is('arrendador/contratos*') ? 'activo' : '' }}" title="Contratos"><i class="bi bi-file-text"></i> <span class="nav-texto">Contratos</span></a></li>
                <li><a href="{{ route('arrendador.incidencias') }}" class="enlace-sub-nav {{ request()->is('arrendador/incidencias*') ? 'activo' : '' }}" title="Incidencias"><i class="bi bi-exclamation-triangle"></i> <span class="nav-texto">Incidencias</span></a></li>
                <li><a href="{{ route('arrendador.precios-gastos') }}" class="enlace-sub-nav {{ request()->is('arrendador/precios-gastos*') ? 'activo' : '' }}" title="Precios/Gastos"><i class="bi bi-cash-coin"></i> <span class="nav-texto">Precios/Gastos</span></a></li>
            </ul>
        </div>
    </div>
    @endif
</nav>