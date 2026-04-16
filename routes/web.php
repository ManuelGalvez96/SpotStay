<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Arrendador\DashboardController as ArrendadorDashboardController;
use App\Http\Controllers\Arrendador\PropiedadController as ArrendadorPropiedadController;
use App\Http\Controllers\Arrendador\SolicitudController as ArrendadorSolicitudController;
use App\Http\Controllers\Arrendador\InquilinoController as ArrendadorInquilinoController;
use App\Http\Controllers\Arrendador\MensajeController as ArrendadorMensajeController;
use App\Http\Controllers\Arrendador\PrecioGastoController as ArrendadorPrecioGastoController;
use App\Http\Controllers\Arrendador\GestorController as ArrendadorGestorController;

Route::get('/', function () {
    return view('inicio');
});

// Arrendador
Route::get('/arrendador/dashboard', [ArrendadorDashboardController::class, 'index'])
    ->name('arrendador.dashboard');
Route::get('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'index'])
    ->name('arrendador.propiedades');
Route::post('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'guardar'])
    ->name('arrendador.propiedades.store');
Route::post('/arrendador/propiedades/{id}/estado', [ArrendadorPropiedadController::class, 'alternarEstado'])
    ->name('arrendador.propiedades.estado');
Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])
    ->name('arrendador.propiedades.show');
Route::get('/arrendador/solicitudes', [ArrendadorSolicitudController::class, 'index'])
    ->name('arrendador.solicitudes');
Route::post('/arrendador/solicitudes/{id}/aprobar', [ArrendadorSolicitudController::class, 'aprobar'])
    ->name('arrendador.solicitudes.aprobar');
Route::post('/arrendador/solicitudes/{id}/rechazar', [ArrendadorSolicitudController::class, 'rechazar'])
    ->name('arrendador.solicitudes.rechazar');
Route::get('/arrendador/inquilinos', [ArrendadorInquilinoController::class, 'index'])
    ->name('arrendador.inquilinos');
Route::get('/arrendador/inquilinos/{id}', [ArrendadorInquilinoController::class, 'mostrar'])
    ->name('arrendador.inquilinos.mostrar');
Route::get('/arrendador/mensajes', [ArrendadorMensajeController::class, 'index'])
    ->name('arrendador.mensajes');
Route::get('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'mostrar'])
    ->name('arrendador.mensajes.mostrar');
Route::post('/arrendador/mensajes/{id}/enviar', [ArrendadorMensajeController::class, 'enviar'])
    ->name('arrendador.mensajes.enviar');
Route::get('/arrendador/precios-gastos', [ArrendadorPrecioGastoController::class, 'index'])
    ->name('arrendador.precios-gastos');
Route::post('/arrendador/precios-gastos/{id}', [ArrendadorPrecioGastoController::class, 'actualizar'])
    ->name('arrendador.precios-gastos.actualizar');
Route::get('/arrendador/gestor', [ArrendadorGestorController::class, 'index'])
    ->name('arrendador.gestor');
Route::post('/arrendador/gestor/{id}', [ArrendadorGestorController::class, 'actualizar'])
    ->name('arrendador.gestor.actualizar');

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
Route::get('/admin/incidencias/{id}', [IncidenciaController::class, 'show']);
Route::post('/admin/incidencias/{id}/estado', [IncidenciaController::class, 'cambiarEstado']);
Route::post('/admin/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);

