@extends('layouts.admin')
@section('titulo', 'Suscripciones — SpotStay')
@section('css')
  <link rel="stylesheet" href="{{ asset('css/admin/suscripciones.css') }}">
@endsection

@section('content')
<style>
    #content {
        background: #F3F4F6;
        padding: 30px !important;
    }
</style>

<div class="hero">
    <h1>Gestión de suscripciones</h1>
    <p>Administra los planes y suscripciones de los arrendadores</p>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <h3>Suscripciones Activas</h3>
        <p class="numero">{{ $totalActivas }}</p>
        <p class="sublabel">{{ $totalActivas > 0 ? round(($totalActivas / 100) * 100) : 0 }}% del total</p>
    </div>
    <div class="kpi-card">
        <h3>Plan Pro</h3>
        <p class="numero">{{ $totalPro }}</p>
        <p class="sublabel">{{ $pctPro }}% de activas</p>
    </div>
    <div class="kpi-card">
        <h3>Plan Básico</h3>
        <p class="numero">{{ $totalBasico }}</p>
        <p class="sublabel">{{ $pctBasico }}% de activas</p>
    </div>
    <div class="kpi-card">
        <h3>Expiradas</h3>
        <p class="numero">{{ $totalExpiradas }}</p>
        <p class="sublabel">Requieren renovación</p>
    </div>
</div>

<!-- TOOLBAR CON FILTROS -->
<div class="toolbar">
    <select id="selectPlanSus">
        <option value="">Todos los planes</option>
        <option value="pro">Plan Pro</option>
        <option value="basico">Plan Básico</option>
        <option value="gratuito">Plan Gratuito</option>
    </select>
    <select id="selectEstadoSus">
        <option value="">Todos los estados</option>
        <option value="activa">Activa</option>
        <option value="expirada">Expirada</option>
        <option value="cancelada">Cancelada</option>
    </select>
    <input type="text" id="buscadorSus" placeholder="Buscar por nombre o email...">
    <button id="btnExportarSus">
        <i class="bi bi-download"></i> Exportar
    </button>
</div>

<!-- CONTENEDOR PRINCIPAL 2 COLUMNAS -->
<div class="container-suscripciones">
    <!-- COLUMNA IZQUIERDA - TABLA -->
    <div class="suscripciones-tabla">
        <!-- HEADER CON CONTADOR Y PAGINACION -->
        <div class="tabla-header-top">
            <span id="contadorSus">{{ $suscripciones->total() }} suscripciones encontradas</span>
            <div class="paginacion">
                <button id="btnAnteriorSus" class="btn-pag">← Anterior</button>
                <span id="paginasSus">
                    @for($i = 1; $i <= min($suscripciones->lastPage(), 3); $i++)
                        <span class="pag-numero {{ $i === 1 ? 'activo' : '' }}" data-pagina="{{ $i }}">{{ $i }}</span>
                    @endfor
                </span>
                <button id="btnSiguienteSus" class="btn-pag">Siguiente →</button>
            </div>
        </div>

        <!-- HEADERS COLUMNAS -->
        <div class="tabla-header">
            <div>ARRENDADOR</div>
            <div>PLAN</div>
            <div>PROPIEDADES</div>
            <div>INICIO</div>
            <div>FIN</div>
            <div>ESTADO</div>
            <div>ACCIONES</div>
        </div>

        <!-- BODY - FILAS -->
        <div class="tabla-body" id="tbodySuscripciones">
            @forelse($suscripciones as $sus)
                @php
                    $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7','#CCE5FF','#FDE8C8'];
                    $color = $colores[$sus->id_usuario_fk % 10];
                    $partes = explode(' ', $sus->nombre_usuario ?? '');
                    $iniciales = strtoupper(substr($partes[0] ?? '', 0, 1)) . strtoupper(substr($partes[1] ?? '', 0, 1));
                    $inactiva = in_array($sus->estado_suscripcion, ['expirada', 'cancelada']) ? 'fila-inactiva' : '';
                    $usadas = $sus->propiedades_usadas ?? 0;
                    $maxProps = match($sus->plan_suscripcion) {
                        'pro' => 10,
                        'basico' => 3,
                        default => 1
                    };
                    $pct = $maxProps > 0 ? min(100, round($usadas / $maxProps * 100)) : 0;
                    $colorBarra = match($sus->plan_suscripcion) {
                        'pro' => '#035498',
                        'basico' => '#D97706',
                        default => '#CBD5E1'
                    };
                @endphp
                <div class="tabla-row {{ $inactiva }}" data-id="{{ $sus->id_suscripcion }}">
                    <div>
                        <div class="usuario-celda">
                            <div class="avatar-tabla" style="background:{{ $color }}">{{ $iniciales }}</div>
                            <div>
                                <p class="usuario-nombre">{{ $sus->nombre_usuario }}</p>
                                <p class="usuario-email">{{ $sus->email_usuario }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge-plan badge-plan-{{ $sus->plan_suscripcion }}">
                            @if($sus->plan_suscripcion === 'pro')
                                <i class="bi bi-star-fill"></i>
                            @elseif($sus->plan_suscripcion === 'basico')
                                <i class="bi bi-layers"></i>
                            @else
                                <i class="bi bi-gift"></i>
                            @endif
                            {{ ucfirst($sus->plan_suscripcion) }}
                        </span>
                    </div>
                    <div>
                        <div class="propiedades-celda">
                            <span class="propiedades-texto">{{ $usadas }} / {{ $maxProps }}</span>
                            <div class="barra-progreso-mini">
                                <div class="barra-relleno-mini" style="width:{{ $pct }}%; background:{{ $colorBarra }}"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="texto-fecha">
                            {{ $sus->inicio_suscripcion ? \Carbon\Carbon::parse($sus->inicio_suscripcion)->format('d M Y') : '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="texto-fecha">
                            {{ $sus->fin_suscripcion ? \Carbon\Carbon::parse($sus->fin_suscripcion)->format('d M Y') : '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="badge-estado badge-sus-{{ $sus->estado_suscripcion }}">{{ ucfirst($sus->estado_suscripcion) }}</span>
                    </div>
                    <div>
                        <div class="acciones-tabla">
                            <button class="btn-accion btn-ver-sus" data-id="{{ $sus->id_suscripcion }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-accion btn-editar-sus" data-id="{{ $sus->id_suscripcion }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="tabla-row sin-resultados">
                    No hay suscripciones registradas
                </div>
            @endforelse
        </div>

        <div class="tabla-footer">
            Mostrando {{ $suscripciones->firstItem() ?? 0 }}-{{ $suscripciones->lastItem() ?? 0 }} de {{ $suscripciones->total() }} suscripciones
        </div>
    </div>

    <!-- COLUMNA DERECHA -->
    <div class="sidebar-suscripciones">
        <!-- PLANES DISPONIBLES -->
        <div class="card-admin card-con-franja">
            <div class="card-franja"></div>
            <div class="card-header-admin">
                <span>Planes disponibles</span>
            </div>

            <div class="planes-lista">
                <!-- Gratuito -->
                <div class="plan-item">
                    <div class="plan-item-top">
                        <div>
                            <span class="badge-plan badge-plan-gratuito">
                                <i class="bi bi-gift"></i>
                                Gratuito
                            </span>
                            <p class="plan-detalle">1 propiedad máximo</p>
                            <p class="plan-detalle">Sin coste mensual</p>
                        </div>
                        <div class="plan-item-stat">
                            <span class="plan-stat-num">{{ $totalGratuito }}</span>
                            <span class="plan-stat-label">usuarios activos</span>
                        </div>
                    </div>
                    <div class="barra-progreso-plan">
                        <div class="barra-relleno-plan" style="width:{{ $pctGratuito }}%; background:#CBD5E1"></div>
                    </div>
                </div>

                <!-- Básico -->
                <div class="plan-item">
                    <div class="plan-item-top">
                        <div>
                            <span class="badge-plan badge-plan-basico">
                                <i class="bi bi-layers"></i>
                                Básico
                            </span>
                            <p class="plan-detalle">3 propiedades máximo</p>
                            <p class="plan-detalle">{{ $precioBasico }} €/mes</p>
                        </div>
                        <div class="plan-item-stat">
                            <span class="plan-stat-num">{{ $totalBasico }}</span>
                            <span class="plan-stat-label">usuarios activos</span>
                        </div>
                    </div>
                    <div class="barra-progreso-plan">
                        <div class="barra-relleno-plan" style="width:{{ $pctBasico }}%; background:#D97706"></div>
                    </div>
                </div>

                <!-- Pro -->
                <div class="plan-item">
                    <div class="plan-item-top">
                        <div>
                            <span class="badge-plan badge-plan-pro">
                                <i class="bi bi-star-fill"></i>
                                Pro
                            </span>
                            <p class="plan-detalle">10 propiedades máximo</p>
                            <p class="plan-detalle">{{ $precioPro }} €/mes</p>
                        </div>
                        <div class="plan-item-stat">
                            <span class="plan-stat-num">{{ $totalPro }}</span>
                            <span class="plan-stat-label">usuarios activos</span>
                        </div>
                    </div>
                    <div class="barra-progreso-plan">
                        <div class="barra-relleno-plan" style="width:{{ $pctPro }}%; background:#035498"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- INGRESOS -->
        <div class="card-admin card-con-franja">
            <div class="card-franja"></div>
            <div class="card-header-admin">
                <span>Ingresos estimados</span>
            </div>

            <div class="ingresos-centro">
                <span class="ingresos-numero">{{ number_format($ingresosMes, 2, ',', '.') }} €</span>
                <span class="ingresos-label">al mes</span>
            </div>

            <div class="ingresos-sep"></div>

            <div class="ingresos-desglose">
                <div class="ingreso-item">
                    <span class="ingreso-concepto">Plan Básico ({{ $totalBasico }} usuarios)</span>
                    <span class="ingreso-importe ingreso-basico">{{ number_format($totalBasico * $precioBasico, 2, ',', '.') }} €/mes</span>
                </div>
                <div class="ingreso-item">
                    <span class="ingreso-concepto">Plan Pro ({{ $totalPro }} usuarios)</span>
                    <span class="ingreso-importe ingreso-pro">{{ number_format($totalPro * $precioPro, 2, ',', '.') }} €/mes</span>
                </div>
                <div class="ingreso-item">
                    <span class="ingreso-concepto">Plan Gratuito ({{ $totalGratuito }} usuarios)</span>
                    <span class="ingreso-importe ingreso-grat">0,00 €/mes</span>
                </div>
            </div>

            <div class="ingresos-sep"></div>

            <div class="ingresos-anual">
                <span class="ingresos-anual-label">PROYECCIÓN ANUAL</span>
                <span class="ingresos-anual-num">{{ number_format($ingresosMes * 12, 2, ',', '.') }} €</span>
            </div>
        </div>

        <!-- PRÓXIMAS A EXPIRAR -->
        <div class="card-admin card-con-franja card-franja-roja">
            <div class="card-franja-roja-el"></div>
            <div class="card-header-admin">
                <span>Próximas a expirar</span>
                <span class="badge-contador" style="background:#EF4444">{{ $proximasExpirar->count() }}</span>
            </div>

            @forelse($proximasExpirar as $prox)
                @php
                    $coloresP = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7','#CCE5FF','#FDE8C8'];
                    $colorP = $coloresP[$prox->id_usuario_fk % 10];
                    $partesP = explode(' ', $prox->nombre_usuario ?? '');
                    $inicialesP = strtoupper(substr($partesP[0] ?? '', 0, 1)) . strtoupper(substr($partesP[1] ?? '', 0, 1));
                    $diasRestantes = $prox->fin_suscripcion ? \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($prox->fin_suscripcion), false) : null;
                    $colorDias = $diasRestantes === null ? 'gris' : ($diasRestantes < 0 ? 'rojo' : ($diasRestantes <= 7 ? 'rojo' : ($diasRestantes <= 15 ? 'naranja' : 'gris')));
                    $textoExpira = $diasRestantes === null ? 'Sin fecha de expiración' : ($diasRestantes < 0 ? 'Expiró hace ' . abs($diasRestantes) . ' días' : 'Expira en ' . $diasRestantes . ' días');
                @endphp
                <div class="expira-item">
                    <div class="avatar-tabla avatar-tabla-sm" style="background:{{ $colorP }}">{{ $inicialesP }}</div>
                    <div class="expira-info">
                        <span class="expira-nombre">{{ $prox->nombre_usuario }}</span>
                        <span class="badge-plan badge-plan-{{ $prox->plan_suscripcion }}">{{ ucfirst($prox->plan_suscripcion) }}</span>
                    </div>
                    <span class="expira-tiempo expira-{{ $colorDias }}">{{ $textoExpira }}</span>
                </div>
            @empty
                <p class="sin-resultados-sm">Sin suscripciones próximas a expirar</p>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal" id="modalSuscripcion">
    <div class="modal-header">
        <h2>Detalle de suscripción</h2>
        <button id="btnCerrarModal" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <div class="modal-content">
        <div class="modal-seccion">
            <h3 class="seccion-titulo">ARRENDADOR</h3>
            <div class="arrendador-info">
                <div class="modal-avatar" id="modalAvatarSus"></div>
                <div class="arrendador-datos">
                    <h3 id="modalNombreSus"></h3>
                    <p id="modalEmailSus"></p>
                    <p id="modalTelefonoSus"></p>
                </div>
            </div>
        </div>

        <div class="modal-seccion">
            <h3 class="seccion-titulo">DETALLES DEL PLAN</h3>
            <div id="modalDetallesGrid"></div>
        </div>

        <div class="modal-seccion">
            <h3 class="seccion-titulo">ACCIONES</h3>
            <div class="modal-acciones">
                <button id="btnCancelarSus" class="btn-desactivar">Cancelar suscripción</button>
                <button id="btnGuardarSus" class="btn-primario">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
  <script src="{{ asset('js/admin/suscripciones.js') }}"></script>
@endsection
