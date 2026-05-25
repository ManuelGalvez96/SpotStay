<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\Miembro\DetallePropiedadController;
use App\Http\Controllers\Miembro\MapaController;
use App\Http\Controllers\Miembro\SolicitudAlquilerController;
use App\Http\Controllers\Miembro\MensajesController;
use App\Http\Controllers\Miembro\SolicitudArrendadorController;
use App\Http\Controllers\Miembro\SolicitudGestorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\CodigoGestorController;
use App\Http\Controllers\Admin\CodigoPropiedadController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\inquilino\InquilinoController;
use App\Http\Controllers\inquilino\InquilinoIncidenciaController;
use App\Http\Controllers\inquilino\InquilinoPagoController;
use App\Http\Controllers\inquilino\InquilinoGastosController;
use App\Http\Controllers\Arrendador\DashboardController as ArrendadorDashboardController;
use App\Http\Controllers\Arrendador\PropiedadController as ArrendadorPropiedadController;
use App\Http\Controllers\Arrendador\SolicitudController as ArrendadorSolicitudController;
use App\Http\Controllers\Arrendador\PrecioGastoController as ArrendadorPrecioGastoController;
use App\Http\Controllers\Arrendador\InquilinoController as ArrendadorInquilinoController;
use App\Http\Controllers\Arrendador\ContratoController as ArrendadorContratoController;
use App\Http\Controllers\Arrendador\GestorController as ArrendadorGestorController;
use App\Http\Controllers\Arrendador\IncidenciaController as ArrendadorIncidenciaController;
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\IncidenciaController as GestorIncidenciaController;
use App\Http\Controllers\Gestor\PropiedadController as GestorPropiedadController;
use App\Http\Controllers\Arrendador\ConfiguracionCobrosController;
use App\Http\Controllers\Gestor\MensajeController as GestorMensajeController;
use App\Http\Controllers\Gestor\PerfilController as GestorPerfilController;
use App\Http\Controllers\Gestor\NotificacionController as GestorNotificacionController;
use App\Http\Controllers\AsesoriaController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Miembro\PerfilController as MiembroPerfilController;

// Rutas Públicas
Route::get('/', function () {
    return view('inicio');
});
Route::get('/logout', [AuthController::class, 'logout']);

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/admin/usuarios/check-email', [AuthController::class, 'checkEmail']);
Route::get('/admin/usuarios/check-telefono', [AuthController::class, 'checkTelefono']);
Route::get('/admin/usuarios/check-dni', [AuthController::class, 'checkDni']);
Route::get('/contratos/{id}/descargar', [
    \App\Http\Controllers\Admin\AlquilerController::class,
    'descargarContrato'
])->name('contratos.descargar');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['role:admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/admin/dashboard/solicitudes-filtrar', [DashboardController::class, 'filtrarSolicitudesNuevas']);
    Route::get('/admin/dashboard/incidencias-filtrar', [DashboardController::class, 'filtrarIncidenciasInactivas']);
    Route::get('/admin/configuracion', [ConfiguracionController::class, 'index']);
    Route::post('/admin/configuracion/notificaciones', [ConfiguracionController::class, 'crearNotificacion'])->name('admin.configuracion.notificaciones.crear');
    Route::get('/admin/planes', [ConfiguracionController::class, 'planes'])->name('admin.planes');
    Route::post('/admin/planes/crear', [ConfiguracionController::class, 'crearPlan'])->name('admin.planes.crear');
    Route::post('/admin/planes/{id}/actualizar', [ConfiguracionController::class, 'actualizarPlan'])->name('admin.planes.actualizar');
    Route::post('/admin/planes/{id}/eliminar', [ConfiguracionController::class, 'eliminarPlan'])->name('admin.planes.eliminar');
    Route::post('/admin/alquiler/{id}/aprobar', [DashboardController::class, 'aprobarAlquiler']);
    Route::post('/admin/alquiler/{id}/rechazar', [DashboardController::class, 'rechazarAlquiler']);

    // Usuarios
    Route::get('/admin/usuarios', [UsuarioController::class, 'index']);
    Route::get('/admin/usuarios/filtrar', [UsuarioController::class, 'filtrar']);
    Route::get('/admin/usuarios/kpis', [UsuarioController::class, 'getKpisUsuarios']);
    Route::get('/admin/usuarios/exportar', [UsuarioController::class, 'exportar']);
    Route::get('/admin/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/admin/usuarios/crear', [UsuarioController::class, 'crear']);
    Route::post('/admin/usuarios/{id}/editar', [UsuarioController::class, 'editar']);
    Route::post('/admin/usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado']);

    // Propiedades
    Route::get('/admin/propiedades', [PropiedadController::class, 'index']);
    Route::get('/admin/propiedades/nueva', [PropiedadController::class, 'nueva']);
    Route::post('/admin/propiedades/crear', [PropiedadController::class, 'crear']);
    Route::get('/admin/propiedades/filtrar', [PropiedadController::class, 'filtrar']);
    Route::get('/admin/propiedades/{id}/editar', [PropiedadController::class, 'editar']);
    Route::post('/admin/propiedades/{id}/editar', [PropiedadController::class, 'actualizar']);
    Route::get('/admin/propiedades/{id}', [PropiedadController::class, 'show']);
    Route::delete('/admin/propiedades/{id}', [PropiedadController::class, 'eliminar']);
    Route::post('/admin/propiedades/{id}/desactivar', [PropiedadController::class, 'desactivar']);
    Route::post('/admin/propiedades/{id}/publicar', [PropiedadController::class, 'publicar']);
    Route::get('/admin/propiedades/{id}/descargar-pdf', [PropiedadController::class, 'descargarPdf']);
    Route::get('/admin/alquileres/{id}/descargar-contrato', [\App\Http\Controllers\Admin\AlquilerController::class, 'descargarContrato'])->name('admin.alquileres.descargar-contrato');
    Route::get('/admin/alquileres/{id}/contrato-debug', [\App\Http\Controllers\Admin\AlquilerController::class, 'contratoDebug'])->name('admin.alquileres.contrato-debug');
    Route::get('/admin/propiedades/exportar', [PropiedadController::class, 'exportar']);

    // Solicitudes
    Route::get('/admin/solicitudes', [SolicitudController::class, 'index']);
    Route::get('/admin/solicitudes/filtrar', [SolicitudController::class, 'filtrar']);
    Route::get('/admin/solicitudes/kpis', [SolicitudController::class, 'getKpisSolicitudes']);
    Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show']);
    Route::post('/admin/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::post('/admin/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar']);

    // Incidencias
    Route::get('/admin/incidencias', [IncidenciaController::class, 'index']);
    Route::get('/admin/incidencias/filtrar', [IncidenciaController::class, 'filtrar']);
    Route::get('/admin/incidencias/kpis', [IncidenciaController::class, 'getKpisIncidencias']);
    Route::get('/admin/incidencias/{id}', [IncidenciaController::class, 'show']);
    Route::post('/admin/incidencias/{id}/estado', [IncidenciaController::class, 'cambiarEstado']);
    Route::post('/admin/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);
    Route::post('/admin/incidencias/{id}/contactar', [IncidenciaController::class, 'contactar']);

    // Categorías
    Route::get('/admin/categorias', [CategoriaController::class, 'index']);
    Route::get('/admin/categorias/obtener', [CategoriaController::class, 'obtenerCategorias']);
    Route::post('/admin/categorias/crear', [CategoriaController::class, 'crear']);
    Route::put('/admin/categorias/{id}', [CategoriaController::class, 'editar']);
    Route::delete('/admin/categorias/{id}', [CategoriaController::class, 'eliminar']);

    // Asesoría Legal
    Route::get('/admin/asesoria', [\App\Http\Controllers\Admin\AsesoriaController::class, 'index'])->name('admin.asesoria');
    Route::get('/admin/asesoria/filtrar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'filtrar'])->name('admin.asesoria.filtrar');
    Route::post('/admin/asesoria/categoria/crear', [\App\Http\Controllers\Admin\AsesoriaController::class, 'store'])->name('admin.asesoria.categoria.crear');
    Route::post('/admin/asesoria/categoria/{id}/toggle-estado', [\App\Http\Controllers\Admin\AsesoriaController::class, 'toggleEstado'])->name('admin.asesoria.categoria.toggle');
    Route::get('/admin/asesoria/categoria/{id}/editar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'edit'])->name('admin.asesoria.categoria.edit');
    Route::post('/admin/asesoria/categoria/{id}/actualizar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'update'])->name('admin.asesoria.categoria.update');
    Route::delete('/admin/asesoria/categoria/{id}/eliminar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'destroy'])->name('admin.asesoria.categoria.destroy');

    // Asesoría Legal — Artículos
    Route::get('/admin/asesoria/articulos', [\App\Http\Controllers\Admin\AsesoriaController::class, 'articulos'])->name('admin.asesoria.articulos');
    Route::get('/admin/asesoria/articulos/filtrar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'filtrarArticulos'])->name('admin.asesoria.articulos.filtrar');
    Route::post('/admin/asesoria/articulos/crear', [\App\Http\Controllers\Admin\AsesoriaController::class, 'storeArticulo'])->name('admin.asesoria.articulos.crear');
    Route::post('/admin/asesoria/articulos/{articulo}/toggle-estado', [\App\Http\Controllers\Admin\AsesoriaController::class, 'toggleEstadoArticulo'])->name('admin.asesoria.articulos.toggle-estado');
    Route::post('/admin/asesoria/articulos/{articulo}/toggle-destacado', [\App\Http\Controllers\Admin\AsesoriaController::class, 'toggleDestacadoArticulo'])->name('admin.asesoria.articulos.toggle-destacado');
    Route::get('/admin/asesoria/articulos/max-orden-faq', [\App\Http\Controllers\Admin\AsesoriaController::class, 'maxOrdenFaq'])->name('admin.asesoria.articulos.max-orden-faq');
    Route::get('/admin/asesoria/articulos/max-orden/{categoria}', [\App\Http\Controllers\Admin\AsesoriaController::class, 'maxOrdenArticulo'])->name('admin.asesoria.articulos.max-orden');
    Route::get('/admin/asesoria/articulos/{articulo}/editar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'editArticulo'])->name('admin.asesoria.articulos.editar');
    Route::post('/admin/asesoria/articulos/{articulo}/actualizar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'updateArticulo'])->name('admin.asesoria.articulos.actualizar');
    Route::delete('/admin/asesoria/articulos/{articulo}/eliminar', [\App\Http\Controllers\Admin\AsesoriaController::class, 'destroyArticulo'])->name('admin.asesoria.articulos.eliminar');

    // Códigos de Gestor
    Route::get('/admin/codigos-gestores', [CodigoGestorController::class, 'index']);
    Route::post('/admin/codigos-gestores/generar', [CodigoGestorController::class, 'generar']);
    Route::post('/admin/codigos-gestores/cancelar', [CodigoGestorController::class, 'cancelar']);
    Route::post('/admin/codigos-gestores/validar', [CodigoGestorController::class, 'validar']);
    Route::get('/admin/codigos-gestores/{id}', [CodigoGestorController::class, 'show']);

    // Códigos de Propiedad
    Route::get('/admin/codigos-propiedades', [CodigoPropiedadController::class, 'index']);
    Route::post('/admin/codigos-propiedades/generar', [CodigoPropiedadController::class, 'generar']);
    Route::post('/admin/codigos-propiedades/cancelar', [CodigoPropiedadController::class, 'cancelar']);
    Route::post('/admin/codigos-propiedades/validar', [CodigoPropiedadController::class, 'validar']);
    Route::post('/admin/codigos-propiedades/registrar-uso', [CodigoPropiedadController::class, 'registrarUso']);
    Route::get('/admin/codigos-propiedades/{id}', [CodigoPropiedadController::class, 'show']);
    Route::get('/admin/codigos-propiedades/propiedad/{idPropiedad}', [CodigoPropiedadController::class, 'obtenerCodigosDePropiedad']);

    // Alquileres
    Route::get('/admin/alquileres', [AlquilerController::class, 'index']);
    Route::get('/admin/alquileres/nuevo', [AlquilerController::class, 'nueva']);
    Route::get('/admin/alquileres/{id}/editar', [AlquilerController::class, 'editar']);
    Route::get('/admin/alquileres/filtrar', [AlquilerController::class, 'filtrar']);
    Route::get('/admin/alquileres/{id}', [AlquilerController::class, 'show']);
    Route::post('/admin/alquileres/crear', [AlquilerController::class, 'crear']);
    Route::post('/admin/alquileres/{id}/actualizar', [AlquilerController::class, 'actualizar']);
    Route::post('/admin/alquileres/{id}/eliminar', [AlquilerController::class, 'eliminar']);
    Route::post('/admin/alquiler/{id}/aprobar', [AlquilerController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AlquilerController::class, 'rechazar']);

    // Suscripciones
    // Route::get('/admin/suscripciones', [SuscripcionController::class, 'index']);
    // Route::get('/admin/suscripciones/filtrar', [SuscripcionController::class, 'filtrar']);
    // Route::get('/admin/suscripciones/{id}', [SuscripcionController::class, 'show']);
    // Route::post('/admin/suscripciones/{id}/editar', [SuscripcionController::class, 'editar']);
    // Route::post('/admin/suscripciones/{id}/cancelar', [SuscripcionController::class, 'cancelar']);
    // Route::get('/admin/suscripciones/exportar', [SuscripcionController::class, 'exportar']);
});

// Rutas Gestor
Route::middleware(['role:gestor'])->group(function () {
    Route::get('/gestor/dashboard', [GestorDashboardController::class, 'index'])->name('gestor.dashboard');

    Route::post('/gestor/notificaciones/{id}/marcar-leida', [GestorNotificacionController::class, 'marcarLeida'])->name('gestor.notificaciones.marcar-leida');
    Route::post('/gestor/notificaciones/{id}/eliminar', [GestorNotificacionController::class, 'eliminar'])->name('gestor.notificaciones.eliminar');
    Route::get('/gestor/incidencias', [GestorIncidenciaController::class, 'index'])->name('gestor.incidencias');
    Route::get('/gestor/incidencias/{id}', [GestorIncidenciaController::class, 'show'])->name('gestor.incidencias.show');
    Route::get('/gestor/propiedades', [GestorPropiedadController::class, 'index'])->name('gestor.propiedades');
    Route::get('/gestor/propiedades/{id}', [GestorPropiedadController::class, 'show'])->name('gestor.propiedades.show');
    Route::get('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'gastos'])->name('gestor.propiedades.gastos');
    Route::post('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'storeGasto'])->name('gestor.propiedades.gastos.store');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/editar', [GestorPropiedadController::class, 'updateGasto'])->name('gestor.propiedades.gastos.update');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/eliminar', [GestorPropiedadController::class, 'destroyGasto'])->name('gestor.propiedades.gastos.destroy');
    Route::post('/gestor/incidencias/{id}/iniciar', [GestorIncidenciaController::class, 'iniciarGestion'])->name('gestor.incidencias.iniciar');
    Route::post('/gestor/incidencias/{id}/estado', [GestorIncidenciaController::class, 'cambiarEstado'])->name('gestor.incidencias.estado');
    Route::post('/gestor/incidencias/{id}/espera', [GestorIncidenciaController::class, 'marcarEspera'])->name('gestor.incidencias.espera');
    Route::post('/gestor/incidencias/{id}/intervencion', [GestorIncidenciaController::class, 'registrarIntervencion'])->name('gestor.incidencias.intervencion');
    Route::post('/gestor/incidencias/{id}/comunicacion', [GestorIncidenciaController::class, 'registrarComunicacion'])->name('gestor.incidencias.comunicacion');
    Route::post('/gestor/incidencias/{id}/documento', [GestorIncidenciaController::class, 'subirDocumento'])->name('gestor.incidencias.documento');
    Route::post('/gestor/incidencias/{id}/presupuesto', [GestorIncidenciaController::class, 'crearPresupuesto'])->name('gestor.incidencias.presupuesto');

    Route::get('/gestor/propiedades/{id}/gastos/filtrar', [GestorPropiedadController::class, 'filtrarGastos'])->name('gestor.propiedades.gastos.filtrar');
    Route::get('/gestor/propiedades/{id}/editar-datos', [GestorPropiedadController::class, 'getDatosEdicion'])->name('gestor.propiedades.editar-datos');
    Route::post('/gestor/propiedades/{id}/editar', [GestorPropiedadController::class, 'actualizar'])->name('gestor.propiedades.actualizar');

    Route::get('/gestor/mensajes', [GestorMensajeController::class, 'index'])->name('gestor.mensajes.index');
    Route::post('/gestor/mensajes/iniciar/{propiedadId}', [GestorMensajeController::class, 'iniciar'])->name('gestor.mensajes.iniciar');
    Route::get('/gestor/mensajes/{id}', [GestorMensajeController::class, 'mostrar'])->name('gestor.mensajes.mostrar')->whereNumber('id');
    Route::post('/gestor/mensajes/{id}', [GestorMensajeController::class, 'enviar'])->name('gestor.mensajes.enviar')->whereNumber('id');

    Route::get('/gestor/perfil', [GestorPerfilController::class, 'index'])->name('gestor.perfil');
    Route::post('/gestor/perfil', [GestorPerfilController::class, 'update'])->name('gestor.perfil.update');

    Route::get('/gestor/asesoria', [AsesoriaController::class, 'index'])->name('gestor.asesoria');
    Route::get('/gestor/asesoria/buscar', [AsesoriaController::class, 'buscar'])->name('gestor.asesoria.buscar');
    Route::get('/gestor/asesoria/{slug}', [AsesoriaController::class, 'categoria'])->name('gestor.asesoria.categoria');

    Route::post('/gestor/asesoria/chatbot/iniciar', [ChatbotController::class, 'iniciarSesion'])->name('gestor.asesoria.chatbot.iniciar');
    Route::post('/gestor/asesoria/chatbot/mensaje', [ChatbotController::class, 'enviarMensaje'])->name('gestor.asesoria.chatbot.mensaje');
    Route::get('/gestor/asesoria/chatbot/historial', [ChatbotController::class, 'historial'])->name('gestor.asesoria.chatbot.historial');
});

// Rutas abiertas a usuarios autenticados para acciones sobre notificaciones
Route::middleware(['auth'])->group(function () {
    Route::post('/notificaciones/{id}/marcar-leida', [GestorNotificacionController::class, 'marcarLeida'])->name('notificaciones.marcar-leida');
    Route::post('/notificaciones/{id}/eliminar', [GestorNotificacionController::class, 'eliminar'])->name('notificaciones.eliminar');
});

// Rutas Arrendador
Route::middleware(['role:arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/arrendador/dashboard', [ArrendadorDashboardController::class, 'inicio'])->name('arrendador.dashboard');
    Route::get('/arrendador/asesoria', [AsesoriaController::class, 'index'])->name('arrendador.asesoria');
    Route::get('/arrendador/asesoria/buscar', [AsesoriaController::class, 'buscar'])->name('arrendador.asesoria.buscar');
    Route::get('/arrendador/asesoria/{slug}', [AsesoriaController::class, 'categoria'])->name('arrendador.asesoria.categoria');

    Route::post('/arrendador/asesoria/chatbot/iniciar', [ChatbotController::class, 'iniciarSesion'])->name('arrendador.asesoria.chatbot.iniciar');
    Route::post('/arrendador/asesoria/chatbot/mensaje', [ChatbotController::class, 'enviarMensaje'])->name('arrendador.asesoria.chatbot.mensaje');
    Route::get('/arrendador/asesoria/chatbot/historial', [ChatbotController::class, 'historial'])->name('arrendador.asesoria.chatbot.historial');

    Route::get('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'inicio'])->name('arrendador.propiedades');
    Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])->name('arrendador.propiedades.show');
    Route::delete('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'eliminar'])->name('arrendador.propiedades.eliminar');
    Route::post('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'guardar'])->name('arrendador.propiedades.store');
    Route::get('/arrendador/propiedades/datos', [ArrendadorPropiedadController::class, 'datosPropiedades'])->name('arrendador.propiedades.datos');
    Route::get('/arrendador/propiedades/{id}/editar-datos', [ArrendadorPropiedadController::class, 'datosEdicion'])->whereNumber('id')->name('arrendador.propiedades.edit-data');
    Route::get('/arrendador/propiedades/check-titulo', [ArrendadorPropiedadController::class, 'checkTitulo'])->name('arrendador.propiedades.check-titulo');
    Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])->whereNumber('id')->name('arrendador.propiedades.show');
    Route::get('/arrendador/gestores/disponibles', [ArrendadorGestorController::class, 'obtenerGestoresDisponibles'])->name('arrendador.gestores.disponibles');
    Route::post('/arrendador/gestores/validar-codigo', [ArrendadorPropiedadController::class, 'validarCodigoGestor'])->name('arrendador.gestores.validar-codigo');
    Route::get('/arrendador/propiedades/{propiedad}/gestor/permisos', [ArrendadorPropiedadController::class, 'getPermisosGestor'])->name('arrendador.permisos.get');
    Route::post('/arrendador/propiedades/{propiedad}/gestor/permisos', [ArrendadorPropiedadController::class, 'updatePermisosGestor'])->name('arrendador.permisos.update');
    Route::post('/arrendador/propiedades/{propiedad}/gestor/desasignar', [ArrendadorPropiedadController::class, 'desasignarGestor'])->name('arrendador.permisos.desasignar');

    Route::get('/arrendador/solicitudes', [ArrendadorSolicitudController::class, 'inicio'])->name('arrendador.solicitudes');
    Route::post('/arrendador/solicitudes/{id}/aprobar', [ArrendadorSolicitudController::class, 'aprobar'])->name('arrendador.solicitudes.aprobar');
    Route::post('/arrendador/solicitudes/{id}/rechazar', [ArrendadorSolicitudController::class, 'rechazar'])->name('arrendador.solicitudes.rechazar');
    Route::get('/arrendador/solicitudes/{id}/ver', [ArrendadorSolicitudController::class, 'ver'])->name('arrendador.solicitudes.ver');
    Route::post('/arrendador/solicitudes/{id}/actualizar', [ArrendadorSolicitudController::class, 'actualizar'])->name('arrendador.solicitudes.actualizar');
    Route::post('/arrendador/solicitudes/{id}/eliminar', [ArrendadorSolicitudController::class, 'eliminar'])->name('arrendador.solicitudes.eliminar');

    Route::get('/arrendador/precios-gastos', [ArrendadorPrecioGastoController::class, 'inicio'])->name('arrendador.precios-gastos');
    Route::post('/arrendador/precios-gastos/{id}', [ArrendadorPrecioGastoController::class, 'actualizar'])->name('arrendador.precios-gastos.actualizar');

    Route::get('/arrendador/inquilinos', [ArrendadorInquilinoController::class, 'inicio'])->name('arrendador.inquilinos');
    Route::get('/arrendador/inquilinos/{id}', [ArrendadorInquilinoController::class, 'mostrar'])->name('arrendador.inquilinos.show');


    Route::get('/arrendador/contratos', [ArrendadorContratoController::class, 'inicio'])->name('arrendador.contratos');
    Route::get('/arrendador/contratos/{id}/descargar-pdf', [ArrendadorContratoController::class, 'descargarPDF'])->name('arrendador.contratos.descargar-pdf');
    Route::post('/arrendador/contratos/{id}/subir-pdf', [ArrendadorContratoController::class, 'subirPDF'])->name('arrendador.contratos.subir-pdf');

    Route::get('/arrendador/gestor', [ArrendadorGestorController::class, 'inicio'])->name('arrendador.gestor');
    Route::post('/arrendador/gestor/{id}', [ArrendadorGestorController::class, 'actualizar'])->name('arrendador.gestor.actualizar');

    Route::get('/arrendador/incidencias', [ArrendadorIncidenciaController::class, 'inicio'])->name('arrendador.incidencias');
    Route::get('/arrendador/incidencias/{id}', [ArrendadorIncidenciaController::class, 'show'])->name('arrendador.incidencias.show');
    Route::post('/arrendador/incidencias/{id}/decision', [ArrendadorIncidenciaController::class, 'decidirPago'])->name('arrendador.incidencias.decision');
    Route::post('/arrendador/incidencias/{id}/pagar', [ArrendadorIncidenciaController::class, 'pagarPresupuesto'])->name('arrendador.incidencias.pagar');
});

Route::middleware(['role:miembro,inquilino,arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/miembro/inicio', [HomeController::class, 'index']);
    Route::get('/miembro/solicitud-arrendador', [SolicitudArrendadorController::class, 'create'])->name('miembro.arrendador.formulario');
    Route::post('/miembro/solicitud-arrendador', [SolicitudArrendadorController::class, 'store'])->name('miembro.arrendador.enviar');

    Route::get('/miembro/solicitud-gestor', [SolicitudGestorController::class, 'create'])->name('miembro.gestor.formulario');
    Route::post('/miembro/solicitud-gestor', [SolicitudGestorController::class, 'store'])->name('miembro.gestor.enviar');

    Route::get('/miembro/chat/{id}/mensajes', [MensajesController::class, 'obtenerMensajes'])->name('miembro.mensajes.mensajes');
    Route::post('/miembro/chat/{id}/mensaje', [MensajesController::class, 'enviarMensaje'])->name('miembro.mensajes.enviar');
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/miembro/asesoria', [AsesoriaController::class, 'index'])->name('miembro.asesoria');
    Route::get('/miembro/asesoria/buscar', [AsesoriaController::class, 'buscar'])->name('miembro.asesoria.buscar');
    Route::get('/miembro/asesoria/{slug}', [AsesoriaController::class, 'categoria'])->name('miembro.asesoria.categoria');

    Route::post('/miembro/asesoria/chatbot/iniciar', [ChatbotController::class, 'iniciarSesion'])->name('miembro.asesoria.chatbot.iniciar');
    Route::post('/miembro/asesoria/chatbot/mensaje', [ChatbotController::class, 'enviarMensaje'])->name('miembro.asesoria.chatbot.mensaje');
    Route::get('/miembro/asesoria/chatbot/historial', [ChatbotController::class, 'historial'])->name('miembro.asesoria.chatbot.historial');

    Route::get('/inquilino/asesoria', [AsesoriaController::class, 'index'])->name('inquilino.asesoria');
    Route::get('/inquilino/asesoria/buscar', [AsesoriaController::class, 'buscar'])->name('inquilino.asesoria.buscar');
    Route::get('/inquilino/asesoria/{slug}', [AsesoriaController::class, 'categoria'])->name('inquilino.asesoria.categoria');

    Route::post('/inquilino/asesoria/chatbot/iniciar', [ChatbotController::class, 'iniciarSesion'])->name('inquilino.asesoria.chatbot.iniciar');
    Route::post('/inquilino/asesoria/chatbot/mensaje', [ChatbotController::class, 'enviarMensaje'])->name('inquilino.asesoria.chatbot.mensaje');
    Route::get('/inquilino/asesoria/chatbot/historial', [ChatbotController::class, 'historial'])->name('inquilino.asesoria.chatbot.historial');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
});

// Rutas de configuración (Excluidas de 'arrendador.activo' para poder completarlas)
Route::middleware(['auth', 'role:miembro,inquilino,arrendador'])->group(function () {
    Route::get('/miembro/suscripcion', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'index'])->name('miembro.suscripcion.index');
    Route::post('/miembro/suscripcion/checkout', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'checkout'])->name('miembro.suscripcion.checkout');
    Route::get('/miembro/suscripcion/success', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'success'])->name('miembro.suscripcion.success');
    Route::post('/miembro/suscripcion/downgrade', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'downgrade'])->name('miembro.suscripcion.downgrade');
    Route::get('/miembro/perfil/{id}', [MiembroPerfilController::class, 'show'])->whereNumber('id')->name('miembro.perfil.show');
    Route::get('/miembro/configuracion', [MiembroPerfilController::class, 'configuracion'])->name('miembro.configuracion');
    Route::put('/miembro/configuracion', [MiembroPerfilController::class, 'actualizar'])->name('miembro.configuracion.actualizar');
    Route::put('/miembro/configuracion/plan', [MiembroPerfilController::class, 'actualizarPlan'])->name('miembro.configuracion.plan');
    Route::post('/miembro/configuracion/cancelar-suscripcion', [MiembroPerfilController::class, 'cancelarSuscripcion'])->name('miembro.configuracion.cancelar-suscripcion');
    Route::post('/miembro/configuracion/reactivar-suscripcion', [MiembroPerfilController::class, 'reactivarSuscripcion'])->name('miembro.configuracion.reactivar-suscripcion');
    Route::post('/miembro/configuracion/cancelar-cambio-programado', [MiembroPerfilController::class, 'cancelarCambioProgramado'])->name('miembro.configuracion.cancelar-cambio-programado');

    Route::get('/arrendador/configurar-stripe', [ConfiguracionCobrosController::class, 'index'])->name('arrendador.stripe.configurar');
    Route::post('/arrendador/guardar-iban', [ConfiguracionCobrosController::class, 'store'])->name('arrendador.guardar-iban');
});

Route::middleware(['role:miembro,inquilino,arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/miembro/propiedad/{id}', [DetallePropiedadController::class, 'show'])->name('miembro.detalle_propiedad');
    Route::post('/miembro/propiedad/{id}/solicitud-alquiler', [SolicitudAlquilerController::class, 'store'])->name('miembro.solicitud_alquiler.store');
    Route::post('/miembro/propiedad/{id}/chat', [MensajesController::class, 'iniciarDesdePropiedad'])->name('miembro.mensajes.iniciar');
    Route::get('/miembro/chat', [MensajesController::class, 'index'])->name('miembro.mensajes.index');
    Route::get('/miembro/chat/{id}', [MensajesController::class, 'show'])->name('miembro.mensajes.show');
    Route::get('/miembro/chat/{id}/mensajes', [MensajesController::class, 'obtenerMensajes'])->name('miembro.mensajes.mensajes');
    Route::post('/miembro/chat/{id}/mensaje', [MensajesController::class, 'enviarMensaje'])->name('miembro.mensajes.enviar');
    Route::post('/miembro/propiedad/{id}/chat-gestor', [MensajesController::class, 'iniciarDesdePropiedadGestor'])->name('miembro.mensajes.iniciar_gestor');
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
    Route::get('/inquilino/propiedad/{id}', [InquilinoController::class, 'verPropiedad'])->name('inquilino.ver_propiedad');
    Route::get('/inquilino/propiedad/{id}/descargar-contrato', [InquilinoController::class, 'descargarContrato'])->name('inquilino.descargar_contrato');

    // Rutas de Incidencias (Controlador Especializado)
    Route::get('/inquilino/propiedad/{id}/incidencias', [InquilinoIncidenciaController::class, 'getIncidencias'])->name('inquilino.get_incidencias');
    Route::get('/inquilino/incidencias/estados', [InquilinoIncidenciaController::class, 'obtenerEstadosIncidencias'])->name('inquilino.get_estados_incidencias');
    Route::get('/inquilino/incidencia/{id}/detalle', [InquilinoIncidenciaController::class, 'getDetalleIncidencia'])->name('inquilino.get_detalle_incidencia');
    Route::post('/inquilino/propiedad/{id}/incidencia', [InquilinoIncidenciaController::class, 'reportarIncidencia'])->name('inquilino.reportar_incidencia');
    Route::post('/inquilino/incidencia/{id}/decision-pago', [InquilinoIncidenciaController::class, 'decidirPagoIncidencia'])->name('inquilino.decision_pago_incidencia');
    Route::post('/inquilino/incidencia/{id}/pagar-presupuesto', [InquilinoIncidenciaController::class, 'pagarPresupuestoIncidencia'])->name('inquilino.pagar_presupuesto_incidencia');
    Route::post('/inquilino/incidencia/{id}/cerrar', [InquilinoIncidenciaController::class, 'cerrarIncidencia'])->name('inquilino.cerrar_incidencia');

    // Rutas de Pagos e Historial (Controlador Especializado)
    Route::post('/inquilino/cuotas/{cuotaId}/pagar', [InquilinoPagoController::class, 'pagarCuotaAlquiler'])->name('inquilino.pagar_cuota');
    Route::post('/inquilino/pagar-todo', [InquilinoPagoController::class, 'pagarTodo'])->name('inquilino.pagar_todo');
    Route::get('/inquilino/alquiler/{id}/historial-suministros', [InquilinoPagoController::class, 'obtenerHistorialSuministros'])->name('inquilino.historial_suministros');
    Route::get('/inquilino/alquiler/{id}/historial-alquiler', [InquilinoPagoController::class, 'obtenerHistorialAlquiler'])->name('inquilino.historial_alquiler');
    Route::get('/inquilino/pago/success', [InquilinoPagoController::class, 'stripeSuccess'])->name('inquilino.pago.success');
    Route::get('/inquilino/gastos', [InquilinoGastosController::class, 'index'])->name('inquilino.historial_pagos');
    Route::get('/inquilino/verificar-pagos-pdf', [InquilinoPagoController::class, 'verificarPagosConPdf'])->name('inquilino.verificar_pagos_pdf');

    Route::get('/inquilino/alquiler/{id}/estado-contrato', [InquilinoController::class, 'obtenerEstadoContrato'])->name('inquilino.estado_contrato');
    Route::get('/miembro/mapa/propiedades', [MapaController::class, 'propiedades'])->name('miembro.mapa.propiedades');

});

// Ruta temporal para ejecutar migraciones y seeders de forma segura desde el navegador
Route::get('/ejecutar-migraciones-seguras', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true
        ]);

        return view('inicio'); // Redirige a la página principal tras el éxito
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// (dev route removed)

// Limpiar cachés de Laravel (config, route, view, cache)
Route::get('/limpiar-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return view('login');
    } catch (\Exception $e) {
        return "Error al limpiar cachés: " . $e->getMessage();
    }
});
