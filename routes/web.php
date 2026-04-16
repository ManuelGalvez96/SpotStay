<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;

// Rutas Públicas
Route::get('/', function () {
    return view('inicio');
});
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

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
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/alquiler/{id}/aprobar', [AdminController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AdminController::class, 'rechazar']);

    // Usuarios
    Route::get('/admin/usuarios', [AdminController::class, 'usuarios']);
    Route::get('/admin/usuarios/filtrar', [AdminController::class, 'filtrarUsuarios']);
    Route::post('/admin/usuarios/{id}/toggle-estado', [AdminController::class, 'toggleEstado']);
    Route::get('/admin/usuarios/exportar', [AdminController::class, 'exportarUsuarios']);

    // Propiedades
    Route::get('/admin/propiedades', [AdminController::class, 'propiedades']);
    Route::get('/admin/propiedades/filtrar', [AdminController::class, 'filtrarPropiedades']);
    Route::post('/admin/propiedades/{id}/desactivar', [AdminController::class, 'desactivarPropiedad']);
    Route::delete('/admin/propiedades/{id}', [AdminController::class, 'eliminarPropiedad']);
    Route::get('/admin/propiedades/exportar', [AdminController::class, 'exportarPropiedades']);

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

Route::get('/miembro/inicio', [HomeController::class, 'index']);
Route::get('/miembro/mapa', function () {
    return view('miembro.mapa');
});

Route::middleware(['role:miembro'])->group(function () {
    Route::get('/miembro/inicio', [HomeController::class, 'index']);
    Route::get('/miembro/mapa', function () {
        return view('miembro.mapa');
    });
});
