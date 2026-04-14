<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inicio');
});

Route::get('/arrendador/dashboard', function () {
    return view('landlord.dashboard');
});

