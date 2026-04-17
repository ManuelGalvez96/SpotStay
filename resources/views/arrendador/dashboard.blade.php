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
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <div class="search-box">
                        <span>🔍</span>
                        <input type="text" placeholder="Buscar propiedades, inquilinos..." />
                    </div>
                </div>
                <div class="user-menu">
                    <span>🔔</span>
                    <div class="user-avatar">{{ $avatarInicial }}</div>
                </div>
            </div>
            
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Bienvenido, {{ $arrendador->nombre_usuario ?? 'Arrendador' }}</h1>
                <p class="dashboard-subtitle">Gestiona tus propiedades y tenants desde aquí</p>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($propiedadesActivas, 0, ',', '.') }}</div>
                    <div class="stat-label">Propiedades Activas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($inquilinosActivos, 0, ',', '.') }}</div>
                    <div class="stat-label">Inquilinos Activos</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($ingresosEsteMes, 2, ',', '.') }} €</div>
                    <div class="stat-label">Ingresos Este Mes</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($solicitudesPendientes, 0, ',', '.') }}</div>
                    <div class="stat-label">Solicitudes Pendientes</div>
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
            </div>

            <!-- Section: Últimas Aplicaciones -->
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title">Últimas Solicitudes</h2>
                    <div class="section-actions">
                        <a class="btn btn-outline btn-sm" href="{{ route('arrendador.solicitudes', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Ver todas las solicitudes</a>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Solicitante</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ultimasSolicitudes as $solicitud)
                                @php
                                    $estado = strtolower($solicitud->estado_alquiler ?? 'pendiente');
                                    $esActivo = in_array($estado, ['activo', 'aprobado', 'aprobada'], true);
                                    $estadoClase = $esActivo ? 'status-active' : 'status-pending';
                                    $estadoTexto = ucfirst($estado);
                                @endphp
                                <tr>
                                    <td>{{ $solicitud->titulo_propiedad }}</td>
                                    <td>{{ $solicitud->nombre_solicitante }}</td>
                                    <td>
                                        <span class="table-status {{ $estadoClase }}">{{ $estadoTexto }}</span>
                                    </td>
                                    <td>
                                        {{ $solicitud->creado_alquiler ? \Carbon\Carbon::parse($solicitud->creado_alquiler)->format('d/m/Y') : 'Sin fecha' }}
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline" href="{{ route('arrendador.solicitudes', ['arrendador_id' => $arrendador->id_usuario ?? null]) }}">Revisar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No hay solicitudes recientes para este arrendador.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Section: Mensajes Recientes -->
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title">Mensajes Recientes</h2>
                    <div class="section-actions">
                        <button class="btn btn-outline btn-sm">Ver Todos</button>
                    </div>
                </div>
                
                <div class="message-list">
                    @forelse ($mensajesRecientes as $mensaje)
                        <div class="message-item">
                            <div class="message-tenant">{{ $mensaje->nombre_usuario }}</div>
                            <div class="message-text">{{ $mensaje->cuerpo_mensaje }}</div>
                            <div class="message-time">
                                {{ $mensaje->creado_mensaje ? \Carbon\Carbon::parse($mensaje->creado_mensaje)->diffForHumans() : 'Reciente' }}
                            </div>
                        </div>
                    @empty
                        <div class="message-item">
                            <div class="message-tenant">Sin mensajes</div>
                            <div class="message-text">Todavía no hay mensajes recientes con inquilinos.</div>
                            <div class="message-time">-</div>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Section: Propiedades Activas -->
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title">Propiedades Activas</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary btn-sm">+ Nueva Propiedad</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Ubicación</th>
                                <th>Precio/Mes</th>
                                <th>Inquilino Actual</th>
                                <th>Ocupación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($propiedadesActivasDetalle as $propiedad)
                                @php
                                    $ocupada = !empty($propiedad->nombre_inquilino_actual);
                                @endphp
                                <tr>
                                    <td>{{ $propiedad->titulo_propiedad }}</td>
                                    <td>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</td>
                                    <td>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} €</td>
                                    <td>{{ $propiedad->nombre_inquilino_actual ?? 'Disponible' }}</td>
                                    <td>
                                        <span class="table-status {{ $ocupada ? 'status-active' : 'status-pending' }}">
                                            {{ $ocupada ? 'Ocupado' : 'Vacío' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline">Editar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No hay propiedades activas para este arrendador.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</body>
</html>
