<div class="topbar">
    <div class="topbar-izq">
        <img src="{{ asset('img/logo.png') }}" alt="SpotStay Logo" class="topbar-logo-img">
    </div>

    <div class="topbar-central">
        <button class="btn-nav-icon {{ request()->is('arrendador/dashboard') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}" title="Panel arrendador">
            <i class="bi bi-grid"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/propiedades*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId]) }}" title="Propiedades">
            <i class="bi bi-house"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/solicitudes*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.solicitudes', ['arrendador_id' => $arrendadorId]) }}" title="Solicitudes">
            <i class="bi bi-inbox"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/inquilinos*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.inquilinos', ['arrendador_id' => $arrendadorId]) }}" title="Inquilinos">
            <i class="bi bi-people"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/contratos*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.contratos', ['arrendador_id' => $arrendadorId]) }}" title="Contratos">
            <i class="bi bi-file-text"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/incidencias*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.incidencias', ['arrendador_id' => $arrendadorId]) }}" title="Incidencias">
            <i class="bi bi-exclamation-triangle"></i>
        </button>
        <button class="btn-nav-icon {{ request()->is('arrendador/asesoria*') ? 'activo' : '' }}" data-ruta="/arrendador/asesoria" title="Asesoría Legal">
            <i class="bi bi-journal-text"></i>
        </button>
    </div>

    <div class="topbar-der">
        <div class="admin-container" id="adminContainer">
            <div class="avatar-admin">{{ strtoupper(substr(trim($avatarInicial ?? 'A'), 0, 1)) }}</div>
            <span class="admin-nombre">Arrendador</span>
            <i class="bi bi-chevron-down chevron-admin"></i>

            <div class="admin-dropdown" id="adminDropdown">
                <a class="dropdown-item" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Panel</a>
                <button class="dropdown-item dropdown-item-logout" id="btnLogout">Cerrar sesion</button>
            </div>
        </div>
    </div>
</div>
