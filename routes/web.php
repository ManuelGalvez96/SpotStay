<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('inicio');
});

// Rutas del Admin
Route::middleware(['web'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/alquiler/{id}/aprobar', [AdminController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AdminController::class, 'rechazar']);
});
