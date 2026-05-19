<nav class="navegacion-horizontal">
    <div class="contenedor-nav">
        <ul class="lista-nav">
            <li><a href="/miembro/inicio" class="enlace-nav {{ request()->is('miembro/inicio') ? 'activo' : '' }}"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a href="{{ route('miembro.arrendador.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.arrendador.formulario') ? 'activo' : '' }}"><i class="bi bi-plus-circle"></i> Conviertete en Arrendador</a></li>
            <li><a href="{{ route('miembro.gestor.formulario') }}" class="enlace-nav {{ request()->routeIs('miembro.gestor.formulario') ? 'activo' : '' }}"><i class="bi bi-plus-circle"></i> Conviertete en Gestor</a></li>
            <li><a href="{{ route('miembro.mensajes.index') }}" class="enlace-nav {{ request()->is('miembro/chat*') ? 'activo' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
            
            @if ($esInquilino)
            <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav {{ request()->is('inquilino/*') ? 'activo' : '' }}"><i class="bi bi-building-gear"></i> Gestionar</a></li>
            @endif
        </ul>
    </div>
</nav>
