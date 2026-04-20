@extends('layouts.admin')
@section('titulo', 'Propiedades — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades.css') }}">
@endsection

@section('content')

<!-- BLOQUE HERO -->
<div class="hero-admin">
    <div class="hero-content">
        <h1>Gestión de propiedades</h1>
        <p>Supervisa todas las propiedades publicadas en la plataforma</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

<!-- BARRA DE HERRAMIENTAS -->
<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorPropiedades" placeholder="Buscar por dirección o ciudad...">
        </div>
        <select id="selectEstado" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="publicada">Publicada</option>
            <option value="alquilada">Alquilada</option>
            <option value="borrador">Borrador</option>
            <option value="inactiva">Inactiva</option>
        </select>
        <select id="selectCiudad" class="select-filtro">
            <option value="">Todas las ciudades</option>
            <option value="madrid">Madrid</option>
            <option value="barcelona">Barcelona</option>
            <option value="valencia">Valencia</option>
            <option value="sevilla">Sevilla</option>
            <option value="bilbao">Bilbao</option>
        </select>
        <select id="selectPrecio" class="select-filtro">
            <option value="">Cualquier precio</option>
            <option value="0-500">0 - 500€</option>
            <option value="500-1000">500 - 1.000€</option>
            <option value="1000-2000">1.000 - 2.000€</option>
            <option value="2000+">+2.000€</option>
        </select>
    </div>
    <div class="toolbar-derecha">
        <button id="btnExportar" class="btn-exportar">
            <i class="bi bi-download"></i>
            <span>Exportar</span>
        </button>
        <a href="/admin/propiedades/nueva" id="btnAniadirPropiedad" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Añadir propiedad</span>
        </a>
    </div>
</div>

<!-- KPI RÁPIDOS -->
<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-house"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalPropiedades }}</span>
            <span class="kpi-mini-label">Total propiedades</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $alquiladas }}</span>
            <span class="kpi-mini-label">Alquiladas</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-megaphone"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $publicadas }}</span>
            <span class="kpi-mini-label">Publicadas</span>
        </div>
    </div>
    
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $inactivas }}</span>
            <span class="kpi-mini-label">Inactivas</span>
        </div>
    </div>
</div>

<!-- TABLA DE PROPIEDADES -->
<div class="card-admin">
    <div class="tabla-header">
        <span id="contadorPropiedades">{{ $totalPropiedades }} propiedades encontradas</span>
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
    
    <table class="tabla-admin" id="tablaPropiedades">
        <thead>
            <tr>
                <th>PROPIEDAD</th>
                <th>ARRENDADOR</th>
                <th>ESTADO</th>
                <th>PRECIO</th>
                <th>INQUILINOS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyPropiedades">
            <!-- Fila 1 -->
            <tr data-id="1">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #B8CCE4;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Mayor 14</p>
                            <p class="propiedad-ciudad">Madrid, 28001</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #B8CCE4; width: 28px; height: 28px;">CG</div>
                        <span style="font-size: 13px;">Carlos García</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-alquilada">Alquilada</span></td>
                <td><span class="precio-propiedad">$1.200/mes</span></td>
                <td>2 / 3</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="1" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="1" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 2 -->
            <tr data-id="2">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #A8D5BF;"></div>
                        <div>
                            <p class="propiedad-nombre">Gran Vía 22</p>
                            <p class="propiedad-ciudad">Madrid, 28013</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #A8D5BF; width: 28px; height: 28px;">AT</div>
                        <span style="font-size: 13px;">Ana Torres</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-publicada">Publicada</span></td>
                <td><span class="precio-propiedad">$980/mes</span></td>
                <td>0 / 2</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="2" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="2" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="2" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 3 -->
            <tr data-id="3">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #F9E4A0;"></div>
                        <div>
                            <p class="propiedad-nombre">Av. Diagonal 88</p>
                            <p class="propiedad-ciudad">Barcelona, 08008</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #F9E4A0; width: 28px; height: 28px;">EV</div>
                        <span style="font-size: 13px;">Elena Vargas</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-alquilada">Alquilada</span></td>
                <td><span class="precio-propiedad">$1.500/mes</span></td>
                <td>1 / 1</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="3" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="3" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="3" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 4 -->
            <tr data-id="4">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #FFD5CC;"></div>
                        <div>
                            <p class="propiedad-nombre">Paseo de Gracia 5</p>
                            <p class="propiedad-ciudad">Barcelona, 08007</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #FFD5CC; width: 28px; height: 28px;">RM</div>
                        <span style="font-size: 13px;">Roberto Mora</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-publicada">Publicada</span></td>
                <td><span class="precio-propiedad">$2.200/mes</span></td>
                <td>0 / 4</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="4" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="4" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="4" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 5 -->
            <tr data-id="5">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #D7EAF9;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Serrano 47</p>
                            <p class="propiedad-ciudad">Madrid, 28001</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #D7EAF9; width: 28px; height: 28px;">CG</div>
                        <span style="font-size: 13px;">Carlos García</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-alquilada">Alquilada</span></td>
                <td><span class="precio-propiedad">$1.800/mes</span></td>
                <td>1 / 1</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="5" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="5" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="5" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 6 -->
            <tr data-id="6">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #EDE7F6;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Colón 8</p>
                            <p class="propiedad-ciudad">Valencia, 46004</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #EDE7F6; width: 28px; height: 28px;">IS</div>
                        <span style="font-size: 13px;">Isabel Sanz</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-borrador">Borrador</span></td>
                <td><span class="precio-propiedad">$750/mes</span></td>
                <td>—</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="6" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="6" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="6" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 7 -->
            <tr data-id="7">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #D5F5E3;"></div>
                        <div>
                            <p class="propiedad-nombre">Alameda de Hércules 3</p>
                            <p class="propiedad-ciudad">Sevilla, 41002</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #D5F5E3; width: 28px; height: 28px;">DG</div>
                        <span style="font-size: 13px;">Diego Guerrero</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-publicada">Publicada</span></td>
                <td><span class="precio-propiedad">$650/mes</span></td>
                <td>0 / 2</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="7" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="7" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="7" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 8 -->
            <tr data-id="8" class="fila-inactiva">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #FAD7D7;"></div>
                        <div>
                            <p class="propiedad-nombre">Gran Vía 45</p>
                            <p class="propiedad-ciudad">Bilbao, 48001</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #FAD7D7; width: 28px; height: 28px;">MF</div>
                        <span style="font-size: 13px;">Miguel Fdez.</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-inactiva">Inactiva</span></td>
                <td><span class="precio-propiedad">$900/mes</span></td>
                <td>—</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="8" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="8" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="8" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 9 -->
            <tr data-id="9">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #CCE5FF;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Pelai 12</p>
                            <p class="propiedad-ciudad">Barcelona, 08001</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #CCE5FF; width: 28px; height: 28px;">EV</div>
                        <span style="font-size: 13px;">Elena Vargas</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-alquilada">Alquilada</span></td>
                <td><span class="precio-propiedad">$1.100/mes</span></td>
                <td>2 / 2</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="9" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="9" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="9" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Fila 10 -->
            <tr data-id="10">
                <td>
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #FDE8C8;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Larios 7</p>
                            <p class="propiedad-ciudad">Málaga, 29005</p>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="avatar-tabla" style="background: #FDE8C8; width: 28px; height: 28px;">RM</div>
                        <span style="font-size: 13px;">Roberto Mora</span>
                    </div>
                </td>
                <td><span class="badge-estado badge-publicada">Publicada</span></td>
                <td><span class="precio-propiedad">$820/mes</span></td>
                <td>0 / 3</td>
                <td>
                    <div class="acciones-tabla">
                        <button class="btn-accion btn-ver" data-id="10" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-accion btn-editar" data-id="10" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-accion btn-eliminar" data-id="10" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="tabla-footer">
        <span>Mostrando {{ $propiedades->firstItem() ?? 0 }}-{{ $propiedades->lastItem() ?? 0 }} de {{ $propiedades->total() }} propiedades</span>
    </div>
</div>

<!-- MODAL DETALLE DE PROPIEDAD -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-admin modal-ancho" id="modalPropiedad">
    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Detalle de propiedad</span>
            <span class="badge-estado badge-alquilada" id="modalBadgeEstado">Alquilada</span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>
    
    <!-- IMAGEN PRINCIPAL -->
    <div class="modal-imagen-propiedad" id="modalImagenPropiedad" style="background: linear-gradient(135deg, #8AAAC4, #B8CCE4);">
        <div class="modal-imagen-texto">
            <span id="modalDireccion">Calle Mayor 14, Madrid</span>
        </div>
    </div>
    
    <div class="modal-cuerpo">
        <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
        <span class="seccion-label">INFORMACIÓN GENERAL</span>
        <div class="modal-grid-3">
            <div class="dato-item">
                <span class="dato-label">Precio</span>
                <span class="dato-valor" id="dataPrecio" style="color: #035498; font-weight: 700;">$1.200/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Ciudad</span>
                <span class="dato-valor" id="dataCiudad">Madrid</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">CP</span>
                <span class="dato-valor" id="dataCP">28001</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Dirección</span>
                <span class="dato-valor" id="dataDireccion">Calle Mayor 14</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Habitaciones</span>
                <span class="dato-valor" id="dataHabitaciones">3</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Baños</span>
                <span class="dato-valor" id="dataBanos">1</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Tamaño</span>
                <span class="dato-valor" id="dataTamano">75 m²</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Planta</span>
                <span class="dato-valor" id="dataPlanta">2ª</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Publicada</span>
                <span class="dato-valor" id="dataPublicada">15 ene 2025</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Actualización</span>
                <span class="dato-valor" id="dataActualizacion">10 abr 2025</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Visitas mes</span>
                <span class="dato-valor" id="dataVisitas">47</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Favoritos</span>
                <span class="dato-valor" id="dataFavoritos">12</span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 2: PRECIOS Y GASTOS -->
        <span class="seccion-label">PRECIOS Y GASTOS</span>
        <div class="modal-grid-2">
            <div class="dato-item">
                <span class="dato-label">Alquiler base</span>
                <span class="dato-valor" id="dataAlquiler">$1.200/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Fianza</span>
                <span class="dato-valor" id="dataFianza">$2.400</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Agua</span>
                <span class="dato-valor" id="dataAgua">$30/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Electricidad</span>
                <span class="dato-valor" id="dataElectricidad">$50/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Gas</span>
                <span class="dato-valor" id="dataGas">$25/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Comunidad</span>
                <span class="dato-valor" id="dataComunidad">$40/mes</span>
            </div>
        </div>
        <div class="total-estimado" id="dataTotalEstimado">
            Total estimado: $1.345/mes
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 3: ARRENDADOR Y GESTOR -->
        <span class="seccion-label">ARRENDADOR</span>
        <div class="persona-fila">
            <div class="avatar-tabla" id="avatarArrendador" style="background: #B8CCE4;">CG</div>
            <div>
                <p id="nombreArrendador" style="font-weight: 600; font-size: 14px; margin: 0;">Carlos García</p>
                <p id="emailArrendador" style="font-size: 12px; color: #6B7280; margin: 0;">carlos.garcia@email.com</p>
                <p id="telefonoArrendador" style="font-size: 12px; color: #6B7280; margin: 0;">+34 612 345 678</p>
            </div>
        </div>
        <a id="linkPerfilArrendador" class="link-accion">Ver perfil →</a>

        <span class="seccion-label mt">GESTOR ASIGNADO</span>
        <div class="persona-fila">
            <div class="avatar-tabla" id="avatarGestor" style="background: #B8CCE4;">CG</div>
            <p id="nombreGestor" style="font-weight: 600; font-size: 14px; margin: 0;">Carlos García</p>
            <span class="badge-el-mismo">Él mismo</span>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 4: INQUILINOS -->
        <span class="seccion-label" id="labelInquilinos">INQUILINOS ACTUALES (2/3)</span>
        <div id="listaInquilinos">
            <div class="inquilino-item">
                <div class="avatar-tabla" style="background: #A8D5BF;">LM</div>
                <div>
                    <p style="font-weight: 600; font-size: 13px; margin: 0;">Laura Martínez</p>
                    <p style="font-size: 12px; color: #6B7280; margin: 0;">laura@email.com</p>
                    <p style="font-size: 12px; color: #6B7280; margin: 0;">Desde: enero 2025</p>
                </div>
                <span class="badge-estado badge-activo" style="margin-left: auto;">Activo</span>
            </div>
            <div class="inquilino-item">
                <div class="avatar-tabla" style="background: #D7EAF9;">PM</div>
                <div>
                    <p style="font-weight: 600; font-size: 13px; margin: 0;">Pedro Molina</p>
                    <p style="font-size: 12px; color: #6B7280; margin: 0;">pedro@email.com</p>
                    <p style="font-size: 12px; color: #6B7280; margin: 0;">Desde: febrero 2025</p>
                </div>
                <span class="badge-estado badge-activo" style="margin-left: auto;">Activo</span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 5: CONTRATO E INCIDENCIAS -->
        <span class="seccion-label">CONTRATO ACTIVO</span>
        <div class="contrato-card">
            <div>
                <p style="font-weight: 600; font-size: 13px; margin: 0;">Contrato #2025-0142</p>
                <p style="font-size: 12px; color: #6B7280; margin: 0;">Firmado 15 ene 2025 · Válido hasta 15 ene 2026</p>
            </div>
            <div class="contrato-acciones">
                <span class="badge-estado badge-activo">Firmado</span>
                <button id="btnDescargarPDF" class="btn-link-azul">
                    <i class="bi bi-download"></i>
                    <span>Descargar PDF</span>
                </button>
            </div>
        </div>

        <span class="seccion-label mt">INCIDENCIAS</span>
        <div class="incidencias-lista">
            <div class="incidencia-item">
                <span class="punto-verde"></span>
                <span style="flex: 1;">Fuga en el baño</span>
                <span class="badge-estado badge-activo">Resuelta</span>
                <span class="tiempo-texto">hace 2 meses</span>
            </div>
            <div class="incidencia-item">
                <span class="punto-naranja"></span>
                <span style="flex: 1;">Calefacción no funciona</span>
                <span class="badge-estado badge-pendiente">En proceso</span>
                <span class="tiempo-texto">hace 3 días</span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 6: SERVICIOS -->
        <span class="seccion-label">SERVICIOS INCLUIDOS</span>
        <div class="servicios-tags">
            <span class="tag-servicio">Agua</span>
            <span class="tag-servicio">Electricidad</span>
            <span class="tag-servicio">Gas</span>
            <span class="tag-servicio">Comunidad</span>
            <span class="tag-servicio">Internet</span>
            <span class="tag-servicio">Parking</span>
            <span class="tag-servicio">Trastero</span>
        </div>
    </div>
    
    <div class="modal-footer-admin">
        <button id="btnDesactivarPropiedad" class="btn-desactivar">
            Desactivar propiedad
        </button>
        <div class="modal-footer-derecha">
            <button id="btnVerMapa" class="btn-exportar">
                <i class="bi bi-map"></i>
                <span>Ver en el mapa</span>
            </button>
            <button id="btnEditarPropiedad" class="btn-primario">
                Editar propiedad
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/propiedades.js') }}"></script>
@endsection
