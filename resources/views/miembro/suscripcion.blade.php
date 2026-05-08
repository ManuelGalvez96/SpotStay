<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotStay | Suscripción</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/suscripcion_pago.css') }}">
</head>
<body>

    <div class="background-city"></div>

    <div class="checkout-container">
        
        <div class="yeti-wrapper">
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
                <h2 class="mb-0 fw-bold">Completa tu Registro</h2>
                <p class="mb-0 text-white-50 mt-1">Activa tu suscripción {{ $suscripcion->plan_suscripcion }}</p>
            </div>
            
            <div class="card-body">
                <div class="price-display">
                    <span class="period">Pago Total</span>
                    <span class="amount">{{ number_format($suscripcion->precio_pagado_suscripcion, 2) }}€</span>
                    <span class="period">Incluye todos los servicios</span>
                </div>

                <div class="px-4">
                    <form action="{{ route('miembro.suscripcion.checkout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-pay">
                            <i class="bi bi-credit-card-2-front-fill me-2"></i> Pagar con Tarjeta
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-footer">
                <div class="text-muted" style="font-size: 0.85rem; color: #757575 !important;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Transacción segura procesada por Stripe
                </div>
                
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" class="stripe-logo" alt="Stripe">
            </div>
        </div>
    </div>

</body>
</html>