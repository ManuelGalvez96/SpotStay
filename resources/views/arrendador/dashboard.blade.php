<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Arrendador - SpotStay</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    />
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/arrendador/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/dashboard-resumen.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/dashboard-charts.css') }}" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Section with Charts -->
            <div class="welcome-section"
                data-total-propiedades="{{ $totalPropiedades }}"
                data-propiedades-alquiladas="{{ $propiedadesPorEstado->get('alquilada')?->cantidad ?? 0 }}"
                data-ingresos-mes="{{ $ingresosEsteMes }}"
                data-estados-json='@json($propiedadesPorEstado->mapWithKeys(function($item, $key) { return [$key => $item->cantidad]; })->toArray())'>
                <!-- Left Chart -->
                <div class="mini-chart chart-left">
                    <div class="chart-container">
                        <canvas id="chartEstados"></canvas>
                    </div>
                    <p class="chart-label">Propiedades por Estado</p>
                </div>

                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="user-menu-header">
                        <div class="user-avatar">{{ $avatarInicial }}</div>
                        <a class="btn btn-outline btn-sm" href="{{ route('logout') }}">Cerrar sesion</a>
                    </div>
                    <h1 class="dashboard-title">Bienvenido, {{ $arrendador->nombre_usuario ?? 'Arrendador' }}</h1>
                    <p class="dashboard-subtitle">Gestiona tus propiedades desde aquí</p>
                </div>

                <!-- Right Chart -->
                <div class="mini-chart chart-right">
                    <div class="chart-container">
                        <canvas id="chartIngresos"></canvas>
                    </div>
                    <p class="chart-label">Ocupación</p>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="dashboard-grid">
                <!-- Card: Publicar Propiedad -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">🏠</div>
                        <div>
                            <div class="card-title">Publicar Propiedad</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Agrega una nueva propiedad a tu portafolio y comienza a recibir solicitudes de inquilinos.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-primary btn-sm" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Publicar Propiedad</a>
                    </div>
                </div>

                <!-- Card: Gestionar Aplicaciones -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">📋</div>
                        <div>
                            <div class="card-title">Gestionar Solicitudes de Alquiler</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Revisa y gestiona las solicitudes de alquiler de posibles inquilinos.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.solicitudes', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Ver Solicitudes ({{ $solicitudesPendientes }})</a>
                    </div>
                </div>

                <!-- Card: Precios y Gastos -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">💰</div>
                        <div>
                            <div class="card-title">Precios y Gastos</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Define el precio del alquiler, gastos adicionales y otras tarifas.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.precios-gastos', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Configurar Precios</a>
                    </div>
                </div>

                <!-- Card: Información Inquilinos -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">👥</div>
                        <div>
                            <div class="card-title">Información Inquilinos</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Consulta datos de contacto, historial y documentos de tus inquilinos.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.inquilinos', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Ver Inquilinos</a>
                    </div>
                </div>

                <!-- Card: Chat con Inquilinos -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">💬</div>
                        <div>
                            <div class="card-title">Chat en Tiempo Real</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Comunícate directamente con tus inquilinos a través de mensajes integrados.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.mensajes', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Abrir Mensajes</a>
                    </div>
                </div>

                <!-- Card: Descargar Contratos -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">📄</div>
                        <div>
                            <div class="card-title">Contratos Digitales</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Genera, firma y descarga contratos de alquiler en formato digital.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.contratos', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Gestionar Contratos</a>
                    </div>
                </div>

                <!-- Card: Gestor Inmobiliario -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">⚙️</div>
                        <div>
                            <div class="card-title">Gestor Inmobiliario</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Define y gestiona tu gestor inmobiliario para administração de tus propiedades.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.gestor', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Configurar Gestor</a>
                    </div>
                </div>
                <!-- Card: Incidencias esperando acción -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">🔧</div>
                        <div>
                            <div class="card-title">Incidencias</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Decide sobre las incidencias que requieren tu atención.
                    </p>
                    <div class="card-footer">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.incidencias', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Gestionar Incidencias</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
    <script src="{{ asset('js/arrendador/dashboard-resumen.js') }}"></script>
    <script src="{{ asset('js/arrendador/dashboard-charts.js') }}"></script>
</body>
</html>
