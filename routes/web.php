<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;

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
Route::post('/admin/incidencias/crear', [IncidenciaController::class, 'crear']);
Route::get('/admin/incidencias/{id}', [IncidenciaController::class, 'show']);
Route::post('/admin/incidencias/{id}/estado', [IncidenciaController::class, 'cambiarEstado']);
Route::post('/admin/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);

// Alquileres
Route::get('/admin/alquileres', [AlquilerController::class, 'index']);
Route::get('/admin/alquileres/filtrar', [AlquilerController::class, 'filtrar']);
Route::post('/admin/alquileres/crear', [AlquilerController::class, 'crear']);
Route::get('/admin/alquileres/{id}', [AlquilerController::class, 'show']);
Route::post('/admin/alquiler/{id}/aprobar', [AlquilerController::class, 'aprobar']);
Route::post('/admin/alquiler/{id}/rechazar', [AlquilerController::class, 'rechazar']);

// Suscripciones
Route::get('/admin/suscripciones', [SuscripcionController::class, 'index']);
Route::get('/admin/suscripciones/filtrar', [SuscripcionController::class, 'filtrar']);
Route::get('/admin/suscripciones/exportar', [SuscripcionController::class, 'exportar']);
Route::get('/admin/suscripciones/{id}', [SuscripcionController::class, 'show']);
Route::post('/admin/suscripciones/{id}/editar', [SuscripcionController::class, 'editar']);
Route::post('/admin/suscripciones/{id}/cancelar', [SuscripcionController::class, 'cancelar']);

