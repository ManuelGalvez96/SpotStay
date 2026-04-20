<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\Miembro\DetallePropiedadController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;
use App\Http\Controllers\inquilino\InquilinoController;
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

Route::middleware(['role:miembro,inquilino,propietario'])->group(function () {
    Route::get('/miembro/inicio', [HomeController::class, 'index']);
    Route::get('/miembro/propiedad/{id}', [DetallePropiedadController::class, 'show'])->name('miembro.detalle_propiedad');
    Route::get('/miembro/mapa', function () {
        return view('miembro.mapa');
    });

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
    Route::get('/inquilino/propiedad/{id}', [InquilinoController::class, 'verPropiedad'])->name('inquilino.ver_propiedad');
    Route::post('/inquilino/propiedad/{id}/incidencia', [InquilinoController::class, 'reportarIncidencia'])->name('inquilino.reportar_incidencia');
    Route::post('/inquilino/incidencia/{id}/cerrar', [InquilinoController::class, 'cerrarIncidencia'])->name('inquilino.cerrar_incidencia');
});
