@extends('layouts.miembro')

@section('title', 'Mi perfil - SpotStay')

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="d-flex justify-content-center mb-3">
                        @if($fotoPerfil)
                            <img src="{{ $fotoPerfil }}" alt="Foto de perfil" class="rounded-circle d-block" style="width: 96px; height: 96px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center" style="width: 96px; height: 96px; font-size: 2rem; font-weight: 700;">
                                {{ strtoupper(substr($usuario->nombre_usuario ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h1 class="h4 mb-1">{{ $usuario->nombre_usuario }}</h1>
                    <p class="text-muted mb-3">{{ $usuario->email_usuario }}</p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('miembro.configuracion') }}" class="btn btn-dark">Editar perfil</a>
                        <a href="{{ url('/miembro/inicio') }}" class="btn btn-outline-secondary">Volver al inicio</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Datos personales</h2>
                            <p class="text-muted mb-0">Información visible desde tu perfil de miembro.</p>
                        </div>
                        <span class="badge text-bg-dark">{{ $usuario->roles->pluck('nombre_rol')->join(', ') }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Nombre</label>
                            <div class="fw-semibold">{{ $usuario->nombre_usuario }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Correo</label>
                            <div class="fw-semibold">{{ $usuario->email_usuario }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Teléfono</label>
                            <div class="fw-semibold">{{ $usuario->telefono_usuario ?: 'No indicado' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Documento de Identidad (DNI/NIE)</label>
                            <div class="fw-semibold">{{ $usuario->dni_usuario ?: 'No indicado' }}</div>
                        </div>

                    </div>
                </div>
            </div>

            @if($rolDestinatario === 'arrendador')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Datos de Facturación</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Tipo de Arrendador</label>
                            <div class="fw-semibold">{{ ucfirst($usuario->tipo_arrendador_usuario) ?: 'No indicado' }}</div>
                        </div>
                        
                        @if($usuario->tipo_arrendador_usuario === 'empresa')
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">NIF de la Empresa</label>
                            <div class="fw-semibold">{{ $usuario->cif_usuario ?: 'No indicado' }}</div>
                        </div>
                        @endif
                        
                        <div class="col-md-{{ $usuario->tipo_arrendador_usuario === 'empresa' ? '12' : '6' }}">
                            <label class="form-label text-muted mb-1">Cuenta Bancaria (IBAN)</label>
                            <div class="fw-semibold">{{ $usuario->iban_usuario ?: 'No indicado' }}</div>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label text-muted mb-1">Dirección fiscal</label>
                            <div class="fw-semibold">{{ $usuario->direccion_fiscal_usuario ?: 'No indicada' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Suscripción actual</h2>
                    {{-- Los días restantes se calculan en el controlador y se pasan como $diasRestantesSuscripcion --}}
                    @if($suscripcionActual)
                        <div class="row g-3">
                            <div class="{{ $rolDestinatario === 'arrendador' ? 'col-md-4' : 'col-md-6' }}">
                                <div class="p-3 rounded-3 bg-light h-100">
                                    <div class="text-muted small">Plan</div>
                                    <div class="fw-semibold">{{ $suscripcionActual->plan_suscripcion }}</div>
                                </div>
                            </div>
                            <div class="{{ $rolDestinatario === 'arrendador' ? 'col-md-4' : 'col-md-6' }}">
                                <div class="p-3 rounded-3 bg-light h-100">
                                    <div class="text-muted small">Estado</div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $suscripcionActual->estado_suscripcion)) }}</div>
                                </div>
                            </div>
                            @if($rolDestinatario === 'arrendador')
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Máximo de propiedades</div>
                                        <div class="fw-semibold">{{ $suscripcionActual->max_propiedades_suscripcion }}</div>
                                    </div>
                                </div>
                            @endif

                            @if (isset($diasRestantesSuscripcion))
                                <div class="{{ $rolDestinatario === 'arrendador' ? 'col-md-4' : 'col-md-12' }}">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Días restantes</div>
                                        <div class="fw-semibold">
                                            @if ($suscripcionActual->estado_suscripcion === 'cancelada')
                                                {{ $diasRestantesSuscripcion }} {{ $diasRestantesSuscripcion === 1 ? 'día' : 'días' }} para cancelar
                                            @else
                                                {{ $diasRestantesSuscripcion }} {{ $diasRestantesSuscripcion === 1 ? 'día' : 'días' }} para renovar
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="mb-0 text-muted">Todavía no tienes una suscripción registrada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection