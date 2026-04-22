<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;
use App\Http\Controllers\Arrendador\DashboardController as ArrendadorDashboardController;
use App\Http\Controllers\Arrendador\PropiedadController as ArrendadorPropiedadController;
use App\Http\Controllers\Arrendador\SolicitudController as ArrendadorSolicitudController;
use App\Http\Controllers\Arrendador\PrecioGastoController as ArrendadorPrecioGastoController;
use App\Http\Controllers\Arrendador\InquilinoController as ArrendadorInquilinoController;
use App\Http\Controllers\Arrendador\MensajeController as ArrendadorMensajeController;
use App\Http\Controllers\Arrendador\ContratoController as ArrendadorContratoController;
use App\Http\Controllers\Arrendador\GestorController as ArrendadorGestorController;
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\IncidenciaController as GestorIncidenciaController;
use App\Http\Controllers\Gestor\PropiedadController as GestorPropiedadController;

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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['role:admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::post('/admin/alquiler/{id}/aprobar', [DashboardController::class, 'aprobarAlquiler']);
    Route::post('/admin/alquiler/{id}/rechazar', [DashboardController::class, 'rechazarAlquiler']);

    // Usuarios
    Route::get('/admin/usuarios', [UsuarioController::class, 'index']);
    Route::get('/admin/usuarios/filtrar', [UsuarioController::class, 'filtrar']);
    Route::get('/admin/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/admin/usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado']);
    Route::get('/admin/usuarios/exportar', [UsuarioController::class, 'exportar']);

    // Propiedades
    Route::get('/admin/propiedades', [PropiedadController::class, 'index']);
    Route::get('/admin/propiedades/nueva', [PropiedadController::class, 'nueva']);
    Route::post('/admin/propiedades/crear', [PropiedadController::class, 'crear']);
    Route::get('/admin/propiedades/filtrar', [PropiedadController::class, 'filtrar']);
    Route::get('/admin/propiedades/{id}', [PropiedadController::class, 'show']);
    Route::post('/admin/propiedades/{id}/desactivar', [PropiedadController::class, 'desactivar']);
    Route::get('/admin/propiedades/exportar', [PropiedadController::class, 'exportar']);

    // Solicitudes
    Route::get('/admin/solicitudes', [SolicitudController::class, 'index']);
    Route::get('/admin/solicitudes/filtrar', [SolicitudController::class, 'filtrar']);
    Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show']);
    Route::post('/admin/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::post('/admin/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar']);

    // Incidencias
    Route::get('/admin/incidencias', [IncidenciaController::class, 'index']);
    Route::get('/admin/incidencias/filtrar', [IncidenciaController::class, 'filtrar']);
    Route::get('/admin/incidencias/{id}', [IncidenciaController::class, 'show']);
    Route::post('/admin/incidencias/{id}/estado', [IncidenciaController::class, 'cambiarEstado']);
    Route::post('/admin/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);

    // Alquileres
    Route::get('/admin/alquileres', [AlquilerController::class, 'index']);
    Route::get('/admin/alquileres/nuevo', [AlquilerController::class, 'nueva']);
    Route::get('/admin/alquileres/filtrar', [AlquilerController::class, 'filtrar']);
    Route::get('/admin/alquileres/{id}', [AlquilerController::class, 'show']);
    Route::post('/admin/alquileres/crear', [AlquilerController::class, 'crear']);
    Route::post('/admin/alquiler/{id}/aprobar', [AlquilerController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AlquilerController::class, 'rechazar']);

    // Suscripciones
    Route::get('/admin/suscripciones', [SuscripcionController::class, 'index']);
    Route::get('/admin/suscripciones/filtrar', [SuscripcionController::class, 'filtrar']);
    Route::get('/admin/suscripciones/{id}', [SuscripcionController::class, 'show']);
    Route::post('/admin/suscripciones/{id}/editar', [SuscripcionController::class, 'editar']);
    Route::post('/admin/suscripciones/{id}/cancelar', [SuscripcionController::class, 'cancelar']);
    Route::get('/admin/suscripciones/exportar', [SuscripcionController::class, 'exportar']);
});

// Rutas Gestor
Route::middleware(['role:gestor'])->group(function () {
    Route::get('/gestor/dashboard', [GestorDashboardController::class, 'index']);
    Route::get('/gestor/incidencias', [GestorIncidenciaController::class, 'index']);
    Route::get('/gestor/incidencias/{id}', [GestorIncidenciaController::class, 'show']);
    Route::get('/gestor/propiedades', [GestorPropiedadController::class, 'index']);
    Route::get('/gestor/propiedades/{id}', [GestorPropiedadController::class, 'show']);
    Route::get('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'gastos']);
    Route::post('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'storeGasto']);
    Route::post('/gestor/propiedades/{id}/gastos/cuotas/{cuotaId}/pagos/{detalleId}', [GestorPropiedadController::class, 'marcarPagoGasto']);
    Route::post('/gestor/incidencias/{id}/iniciar', [GestorIncidenciaController::class, 'iniciarGestion']);
    Route::post('/gestor/incidencias/{id}/estado', [GestorIncidenciaController::class, 'cambiarEstado']);
    Route::post('/gestor/incidencias/{id}/espera', [GestorIncidenciaController::class, 'marcarEspera']);
    Route::post('/gestor/incidencias/{id}/intervencion', [GestorIncidenciaController::class, 'registrarIntervencion']);
    Route::post('/gestor/incidencias/{id}/comunicacion', [GestorIncidenciaController::class, 'registrarComunicacion']);
    Route::post('/gestor/incidencias/{id}/documento', [GestorIncidenciaController::class, 'subirDocumento']);
    Route::post('/gestor/incidencias/{id}/presupuesto', [GestorIncidenciaController::class, 'crearPresupuesto']);
});

// Rutas Arrendador
Route::middleware(['role:arrendador'])->group(function () {
    Route::get('/arrendador/dashboard', [ArrendadorDashboardController::class, 'inicio'])->name('arrendador.dashboard');

    Route::get('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'inicio'])->name('arrendador.propiedades');
    Route::post('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'guardar'])->name('arrendador.propiedades.store');
    Route::post('/arrendador/propiedades/{id}/estado', [ArrendadorPropiedadController::class, 'alternarEstado'])->name('arrendador.propiedades.estado');
    Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])->name('arrendador.propiedades.show');

    Route::get('/arrendador/solicitudes', [ArrendadorSolicitudController::class, 'inicio'])->name('arrendador.solicitudes');
    Route::post('/arrendador/solicitudes/{id}/aprobar', [ArrendadorSolicitudController::class, 'aprobar'])->name('arrendador.solicitudes.aprobar');
    Route::post('/arrendador/solicitudes/{id}/rechazar', [ArrendadorSolicitudController::class, 'rechazar'])->name('arrendador.solicitudes.rechazar');

    Route::get('/arrendador/precios-gastos', [ArrendadorPrecioGastoController::class, 'inicio'])->name('arrendador.precios-gastos');
    Route::post('/arrendador/precios-gastos/{id}', [ArrendadorPrecioGastoController::class, 'actualizar'])->name('arrendador.precios-gastos.actualizar');

    Route::get('/arrendador/inquilinos', [ArrendadorInquilinoController::class, 'inicio'])->name('arrendador.inquilinos');
    Route::get('/arrendador/inquilinos/{id}', [ArrendadorInquilinoController::class, 'mostrar'])->name('arrendador.inquilinos.show');

    Route::get('/arrendador/mensajes', [ArrendadorMensajeController::class, 'inicio'])->name('arrendador.mensajes');
    Route::get('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'mostrar'])->name('arrendador.mensajes.show');
    Route::post('/arrendador/mensajes/{id}/enviar', [ArrendadorMensajeController::class, 'enviar'])->name('arrendador.mensajes.enviar');

    Route::get('/arrendador/contratos', [ArrendadorContratoController::class, 'inicio'])->name('arrendador.contratos');
    Route::post('/arrendador/contratos/{id}/firmar-arrendador', [ArrendadorContratoController::class, 'firmarArrendador'])->name('arrendador.contratos.firmar-arrendador');

    Route::get('/arrendador/gestor', [ArrendadorGestorController::class, 'inicio'])->name('arrendador.gestor');
    Route::post('/arrendador/gestor/{id}', [ArrendadorGestorController::class, 'actualizar'])->name('arrendador.gestor.actualizar');
});

Route::middleware(['role:miembro,inquilino'])->group(function () {
    Route::get('/miembro/inicio', [HomeController::class, 'index']);
    Route::get('/miembro/mapa', function () {
        return view('miembro.mapa');
    });
});
