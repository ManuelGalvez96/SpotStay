<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\IncidenciaController as GestorIncidenciaController;

Route::get('/', function () {
    return view('inicio');
});

// Logout
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return response()->json(['success' => true]);
});

// Rutas Admin - Sin middleware
Route::get('/admin/dashboard', [DashboardController::class, 'index']);
Route::post('/admin/alquiler/{id}/aprobar', [DashboardController::class, 'aprobarAlquiler']);
Route::post('/admin/alquiler/{id}/rechazar', [DashboardController::class, 'rechazarAlquiler']);

// Rutas Gestor
Route::get('/gestor/dashboard', [GestorDashboardController::class, 'index']);
Route::get('/gestor/incidencias/{id}', [GestorIncidenciaController::class, 'show']);
Route::post('/gestor/incidencias/{id}/iniciar', [GestorIncidenciaController::class, 'iniciarGestion']);
Route::post('/gestor/incidencias/{id}/estado', [GestorIncidenciaController::class, 'cambiarEstado']);
Route::post('/gestor/incidencias/{id}/espera', [GestorIncidenciaController::class, 'marcarEspera']);
Route::post('/gestor/incidencias/{id}/intervencion', [GestorIncidenciaController::class, 'registrarIntervencion']);
Route::post('/gestor/incidencias/{id}/comunicacion', [GestorIncidenciaController::class, 'registrarComunicacion']);
Route::post('/gestor/incidencias/{id}/documento', [GestorIncidenciaController::class, 'subirDocumento']);
Route::post('/gestor/incidencias/{id}/presupuesto', [GestorIncidenciaController::class, 'crearPresupuesto']);

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

