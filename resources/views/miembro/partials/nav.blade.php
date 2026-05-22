<nav class="navegacion-horizontal">
    <div class="contenedor-nav">
        <ul class="lista-nav">
            <li><a href="/miembro/inicio" class="enlace-nav {{ request()->is('miembro/inicio') ? 'activo' : '' }}"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a href="{{ route('miembro.arrendador.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.arrendador.formulario') ? 'activo' : '' }}"><i class="bi bi-plus-circle"></i> Conviertete en Arrendador</a></li>
            <li><a href="{{ route('miembro.mensajes.index') }}" class="enlace-nav {{ request()->is('miembro/chat*') ? 'activo' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes</a></li>

            @if ($esInquilino)
            <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav {{ request()->is('inquilino/gestionar-propiedades') || request()->is('inquilino/propiedad/*') ? 'activo' : '' }}"><i class="bi bi-building-gear"></i> Gestionar</a></li>
            @endif

            @if($tienePagos)
            <li><a href="{{ route('inquilino.historial_pagos') }}" class="enlace-nav {{ request()->routeIs('inquilino.historial_pagos') ? 'activo' : '' }}"><i class="bi bi-receipt"></i> Mis Gastos</a></li>
            @endif
            <li><a href="{{ route('miembro.asesoria') }}" class="enlace-nav {{ request()->is('*/asesoria*') ? 'activo' : '' }}"><i class="bi bi-journal-text"></i> Asesoría Legal</a></li>
        </ul>
    </div>
</nav>