@extends('layouts.admin')
@section('titulo', 'Propiedades — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
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
        {{-- 
        <button id="btnExportar" class="btn-exportar">
            <i class="bi bi-download"></i>
            <span>Exportar</span>
        </button>
        <button id="btnVerMapaGeneral" class="btn-exportar" type="button" disabled>
            <i class="bi bi-map"></i>
            <span>Ver en el mapa</span>
        </button>
        --}}
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
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-pencil-square"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $borradores }}</span>
            <span class="kpi-mini-label">Borradores</span>
        </div>
    </div>
</div>

<!-- TABLA DE PROPIEDADES -->
<div class="card-admin">
    <div class="tabla-header">
        <span id="contadorPropiedades" class="info-paginacion">{{ $totalPropiedades }} propiedades encontradas</span>
        <nav aria-label="Paginación de propiedades">
            <ul class="pagination pagination-sm mb-0" id="paginas">
                {{-- Paginación dinámica (JS) --}}
            </ul>
        </nav>
    </div>
    
    <table class="tabla-admin" id="tablaPropiedades">
        <thead>
            <tr>
                <th>PROPIEDAD</th>
                <th class="col-mobile-hide">ARRENDADOR</th>
                <th>ESTADO</th>
                <th class="col-mobile-hide">PRECIO</th>
                <th class="col-tablet-hide">INQUILINOS</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyPropiedades">
            <!-- Fila 1 -->
            <tr data-id="1">
                <td data-label="PROPIEDAD">
                    <div class="propiedad-celda">
                        <div class="thumb-propiedad" style="background: #B8CCE4;"></div>
                        <div>
                            <p class="propiedad-nombre">Calle Mayor 14</p>
                            <p class="propiedad-ciudad">Madrid, 28001</p>
                        </div>
                    </div>
                </td>
                <td data-label="ARRENDADOR" class="col-mobile-hide">
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #B8CCE4;">CG</div>
                        <span class="font-13">Carlos García</span>
                    </div>
                </td>
                <td data-label="ESTADO"><span class="badge-estado badge-alquilada">Alquilada</span></td>
                <td data-label="PRECIO" class="col-mobile-hide"><span class="precio-propiedad">$1.200/mes</span></td>
                <td data-label="INQUILINOS" class="col-tablet-hide">2 / 3</td>
                <td data-label="ACCIONES">
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #A8D5BF;">AT</div>
                        <span class="font-13">Ana Torres</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #F9E4A0;">EV</div>
                        <span class="font-13">Elena Vargas</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #FFD5CC;">RM</div>
                        <span class="font-13">Roberto Mora</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #D7EAF9;">CG</div>
                        <span class="font-13">Carlos García</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #EDE7F6;">IS</div>
                        <span class="font-13">Isabel Sanz</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #D5F5E3;">DG</div>
                        <span class="font-13">Diego Guerrero</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #FAD7D7;">MF</div>
                        <span class="font-13">Miguel Fdez.</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #CCE5FF;">EV</div>
                        <span class="font-13">Elena Vargas</span>
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
                    <div class="d-flex align-items-center gap-8px">
                        <div class="avatar-tabla avatar-28" style="background: #FDE8C8;">RM</div>
                        <span class="font-13">Roberto Mora</span>
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
<div class="modal fade" id="modalPropiedad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Detalle de propiedad</h5>
                    <span class="badge-estado badge-alquilada" id="modalBadgeEstado">Alquilada</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0">
                <!-- IMAGEN PRINCIPAL -->
                <div class="modal-imagen-propiedad" id="modalImagenPropiedad" style="background: linear-gradient(135deg, #8AAAC4, #B8CCE4);">
                    <div class="modal-imagen-texto">
                        <span id="modalDireccion">Calle Mayor 14, Madrid</span>
                    </div>
                </div>

                <div class="p-4 modal-cuerpo">
        <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
        <span class="seccion-label">INFORMACIÓN GENERAL</span>
        <div class="modal-grid-3">
            <div class="dato-item">
                <span class="dato-label">Precio</span>
                <span class="dato-valor color-primary fw-700" id="dataPrecio">$1.200/mes</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Tipo</span>
                <span class="dato-valor" id="dataTipo">-</span>
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
                <span class="dato-label">Latitud</span>
                <span class="dato-valor" id="dataLatitud">-</span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Longitud</span>
                <span class="dato-valor" id="dataLongitud">-</span>
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
                <span class="dato-label">Puerta</span>
                <span class="dato-valor" id="dataPuerta">-</span>
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

        <!-- SECCIÓN GALERÍA DE IMÁGENES -->
        <div id="seccionGaleriaModal" style="display: none; margin-bottom: 20px;">
            <span class="seccion-label">GALERÍA DE IMÁGENES</span>
            <div id="galeriaModal" style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 10px; margin-top: 10px;">
                <!-- Se rellenará dinámicamente con JavaScript -->
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN EXTRAS -->
        <span class="seccion-label">EXTRAS DE LA PROPIEDAD</span>
        <div class="extras-modal" id="extrasModal">
            <!-- Se rellenará dinámicamente con JavaScript -->
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 3: ARRENDADOR Y GESTOR -->
        <span class="seccion-label">ARRENDADOR</span>
        <div class="persona-fila">
            <div class="avatar-tabla" id="avatarArrendador" style="background: #B8CCE4;">CG</div>
            <div>
                <p id="nombreArrendador" class="fw-600 font-14 m-0">Carlos García</p>
                <p id="emailArrendador" class="font-12 color-gray m-0">carlos.garcia@email.com</p>
                <p id="telefonoArrendador" class="font-12 color-gray m-0">+34 612 345 678</p>
            </div>
        </div>
        <a id="linkPerfilArrendador" class="link-accion">Ver perfil →</a>

        <span class="seccion-label mt">GESTOR ASIGNADO</span>
        <div class="persona-fila">
            <div class="avatar-tabla" id="avatarGestor" style="background: #B8CCE4;">CG</div>
            <p id="nombreGestor" class="fw-600 font-14 m-0">Carlos García</p>
            <span class="badge-el-mismo">Él mismo</span>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 4: INQUILINOS -->
        <span class="seccion-label" id="labelInquilinos">INQUILINOS ACTUALES (2/3)</span>
        <div id="listaInquilinos">
            <div class="inquilino-item">
                <div class="avatar-tabla" style="background: #A8D5BF;">LM</div>
                <div>
                    <p class="fw-600 font-13 m-0">Laura Martínez</p>
                    <p class="font-12 color-gray m-0">laura@email.com</p>
                    <p class="font-12 color-gray m-0">Desde: enero 2025</p>
                </div>
                <span class="badge-estado badge-activo ml-auto">Activo</span>
            </div>
            <div class="inquilino-item">
                <div class="avatar-tabla" style="background: #D7EAF9;">PM</div>
                <div>
                    <p class="fw-600 font-13 m-0">Pedro Molina</p>
                    <p class="font-12 color-gray m-0">pedro@email.com</p>
                    <p class="font-12 color-gray m-0">Desde: febrero 2025</p>
                </div>
                <span class="badge-estado badge-activo ml-auto">Activo</span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- SECCIÓN 5: CONTRATO E INCIDENCIAS -->
        <span class="seccion-label">CONTRATO ACTIVO</span>
        <div class="contrato-card">
            <div>
                <p class="fw-600 font-13 m-0">Contrato #2025-0142</p>
                <p class="font-12 color-gray m-0">Firmado 15 ene 2025 · Válido hasta 15 ene 2026</p>
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
                <span class="flex-1">Fuga en el baño</span>
                <span class="badge-estado badge-activo">Resuelta</span>
                <span class="tiempo-texto">hace 2 meses</span>
            </div>
            <div class="incidencia-item">
                <span class="punto-naranja"></span>
                <span class="flex-1">Calefacción no funciona</span>
                <span class="badge-estado badge-pendiente">En proceso</span>
                <span class="tiempo-texto">hace 3 días</span>
            </div>
        </div>

                </div>
            </div>

            <div class="modal-footer">
                <div class="d-flex align-items-center w-100 justify-content-between">
                    <button id="btnDesactivarPropiedad" class="btn-desactivar">
                        Desactivar propiedad
                    </button>
                    <div class="d-flex gap-2">
                        <button id="btnEditarPropiedad" class="btn btn-primary">
                            Editar propiedad
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/propiedades.js') }}"></script>
@endsection
