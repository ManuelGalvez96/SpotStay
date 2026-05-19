@extends('layouts.miembro')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 100vh;">
    <!-- Encabezado con efecto Glassmorphism -->
    <div class="mb-4 p-4" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="mb-1" style="font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Centro de Pagos</h2>
                <p class="text-muted mb-0">Gestiona y descarga tus recibos de alquiler e incidencias</p>
            </div>
            <div class="col-auto">
                <div class="p-2 px-3" style="background: #00c4cc15; border-radius: 12px; border: 1px solid #00c4cc30;">
                    <span style="color: #00c4cc; font-weight: 700;">{{ $pagos->total() }} Transacciones</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Filtros Moderno -->
    <div class="mb-4 p-4" style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #edf2f7;">
        <form action="{{ route('inquilino.historial_pagos') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted">Desde</label>
                <input type="date" name="desde" class="form-control" value="{{ request('desde') }}" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px;">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px;">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted">Tipo</label>
                <select name="tipo" class="form-select" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px;">
                    <option value="">Todos</option>
                    <option value="alquiler" {{ request('tipo') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                    <option value="gasto" {{ request('tipo') == 'gasto' ? 'selected' : '' }}>Gasto</option>
                    <option value="incidencia" {{ request('tipo') == 'incidencia' ? 'selected' : '' }}>Incidencia</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted">Referencia</label>
                <input type="text" name="referencia" class="form-control" placeholder="TXN..." value="{{ request('referencia') }}" style="border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px;">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100" style="background: #00c4cc; border: none; border-radius: 10px; padding: 10px; font-weight: 700;">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                <a href="{{ route('inquilino.historial_pagos') }}" class="btn btn-light border" style="border-radius: 10px; padding: 10px;">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Pagos Estilo Premium -->
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-muted small fw-bold text-uppercase">Fecha</th>
                        <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Concepto</th>
                        <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Propiedad</th>
                        <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Referencia</th>
                        <th class="py-3 border-0 text-muted small fw-bold text-uppercase">Importe</th>
                        <th class="py-3 border-0 text-muted small fw-bold text-uppercase text-center">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold" style="color: #334155;">{{ \Carbon\Carbon::parse($pago->fecha_confirmacion_pago)->format('d M, Y') }}</span>
                                <span class="text-muted small">{{ \Carbon\Carbon::parse($pago->fecha_confirmacion_pago)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($pago->tipo_pago == 'alquiler')
                                    <div class="me-3 p-2 rounded-circle" style="background: #e0f2fe; color: #0369a1;"><i class="bi bi-house"></i></div>
                                @elseif($pago->tipo_pago == 'gasto')
                                    <div class="me-3 p-2 rounded-circle" style="background: #fef3c7; color: #b45309;"><i class="bi bi-lightning"></i></div>
                                @else
                                    <div class="me-3 p-2 rounded-circle" style="background: #fecaca; color: #b91c1c;"><i class="bi bi-tools"></i></div>
                                @endif
                                <span class="fw-semibold">{{ $pago->concepto_pago }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted small d-block">{{ $pago->titulo_propiedad }}</span>
                            <span class="text-muted" style="font-size: 0.75rem;">{{ $pago->calle_propiedad }}</span>
                        </td>
                        <td>
                            <code class="small px-2 py-1 bg-light rounded text-muted" style="border: 1px solid #e2e8f0;">{{ $pago->referencia_pago }}</code>
                        </td>
                        <td>
                            <span class="fw-bold" style="color: #1e293b; font-size: 1.1rem;">{{ number_format($pago->importe_pago, 2, ',', '.') }}€</span>
                        </td>
                        <td class="text-center">
                            @if($pago->factura_url)
                                <a href="{{ asset($pago->factura_url) }}" target="_blank" class="btn btn-sm btn-outline-info" style="border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            @else
                                <span class="badge bg-light text-muted border" style="font-weight: 500;">Generando...</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="https://illustrations.popsy.co/flat/empty-state.svg" alt="No hay pagos" style="height: 150px; opacity: 0.5;">
                            <p class="text-muted mt-3 fw-bold">No se han encontrado pagos con estos criterios</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación con Estilo -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $pagos->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f1f5f9;
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        background: #00adb5 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 196, 204, 0.3);
    }
    .form-control:focus, .form-select:focus {
        border-color: #00c4cc;
        box-shadow: 0 0 0 3px rgba(0, 196, 204, 0.1);
    }
</style>
@endsection
