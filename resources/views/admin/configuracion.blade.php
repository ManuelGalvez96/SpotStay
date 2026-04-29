@extends('layouts.admin')

@section('titulo', 'Configuración — SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/configuracion.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Configuración general</h1>
        <p>Administra los ajustes globales del panel de administración</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid-pequeno">
</div>

<div class="card-admin">
    <div class="tabla-header">
        <span class="info-paginacion">Secciones disponibles de configuración</span>
    </div>

    <div class="configuracion-admin-body">
        <div class="configuracion-grid">
        </div>
    </div>
</div>
@endsection