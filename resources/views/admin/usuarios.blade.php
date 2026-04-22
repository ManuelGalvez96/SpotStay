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

<!-- MODAL PERFIL DE USUARIO (Bootstrap 5) -->
<div class="modal fade" id="modalPerfil" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3" style="flex: 1;">
                    <h5 class="modal-title mb-0">Perfil de usuario</h5>
                    <span class="badge bg-success" id="modalBadgeEstado">Activo</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Header usuario -->
                <div class="d-flex gap-3 mb-4">
                    <div class="avatar-modal" id="modalAvatar" style="width: 80px; height: 80px; background: #B8CCE4;">CG</div>
                    <div class="flex-grow-1">
                        <h6 id="modalNombre" class="fw-bold mb-1">Carlos García</h6>
                        <p id="modalEmail" class="text-muted mb-2">carlos.garcia@email.com</p>
                        <p id="modalTelefono" class="text-muted mb-2">+34 612 345 678</p>
                        <span class="badge bg-info" id="modalBadgeRol">Arrendador</span>
                    </div>
                </div>
                
                <hr>
                
                <!-- Grid de datos -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Teléfono</small>
                        <p id="dataTelefono" class="fw-500">+34 612 345 678</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Registro</small>
                        <p id="dataRegistro" class="fw-500">12 ene 2025</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Propiedades</small>
                        <p id="dataPropiedades" class="fw-500">3</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Último acceso</small>
                        <p id="dataAcceso" class="fw-500">hace 2h</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Alquileres</small>
                        <p id="dataAlquileres" class="fw-500">5</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Suscripción</small>
                        <p id="dataSuscripcion" class="fw-500">Premium</p>
                    </div>
                </div>
                
                <hr>
                
                <!-- Propiedades -->
                <h6 class="mb-3">Propiedades del Usuario</h6>
                <div class="list-group list-group-flush" id="listaPropiedades">
                    <div class="list-group-item">
                        <p class="text-muted">Cargando propiedades...</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btnDesactivarUsuario">Desactivar cuenta</button>
                <button type="button" class="btn btn-primary" id="btnEditarUsuario">Editar usuario</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR/EDITAR USUARIO (Bootstrap 5) -->
<div class="modal fade" id="modalFormUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTitulo">Nuevo usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form id="formUsuario">
                    <div class="mb-3">
                        <label for="inputNombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="inputNombre" name="nombre" placeholder="Ej. Juan García" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inputEmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="inputEmail" name="email" placeholder="juan@example.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inputTelefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="inputTelefono" name="telefono" placeholder="+34 612 345 678">
                    </div>
                    
                    <div class="mb-3">
                        <label for="selectRolForm" class="form-label">Rol</label>
                        <select class="form-select" id="selectRolForm" name="rol" required>
                            <option value="">Selecciona un rol</option>
                            <option value="admin">Admin</option>
                            <option value="arrendador">Arrendador</option>
                            <option value="inquilino">Inquilino</option>
                            <option value="gestor">Gestor</option>
                            <option value="miembro">Miembro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="inputPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Contraseña">
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarFormUsuario" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuario">Guardar usuario</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/usuarios.js') }}"></script>
@endsection
