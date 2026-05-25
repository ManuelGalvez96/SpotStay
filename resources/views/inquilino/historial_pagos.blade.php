@extends('layouts.miembro')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/inquilino/historial_pagos.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4 bg-light-blue-min-vh-100">
    <!-- Encabezado con efecto Glassmorphism -->
    <div class="mb-4 p-4 glass-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="mb-1 title-pagos">Centro de Pagos</h2>
                <p class="text-muted mb-0">Gestiona y descarga tus recibos de alquiler e incidencias</p>
            </div>
            <div class="col-auto">
                <div class="p-2 px-3 badge-transacciones">
                    <span class="text-transacciones">{{ $pagos->total() }} Transacciones</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Filtros Moderno -->
    <div class="mb-4 p-4 filtros-card">
        <form action="{{ route('inquilino.historial_pagos') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted">Desde</label>
                <input type="date" name="desde" class="form-control form-control-custom" value="{{ request('desde') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-custom" value="{{ request('hasta') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted">Tipo</label>
                <select name="tipo" class="form-select form-select-custom">
                    <option value="">Todos</option>
                    <option value="alquiler" {{ request('tipo') == 'alquiler' ? 'selected' : '' }}>Alquiler</option>
                    <option value="gasto" {{ request('tipo') == 'gasto' ? 'selected' : '' }}>Gasto</option>
                    <option value="incidencia" {{ request('tipo') == 'incidencia' ? 'selected' : '' }}>Incidencia</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted">Referencia</label>
                <input type="text" name="referencia" class="form-control form-control-custom" placeholder="TXN..." value="{{ request('referencia') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 btn-filtrar-custom">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                <a href="{{ route('inquilino.historial_pagos') }}" class="btn btn-light border btn-reset-custom">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Pagos Estilo Premium -->
    <div class="card border-0 shadow-sm tabla-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-f8fafc">
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
                                <span class="fw-bold text-334155">{{ \Carbon\Carbon::parse($pago->fecha_confirmacion_pago)->format('d M, Y') }}</span>
                                <span class="text-muted small">{{ \Carbon\Carbon::parse($pago->fecha_confirmacion_pago)->format('H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($pago->tipo_pago == 'alquiler')
                                    <div class="me-3 p-2 rounded-circle icon-alquiler"><i class="bi bi-house"></i></div>
                                @elseif($pago->tipo_pago == 'gasto')
                                    <div class="me-3 p-2 rounded-circle icon-gasto"><i class="bi bi-lightning"></i></div>
                                @else
                                    <div class="me-3 p-2 rounded-circle icon-incidencia"><i class="bi bi-tools"></i></div>
                                @endif
                                <span class="fw-semibold">{{ $pago->concepto_pago }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted small d-block">{{ $pago->titulo_propiedad }}</span>
                            <span class="text-muted font-075rem">{{ $pago->calle_propiedad }}</span>
                        </td>
                        <td>
                            <code class="small px-2 py-1 bg-light rounded text-muted border-e2e8f0">{{ $pago->referencia_pago }}</code>
                        </td>
                        <td>
                            <span class="fw-bold text-importe">{{ number_format($pago->importe_pago, 2, ',', '.') }}€</span>
                        </td>
                        <td class="text-center">
                            @if($pago->factura_url)
                                <a href="{{ asset($pago->factura_url) }}" target="_blank" class="btn btn-sm btn-outline-info btn-pdf">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            @else
                                <span class="badge bg-light text-muted border fw-500">Generando...</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <img src="https://illustrations.popsy.co/flat/empty-state.svg" alt="No hay pagos" class="empty-state-img">
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


@endsection
