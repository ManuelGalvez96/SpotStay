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
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="https://firebasestorage.googleapis.com/v0/b/banani-prod.appspot.com/o/reference-images%2F33506986-ca37-4e67-98fe-bd56178669bd?alt=media&token=1a48934b-52b6-429c-ad03-7f55dcaf5bf0" alt="SpotStay Logo" />
                <span class="logo-text">SpotStay</span>
            </div>
            
            <nav class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link active">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">🏠</span>
                        Publicar Propiedad
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">📋</span>
                        Aplicaciones
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">💰</span>
                        Precios y Gastos
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">👥</span>
                        Inquilinos
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">💬</span>
                        Mensajes
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">📄</span>
                        Contratos
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="#" class="sidebar-nav-link">
                        <span class="nav-icon">⚙️</span>
                        Gestor Inmobiliario
                    </a>
                </li>
            </nav>
        </aside>
        
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
                    <div class="user-avatar">A</div>
                </div>
            </div>
            
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Bienvenido, Arrendador</h1>
                <p class="dashboard-subtitle">Gestiona tus propiedades y tenants desde aquí</p>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">5</div>
                    <div class="stat-label">Propiedades Activas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">12</div>
                    <div class="stat-label">Inquilinos Activos</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">$8,450</div>
                    <div class="stat-label">Ingresos Este Mes</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">3</div>
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
                        <button class="btn btn-primary btn-sm">Publicar Propiedad</button>
                    </div>
                </div>

                <!-- Card: Gestionar Aplicaciones -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">📋</div>
                        <div>
                            <div class="card-title">Gestionar Aplicaciones</div>
                        </div>
                    </div>
                    <p class="card-description">
                        Revisa y gestiona todas las solicitudes de alquiler de posibles inquilinos.
                    </p>
                    <div class="card-footer">
                        <button class="btn btn-outline btn-sm">Ver Aplicaciones (3)</button>
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
                        <button class="btn btn-outline btn-sm">Configurar Precios</button>
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
                        <button class="btn btn-outline btn-sm">Ver Inquilinos</button>
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
                        <button class="btn btn-outline btn-sm">Abrir Mensajes</button>
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
                        <button class="btn btn-outline btn-sm">Gestionar Contratos</button>
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
                        <button class="btn btn-outline btn-sm">Configurar Gestor</button>
                    </div>
                </div>
            </div>

            <!-- Section: Últimas Aplicaciones -->
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title">Últimas Solicitudes</h2>
                    <div class="section-actions">
                        <button class="btn btn-outline btn-sm">Ver Todas</button>
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
                            <tr>
                                <td>Apartamento Centro - Apt 301</td>
                                <td>Juan González</td>
                                <td>
                                    <span class="table-status status-pending">Pendiente</span>
                                </td>
                                <td>14 Abr 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Revisar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Casa Familia - 3 Hab</td>
                                <td>María López</td>
                                <td>
                                    <span class="table-status status-active">Aprobado</span>
                                </td>
                                <td>12 Abr 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Detalles</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Estudio Moderno - Barrio Norte</td>
                                <td>Carlos Ruiz</td>
                                <td>
                                    <span class="table-status status-pending">Pendiente</span>
                                </td>
                                <td>10 Abr 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Revisar</button>
                                </td>
                            </tr>
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
                    <div class="message-item">
                        <div class="message-tenant">Juan González</div>
                        <div class="message-text">¿Cuál es la política de mascotas para el apartamento?</div>
                        <div class="message-time">Hace 2 horas</div>
                    </div>
                    <div class="message-item">
                        <div class="message-tenant">María López</div>
                        <div class="message-text">Gracias por aprobar mi solicitud. ¿Cuándo puedo recoger las llaves?</div>
                        <div class="message-time">Hace 4 horas</div>
                    </div>
                    <div class="message-item">
                        <div class="message-tenant">Carlos Ruiz</div>
                        <div class="message-text">Me gustaría visitar la propiedad nuevamente antes de decidirme.</div>
                        <div class="message-time">Ayer</div>
                    </div>
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
                            <tr>
                                <td>Apartamento Centro</td>
                                <td>Centro Histórico, Madrid</td>
                                <td>$1,200</td>
                                <td>Juan González</td>
                                <td>
                                    <span class="table-status status-active">Ocupado</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Editar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Casa Familia</td>
                                <td>Barrio Residencial</td>
                                <td>$2,500</td>
                                <td>María López</td>
                                <td>
                                    <span class="table-status status-active">Ocupado</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Editar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Estudio Moderno</td>
                                <td>Barrio Norte</td>
                                <td>$950</td>
                                <td>Disponible</td>
                                <td>
                                    <span class="table-status status-pending">Vacío</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline">Editar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</body>
</html>
