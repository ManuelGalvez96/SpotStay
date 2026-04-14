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
            <span class="kpi-mini-numero">1.284</span>
            <span class="kpi-mini-label">Total usuarios</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">1.156</span>
            <span class="kpi-mini-label">Activos</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">128</span>
            <span class="kpi-mini-label">Inactivos</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">47</span>
            <span class="kpi-mini-label">Este mes</span>
        </div>
    </div>
</div>

<!-- TABLA DE USUARIOS -->
<div class="card-admin">
    <div class="tabla-header">
        <span id="contadorResultados">1.284 usuarios encontrados</span>
        <div class="paginacion">
            <button id="btnAnterior" class="btn-pag">← Anterior</button>
            <span id="paginas">
                <button class="pag-numero activo" data-pagina="1">1</button>
                <button class="pag-numero" data-pagina="2">2</button>
                <button class="pag-numero" data-pagina="3">3</button>
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
            <!-- Fila 1 -->
            <tr data-id="1" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #B8CCE4;">CG</div>
                        <div>
                            <p class="usuario-nombre">Carlos García</p>
                            <p class="usuario-email">carlos.garcia@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-arrendador">Arrendador</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>3</td>
                <td>12 ene 2025</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="1" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="1">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 2 -->
            <tr data-id="2" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #A8D5BF;">LM</div>
                        <div>
                            <p class="usuario-nombre">Laura Martínez</p>
                            <p class="usuario-email">laura.martinez@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-inquilino">Inquilino</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>—</td>
                <td>08 ene 2025</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="2" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="2" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="2">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 3 -->
            <tr data-id="3" data-activo="0" class="fila-inactiva">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #F9E4A0;">SR</div>
                        <div>
                            <p class="usuario-nombre">Sofía Rodríguez</p>
                            <p class="usuario-email">sofia.rodriguez@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-arrendador">Arrendador</span></td>
                <td><span class="badge-estado badge-inactivo">Inactivo</span></td>
                <td>1</td>
                <td>15 dic 2024</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="3" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="3" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch" data-id="3">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 4 -->
            <tr data-id="4" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #E8D5F0;">PM</div>
                        <div>
                            <p class="usuario-nombre">Pedro Molina</p>
                            <p class="usuario-email">pedro.molina@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-gestor">Gestor</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>—</td>
                <td>20 dic 2024</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="4" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="4" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="4">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 5 -->
            <tr data-id="5" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #FFD5CC;">AT</div>
                        <div>
                            <p class="usuario-nombre">Ana Torres</p>
                            <p class="usuario-email">ana.torres@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-miembro">Miembro</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>—</td>
                <td>03 ene 2025</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="5" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="5" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="5">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 6 -->
            <tr data-id="6" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #CCE5FF;">MF</div>
                        <div>
                            <p class="usuario-nombre">Miguel Fernández</p>
                            <p class="usuario-email">miguel.fernandez@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-admin">Admin</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>—</td>
                <td>01 ene 2025</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="6" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="6" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="6">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 7 -->
            <tr data-id="7" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #D5F5E3;">EV</div>
                        <div>
                            <p class="usuario-nombre">Elena Vargas</p>
                            <p class="usuario-email">elena.vargas@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-arrendador">Arrendador</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>2</td>
                <td>18 nov 2024</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="7" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="7" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="7">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 8 -->
            <tr data-id="8" data-activo="0" class="fila-inactiva">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #FAD7D7;">JR</div>
                        <div>
                            <p class="usuario-nombre">Javier Ruiz</p>
                            <p class="usuario-email">javier.ruiz@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-inquilino">Inquilino</span></td>
                <td><span class="badge-estado badge-inactivo">Inactivo</span></td>
                <td>—</td>
                <td>05 dic 2024</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="8" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="8" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch" data-id="8">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 9 -->
            <tr data-id="9" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #D7EAF9;">CL</div>
                        <div>
                            <p class="usuario-nombre">Carmen López</p>
                            <p class="usuario-email">carmen.lopez@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-miembro">Miembro</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>—</td>
                <td>10 ene 2025</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="9" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="9" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="9">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Fila 10 -->
            <tr data-id="10" data-activo="1">
                <td>
                    <div class="usuario-celda">
                        <div class="avatar-tabla" style="background: #FDE8C8;">RM</div>
                        <div>
                            <p class="usuario-nombre">Roberto Mora</p>
                            <p class="usuario-email">roberto.mora@email.com</p>
                        </div>
                    </div>
                </td>
                <td><span class="badge-rol badge-arrendador">Arrendador</span></td>
                <td><span class="badge-estado badge-activo">Activo</span></td>
                <td>5</td>
                <td>22 oct 2024</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="10" title="Ver perfil">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="10" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="toggle-switch activo" data-id="10">
                            <div class="toggle-circulo"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="tabla-footer">
        <span>Mostrando 1-10 de 1.284 usuarios</span>
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

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/usuarios.js') }}"></script>
@endsection
