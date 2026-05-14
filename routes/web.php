<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\Miembro\DetallePropiedadController;
use App\Http\Controllers\Miembro\MapaController;
use App\Http\Controllers\Miembro\SolicitudAlquilerController;
use App\Http\Controllers\Miembro\MensajesController;
use App\Http\Controllers\Miembro\SolicitudArrendadorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;
use App\Http\Controllers\Admin\CodigoGestorController;
use App\Http\Controllers\Admin\CodigoPropiedadController;
use App\Http\Controllers\inquilino\InquilinoController;
use App\Http\Controllers\inquilino\InquilinoIncidenciaController;
use App\Http\Controllers\inquilino\InquilinoPagoController;
use App\Http\Controllers\inquilino\InquilinoGastosController;
use App\Http\Controllers\Arrendador\DashboardController as ArrendadorDashboardController;
use App\Http\Controllers\Arrendador\PropiedadController as ArrendadorPropiedadController;
use App\Http\Controllers\Arrendador\SolicitudController as ArrendadorSolicitudController;
use App\Http\Controllers\Arrendador\PrecioGastoController as ArrendadorPrecioGastoController;
use App\Http\Controllers\Arrendador\InquilinoController as ArrendadorInquilinoController;
use App\Http\Controllers\Arrendador\MensajeController as ArrendadorMensajeController;
use App\Http\Controllers\Arrendador\ContratoController as ArrendadorContratoController;
use App\Http\Controllers\Arrendador\GestorController as ArrendadorGestorController;
use App\Http\Controllers\Arrendador\IncidenciaController as ArrendadorIncidenciaController;
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\IncidenciaController as GestorIncidenciaController;
use App\Http\Controllers\Gestor\PropiedadController as GestorPropiedadController;
use App\Http\Controllers\Arrendador\ConfiguracionCobrosController;

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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['role:admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/configuracion', function () {
        return view('admin.configuracion');
    });
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
    Route::get('/gestor/incidencias', [GestorIncidenciaController::class, 'index'])->name('gestor.incidencias');
    Route::get('/gestor/incidencias/{id}', [GestorIncidenciaController::class, 'show'])->name('gestor.incidencias.show');
    Route::get('/gestor/propiedades', [GestorPropiedadController::class, 'index'])->name('gestor.propiedades');
    Route::get('/gestor/propiedades/{id}', [GestorPropiedadController::class, 'show'])->name('gestor.propiedades.show');
    Route::get('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'gastos'])->name('gestor.propiedades.gastos');
    Route::post('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'storeGasto'])->name('gestor.propiedades.gastos.store');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/editar', [GestorPropiedadController::class, 'updateGasto'])->name('gestor.propiedades.gastos.update');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/eliminar', [GestorPropiedadController::class, 'destroyGasto'])->name('gestor.propiedades.gastos.destroy');
    Route::post('/gestor/propiedades/{id}/gastos/cuotas/{cuotaId}/pagos/{detalleId}', [GestorPropiedadController::class, 'marcarPagoGasto'])->name('gestor.propiedades.gastos.pago');
    Route::post('/gestor/incidencias/{id}/iniciar', [GestorIncidenciaController::class, 'iniciarGestion'])->name('gestor.incidencias.iniciar');
    Route::post('/gestor/incidencias/{id}/estado', [GestorIncidenciaController::class, 'cambiarEstado'])->name('gestor.incidencias.estado');
    Route::post('/gestor/incidencias/{id}/espera', [GestorIncidenciaController::class, 'marcarEspera'])->name('gestor.incidencias.espera');
    Route::post('/gestor/incidencias/{id}/intervencion', [GestorIncidenciaController::class, 'registrarIntervencion'])->name('gestor.incidencias.intervencion');
    Route::post('/gestor/incidencias/{id}/comunicacion', [GestorIncidenciaController::class, 'registrarComunicacion'])->name('gestor.incidencias.comunicacion');
    Route::post('/gestor/incidencias/{id}/documento', [GestorIncidenciaController::class, 'subirDocumento'])->name('gestor.incidencias.documento');
    Route::post('/gestor/incidencias/{id}/presupuesto', [GestorIncidenciaController::class, 'crearPresupuesto'])->name('gestor.incidencias.presupuesto');
});

// Rutas Arrendador
Route::middleware(['role:arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/arrendador/dashboard', [ArrendadorDashboardController::class, 'inicio'])->name('arrendador.dashboard');

    Route::get('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'inicio'])->name('arrendador.propiedades');
    Route::post('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'guardar'])->name('arrendador.propiedades.store');
    Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])->name('arrendador.propiedades.show');
    Route::post('/arrendador/propiedades/{id}/estado', [ArrendadorPropiedadController::class, 'alternarEstado'])->name('arrendador.propiedades.estado');

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

    Route::get('/arrendador/mensajes', [ArrendadorMensajeController::class, 'inicio'])->name('arrendador.mensajes');
    Route::get('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'mostrar'])->name('arrendador.mensajes.show');
    Route::post('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'enviar'])->name('arrendador.mensajes.enviar');

    Route::get('/arrendador/contratos', [ArrendadorContratoController::class, 'inicio'])->name('arrendador.contratos');
    Route::post('/arrendador/contratos/{id}/firmar', [ArrendadorContratoController::class, 'firmarArrendador'])->name('arrendador.contratos.firmar');
    Route::get('/arrendador/contratos/{id}/descargar-pdf', [ArrendadorContratoController::class, 'descargarPDF'])->name('arrendador.contratos.descargar-pdf');

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

    Route::get('/miembro/chat/{id}/mensajes', [MensajesController::class, 'obtenerMensajes'])->name('miembro.mensajes.mensajes');
    Route::post('/miembro/chat/{id}/mensaje', [MensajesController::class, 'enviarMensaje'])->name('miembro.mensajes.enviar');
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
});

// Rutas de configuración (Excluidas de 'arrendador.activo' para poder completarlas)
Route::middleware(['auth', 'role:miembro,inquilino,arrendador'])->group(function () {
    Route::get('/miembro/suscripcion', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'index'])->name('miembro.suscripcion.index');
    Route::post('/miembro/suscripcion/checkout', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'checkout'])->name('miembro.suscripcion.checkout');
    Route::get('/miembro/suscripcion/success', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'success'])->name('miembro.suscripcion.success');

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
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
    Route::get('/inquilino/propiedad/{id}', [InquilinoController::class, 'verPropiedad'])->name('inquilino.ver_propiedad');

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
    Route::get('/inquilino/alquiler/{id}/historial-suministros', [InquilinoPagoController::class, 'obtenerHistorialSuministros'])->name('inquilino.historial_suministros');
    Route::get('/inquilino/alquiler/{id}/historial-alquiler', [InquilinoPagoController::class, 'obtenerHistorialAlquiler'])->name('inquilino.historial_alquiler');
    Route::get('/inquilino/pago/success', [InquilinoPagoController::class, 'stripeSuccess'])->name('inquilino.pago.success');
    Route::get('/inquilino/gastos', [InquilinoGastosController::class, 'index'])->name('inquilino.historial_pagos');

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

// Limpiar cachés de Laravel (config, route, view, cache)
Route::get('/limpiar-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return "Cachés limpiadas correctamente.";
    } catch (\Exception $e) {
        return "Error al limpiar cachés: " . $e->getMessage();
    }
});
