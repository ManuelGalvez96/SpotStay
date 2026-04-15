<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('inicio');
});

// Rutas del Admin
Route::middleware(['web'])->group(function () {
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
});
