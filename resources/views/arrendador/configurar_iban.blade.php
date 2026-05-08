<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotStay | Configurar Cobros</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/suscripcion_pago.css') }}">
    <link rel="stylesheet" href="{{ asset('css/configurar_iban.css') }}">
</head>
<body>

    <div class="background-city"></div>

    <div class="checkout-container">
        
        <div class="yeti-wrapper" style="width: 200px;">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <circle class="yeti-part" cx="62" cy="52" r="14" />
                <circle class="yeti-part" cx="138" cy="52" r="14" />
                <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
                <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
                <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
                <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
                <g id="face-group">
                    <circle cx="82" cy="105" r="5" fill="#000" />
                    <circle cx="118" cy="105" r="5" fill="#000" />
                    <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </g>
                <circle class="hand hand-l" cx="48" cy="180" r="19" />
                <circle class="hand hand-r" cx="152" cy="180" r="19" />
            </svg>
        </div>

        <div class="payment-card">
            <div class="card-header-gradient">
                <h2 class="mb-0 fw-bold">Configuración de Cobros</h2>
                <p class="mb-0 text-white-50 mt-1">Vincula tu cuenta bancaria para recibir pagos</p>
            </div>
            
            <div class="card-body">
                @if(session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i> {!! session('status') !!}
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! session('error') !!}
                </div>
                @endif
                @if($errors->any())
                <div class="alert alert-error">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle-fill me-2"></i> {!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('arrendador.guardar-iban') }}" method="POST">
                    @csrf
                    
                    <div class="row text-start">
                        <div class="col-md-12 mb-4">
                            <label class="form-label">IBAN de la Cuenta</label>
                            <input type="text" name="iban" id="iban-usuario" class="input-custom" 
                                   placeholder="ES00 0000 0000 0000 0000 0000" 
                                   value="{{ old('iban', $usuario->iban_usuario) }}" required>
                            <span id="error-iban" class="text-danger" style="font-size: 0.75rem;"></span>
                            <p class="helper-text"><i class="bi bi-info-circle me-1"></i> Formato internacional (Ej: ES + 22 dígitos)</p>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="form-label">Titular de la Cuenta</label>
                            <input type="text" name="titular" id="titular-cuenta" class="input-custom" 
                                   placeholder="Nombre completo o Razón Social" 
                                   value="{{ old('titular', $usuario->nombre_usuario) }}" required>
                            <span id="error-titular" class="text-danger" style="font-size: 0.75rem;"></span>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="form-label">Dirección Fiscal</label>
                            <textarea name="direccion_fiscal" id="direccion-fiscal" class="input-custom" rows="2" 
                                      placeholder="Calle, número, CP, Ciudad..." required>{{ old('direccion_fiscal', $usuario->direccion_fiscal_usuario) }}</textarea>
                            <span id="error-direccion" class="text-danger" style="font-size: 0.75rem;"></span>
                        </div>
                    </div>

                    <button type="submit" id="boton-finalizar" class="btn-pay mt-2">
                        <i class="bi bi-check-circle-fill me-2"></i> Guardar y Finalizar
                    </button>
                </form>
            </div>

            <div class="card-footer">
                <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-lock-fill me-1"></i> Tus datos bancarios se almacenan de forma cifrada y segura.
                </p>
            </div>
        </div>
    </div>

    <!-- JS de Validación -->
    <script src="{{ asset('js/configurar_iban.js') }}"></script>

</body>
</html>