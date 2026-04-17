@extends('layouts.admin')
@section('titulo', 'Usuarios — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/usuarios.css') }}">
@endsection

@section('content')

<!-- BLOQUE HERO -->
<div class="hero-admin">
    <div class="hero-content">
        <h1>Gestión de usuarios</h1>
        <p>Administra los usuarios registrados en la plataforma</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<!-- BARRA DE HERRAMIENTAS -->
<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorUsuarios" placeholder="Buscar usuario...">
        </div>
        <select id="selectRol" class="select-filtro">
            <option value="">Todos los roles</option>
            <option value="admin">Admin</option>
            <option value="arrendador">Arrendador</option>
            <option value="inquilino">Inquilino</option>
            <option value="gestor">Gestor</option>
            <option value="miembro">Miembro</option>
        </select>
        <select id="selectEstado" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
        </select>
    </div>
    <div class="toolbar-derecha">
        <button id="btnExportar" class="btn-exportar">
            <i class="bi bi-download"></i>
            <span>Exportar</span>
        </button>
        <button id="btnNuevoUsuario" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Nuevo usuario</span>
        </button>
    </div>
</div>

<!-- KPI RÁPIDOS -->
<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-people"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ number_format($totalUsuarios) }}</span>
            <span class="kpi-mini-label">Total usuarios</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ number_format($activos) }}</span>
            <span class="kpi-mini-label">Activos</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ number_format($inactivos) }}</span>
            <span class="kpi-mini-label">Inactivos</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ number_format($esteMes) }}</span>
            <span class="kpi-mini-label">Este mes</span>
        </div>
    </div>
</div>

<!-- TABLA DE USUARIOS -->
<div class="card-admin">
    <div class="tabla-header">
        <span id="contadorResultados">{{ number_format($totalUsuarios) }} usuarios encontrados</span>
        <div class="paginacion">
            <button id="btnAnterior" class="btn-pag">← Anterior</button>
            <span id="paginas">
                {{-- Generar botones de página dinámicamente --}}
                @php
                    $totalPages = $usuarios->lastPage() ?? 1;
                    $paginaActual = $usuarios->currentPage() ?? 1;
                @endphp
                @for($i = 1; $i <= $totalPages; $i++)
                    <button class="pag-numero {{ $paginaActual === $i ? 'activo' : '' }}" data-pagina="{{ $i }}">{{ $i }}</button>
                @endfor
            </span>
            <button id="btnSiguiente" class="btn-pag">Siguiente →</button>
        </div>
    </div>
    
    <table class="tabla-admin" id="tablaUsuarios">
        <thead>
            <tr>
                <th>USUARIO</th>
                <th>ROL</th>
                <th>ESTADO</th>
                <th>PROPIEDADES</th>
                <th>FECHA REGISTRO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyUsuarios">
            @forelse($usuarios as $usuario)
                @php
                    $nombre = $usuario->nombre_usuario;
                    $partes = explode(' ', $nombre);
                    $avatarText = strtoupper(substr($partes[0], 0, 1));
                    if (isset($partes[1])) {
                        $avatarText .= strtoupper(substr($partes[1], 0, 1));
                    }
                    $coloresAvatar = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#E8D5F0', '#FFD5CC', '#CCE5FF', '#D5F5E3', '#FAD7D7', '#D7EAF9', '#FDE8C8'];
                    $colorIndex = crc32($usuario->email_usuario) % count($coloresAvatar);
                    $colorAvatar = $coloresAvatar[$colorIndex];
                    
                    $activo = $usuario->activo_usuario ? '1' : '0';
                    $inactivaClass = $activo === '0' ? 'class="fila-inactiva"' : '';
                    $rolLabel = $usuario->nombre_rol ?? 'Sin rol';
                    $estadoLabel = $usuario->activo_usuario ? 'Activo' : 'Inactivo';
                    $estadoClass = $usuario->activo_usuario ? 'activo' : 'inactivo';
                    $propiedades = $usuario->total_propiedades ?? 0;
                    $propiedadesText = $propiedades > 0 ? $propiedades : '—';
                @endphp
                <tr data-id="{{ $usuario->id_usuario }}" data-activo="{{ $activo }}" {{ $inactivaClass }}>
                    <td>
                        <div class="usuario-celda">
                            <div class="avatar-tabla" style="background: {{ $colorAvatar }};">{{ $avatarText }}</div>
                            <div>
                                <p class="usuario-nombre">{{ $nombre }}</p>
                                <p class="usuario-email">{{ $usuario->email_usuario }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-rol badge-usuario">{{ $rolLabel }}</span></td>
                    <td><span class="badge-estado badge-{{ $estadoClass }}">{{ $estadoLabel }}</span></td>
                    <td>{{ $propiedadesText }}</td>
                    <td>{{ \Carbon\Carbon::parse($usuario->creado_usuario)->format('d M Y') }}</td>
                    <td>
                        <div class="acciones-tabla">
                            <button class="btn-accion btn-ver" data-id="{{ $usuario->id_usuario }}" title="Ver perfil">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-accion btn-editar" data-id="{{ $usuario->id_usuario }}" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <div class="toggle-switch {{ $activo === '1' ? 'activo' : '' }}" data-id="{{ $usuario->id_usuario }}">
                                <div class="toggle-circulo"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999; padding: 20px;">No hay usuarios para mostrar</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="tabla-footer" id="tablaFooter">
        <span>Mostrando {{ $usuarios->firstItem() ?? 0 }}-{{ $usuarios->lastItem() ?? 0 }} de {{ $totalUsuarios }} usuarios</span>
    </div>
</div>

<!-- MODAL PERFIL DE USUARIO -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-admin" id="modalPerfil">
    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Perfil de usuario</span>
            <span class="badge-estado badge-activo" id="modalBadgeEstado">Activo</span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>
    
    <div class="modal-cuerpo">
        <div class="modal-usuario-header">
            <div class="modal-avatar" id="modalAvatar">CG</div>
            <div class="modal-usuario-info">
                <h2 id="modalNombre">Carlos García</h2>
                <p id="modalEmail">carlos.garcia@email.com</p>
                <p id="modalTelefono">+34 612 345 678</p>
                <div class="modal-badges">
                    <span class="badge-rol badge-arrendador" id="modalBadgeRol">Arrendador</span>
                </div>
            </div>
        </div>
        
        <div class="modal-separador"></div>
        
        <div class="modal-grid-datos">
            <div class="dato-item">
                <span class="dato-label">Teléfono</span>
                <span class="dato-valor" id="dataTelefono">+34 612 345 678</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Registro</span>
                <span class="dato-valor" id="dataRegistro">12 ene 2025</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Propiedades</span>
                <span class="dato-valor" id="dataPropiedades">3</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Último acceso</span>
                <span class="dato-valor" id="dataAcceso">hace 2h</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Alquileres</span>
                <span class="dato-valor" id="dataAlquileres">5</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Suscripción</span>
                <span class="dato-valor" id="dataSuscripcion">Premium</span>
            </div>
        </div>
        
        <div class="modal-separador"></div>
        
        <div class="modal-propiedades-lista">
            <span class="dato-label" style="display: block; margin-bottom: 12px;">PROPIEDADES DEL USUARIO</span>
            
            <div class="propiedad-mini-item">
                <div>
                    <p style="font-weight: 600; font-size: 14px; margin: 0;">Calle Mayor 14, Madrid</p>
                </div>
                <span class="badge-estado badge-activo">Alquilada</span>
                <span style="font-weight: 600; color: #111827;">$1.200/mes</span>
            </div>
            
            <div class="propiedad-mini-item">
                <div>
                    <p style="font-weight: 600; font-size: 14px; margin: 0;">Gran Vía 22, Barcelona</p>
                </div>
                <span class="badge-estado badge-activo">Alquilada</span>
                <span style="font-weight: 600; color: #111827;">$1.500/mes</span>
            </div>
            
            <div class="propiedad-mini-item">
                <div>
                    <p style="font-weight: 600; font-size: 14px; margin: 0;">Av. Diagonal 88, BCN</p>
                </div>
                <span class="badge-estado badge-activo">Disponible</span>
                <span style="font-weight: 600; color: #111827;">$1.800/mes</span>
            </div>
        </div>
    </div>
    
    <div class="modal-footer-admin">
        <button id="btnDesactivarUsuario" class="btn-desactivar">
            Desactivar cuenta
        </button>
        <button id="btnEditarUsuario" class="btn-primario">
            Editar usuario
        </button>
    </div>
</div>

<!-- MODAL CREAR/EDITAR USUARIO -->
<div class="modal-overlay" id="modalOverlayFormUsuario"></div>
<div class="modal-admin" id="modalFormUsuario">
    <div class="modal-header-admin">
        <span class="modal-titulo" id="modalFormTitulo">Nuevo usuario</span>
        <button id="btnCerrarFormUsuario" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>
    
    <div class="modal-cuerpo">
        <form id="formUsuario">
            <div class="form-grupo">
                <label for="inputNombre">Nombre completo</label>
                <input type="text" id="inputNombre" name="nombre" placeholder="Ej. Juan García" required>
            </div>
            
            <div class="form-grupo">
                <label for="inputEmail">Correo electrónico</label>
                <input type="email" id="inputEmail" name="email" placeholder="juan@example.com" required>
            </div>
            
            <div class="form-grupo">
                <label for="inputTelefono">Teléfono</label>
                <input type="tel" id="inputTelefono" name="telefono" placeholder="+34 612 345 678">
            </div>
            
            <div class="form-grupo">
                <label for="selectRolForm">Rol</label>
                <select id="selectRolForm" name="rol" required>
                    <option value="">Selecciona un rol</option>
                    <option value="admin">Admin</option>
                    <option value="arrendador">Arrendador</option>
                    <option value="inquilino">Inquilino</option>
                    <option value="gestor">Gestor</option>
                    <option value="miembro">Miembro</option>
                </select>
            </div>
            
            <div class="form-grupo">
                <label for="inputPassword">Contraseña</label>
                <input type="password" id="inputPassword" name="password" placeholder="Contraseña" id="inputPasswordForm">
            </div>
        </form>
    </div>
    
    <div class="modal-footer-admin">
        <button id="btnCancelarFormUsuario" class="btn-secundario">Cancelar</button>
        <button id="btnGuardarUsuario" class="btn-primario">Guardar usuario</button>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/usuarios.js') }}"></script>
@endsection
