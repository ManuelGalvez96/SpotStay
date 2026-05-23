@extends('layouts.miembro')

@section('title', 'Configuración - SpotStay')

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h1 class="h3 mb-1">Configuración de cuenta</h1>
                                <p class="text-muted mb-0">Edita tus datos personales y seguridad.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('miembro.configuracion.actualizar') }}"
                            enctype="multipart/form-data" class="row g-3" id="form-configuracion-cuenta"
                            data-email-original="{{ $usuario->email_usuario }}"
                            data-telefono-original="{{ $usuario->telefono_usuario }}"
                            data-dni-original="{{ $usuario->dni_usuario }}">
                            @csrf
                            @method('PUT')

                            <div class="col-md-6">
                                <label for="nombre_usuario" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario"
                                    value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}">
                                <div id="error-nombre-usuario" class="text-danger small mt-1" aria-live="polite"></div>
                                @error('nombre_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email_usuario" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email_usuario" name="email_usuario"
                                    value="{{ old('email_usuario', $usuario->email_usuario) }}">
                                <div id="error-email-usuario" class="text-danger small mt-1" aria-live="polite"></div>
                                @error('email_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono_usuario" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_usuario" name="telefono_usuario"
                                    value="{{ old('telefono_usuario', $usuario->telefono_usuario) }}">
                                <div id="error-telefono-usuario" class="text-danger small mt-1" aria-live="polite"></div>
                                @error('telefono_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="dni_usuario" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="dni_usuario" name="dni_usuario"
                                    value="{{ old('dni_usuario', $usuario->dni_usuario) }}">
                                <div id="error-dni-usuario" class="text-danger small mt-1" aria-live="polite"></div>
                                @error('dni_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="fecha_nacimiento_usuario" class="form-label">Fecha de nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento_usuario"
                                    name="fecha_nacimiento_usuario"
                                    value="{{ old('fecha_nacimiento_usuario', optional($usuario->fecha_nacimiento_usuario)->format('Y-m-d')) }}">
                                <div id="error-fecha-nacimiento-usuario" class="text-danger small mt-1" aria-live="polite">
                                </div>
                                @error('fecha_nacimiento_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="avatar_usuario" class="form-label">Avatar</label>
                                <input type="file" class="form-control" id="avatar_usuario" name="avatar_usuario"
                                    accept="image/*">
                                <div id="error-avatar-usuario" class="text-danger small mt-1" aria-live="polite"></div>
                                @error('avatar_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="direccion_fiscal_usuario" class="form-label">Dirección fiscal</label>
                                <textarea class="form-control" id="direccion_fiscal_usuario" name="direccion_fiscal_usuario" rows="3">{{ old('direccion_fiscal_usuario', $usuario->direccion_fiscal_usuario) }}</textarea>
                                <div id="error-direccion-fiscal-usuario" class="text-danger small mt-1"
                                    aria-live="polite"></div>
                                @error('direccion_fiscal_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h2 class="h5 mb-3">Cambiar contraseña</h2>
                            </div>

                            <div class="col-md-4">
                                <label for="contrasena_usuario" class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" id="contrasena_usuario"
                                    name="contrasena_usuario" autocomplete="new-password">
                                <div id="error-contrasena-usuario" class="text-danger small mt-1" aria-live="polite">
                                </div>
                                @error('contrasena_usuario')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="contrasena_usuario_confirmation" class="form-label">Confirmar
                                    contraseña</label>
                                <input type="password" class="form-control" id="contrasena_usuario_confirmation"
                                    name="contrasena_usuario_confirmation" autocomplete="new-password">
                                <div id="error-contrasena-usuario-confirmation" class="text-danger small mt-1"
                                    aria-live="polite"></div>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-dark px-4" id="boton-guardar-cambios"
                                    disabled>Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-2">Resumen</h2>
                        <p class="text-muted mb-4">Aquí puedes revisar el estado actual de tu cuenta.</p>

                        {{-- Los días restantes se calculan en el controlador y se pasan como $diasRestantesSuscripcion --}}

                        <div class="mb-3">
                            <div class="text-muted small">Rol</div>
                            <div class="fw-semibold">{{ $usuario->roles->pluck('nombre_rol')->join(', ') }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Estado de suscripción</div>
                            <div class="fw-semibold">
                                {{ $suscripcionActual ? ucfirst(str_replace('_', ' ', $suscripcionActual->estado_suscripcion)) : 'Sin suscripción' }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Correo de acceso</div>
                            <div class="fw-semibold">{{ $usuario->email_usuario }}</div>
                        </div>
                    </div>
                </div>

                @if ($rolDestinatario)
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-3">Plan de suscripción</h2>

                            @if ($suscripcionActual)
                                <div class="mb-3 p-3 rounded-3 bg-light">
                                    <div class="text-muted small">Plan actual</div>
                                    <div class="fw-semibold">{{ $suscripcionActual->plan_suscripcion }}</div>
                                    <div class="text-muted small mt-2">Precio</div>
                                    <div class="fw-semibold">
                                        {{ number_format((float) $suscripcionActual->precio_pagado_suscripcion, 2) }} €
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-12 col-sm-6 col-lg-6">
                                        <div class="p-3 rounded-3 bg-light h-100">
                                            <div class="text-muted small">Plan</div>
                                            <div class="fw-semibold">{{ $suscripcionActual->plan_suscripcion }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg-6">
                                        <div class="p-3 rounded-3 bg-light h-100">
                                            <div class="text-muted small">Estado</div>
                                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $suscripcionActual->estado_suscripcion)) }}</div>
                                        </div>
                                    </div>

                                    @if($rolDestinatario === 'arrendador')
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <div class="p-3 rounded-3 bg-light h-100">
                                                <div class="text-muted small">Máximo de propiedades</div>
                                                <div class="fw-semibold">{{ $suscripcionActual->max_propiedades_suscripcion }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ((float) $suscripcionActual->precio_pagado_suscripcion > 0 && isset($diasRestantesSuscripcion))
                                        <div class="mb-3 p-3 rounded-3 bg-light">
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

                            @endif

                            <form method="POST" action="{{ route('miembro.configuracion.plan') }}"
                                class="d-grid gap-3">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="id_plan" class="form-label">Cambiar a un plan disponible</label>
                                    <select class="form-select" id="id_plan" name="id_plan" required>
                                        <option value="">Selecciona un plan</option>
                                        @foreach ($planesDisponibles as $plan)
                                            <option value="{{ $plan->id_plan }}">
                                                {{ $plan->nombre_plan }} -
                                                {{ number_format((float) $plan->precio_plan, 2) }} €
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-outline-dark">Actualizar plan</button>
                            </form>

                            @if (
                                $suscripcionActual &&
                                    (float) $suscripcionActual->precio_pagado_suscripcion > 0 &&
                                    $suscripcionActual->estado_suscripcion !== 'cancelada')
                                <form method="POST" action="{{ route('miembro.configuracion.cancelar-suscripcion') }}"
                                    class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100">Cancelar
                                        suscripción</button>
                                </form>
                            @endif

                            @if ($suscripcionActual && $suscripcionActual->estado_suscripcion === 'cancelada')
                                <form method="POST" action="{{ route('miembro.configuracion.reactivar-suscripcion') }}"
                                    class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">Reactivar suscripción</button>
                                </form>
                            @endif

                            <p class="text-muted small mb-0 mt-3">Si eliges un plan de pago, pasarás al proceso de
                                activación para completar la suscripción.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

            @section('scripts')
                <script src="{{ asset('js/miembro/configuracion-cuenta.js') }}?v=1"></script>
            @endsection
