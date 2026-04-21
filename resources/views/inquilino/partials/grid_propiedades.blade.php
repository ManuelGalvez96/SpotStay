@forelse ($alquileres as $alquiler)
<article class="tarjeta-propiedad-gestion">
    <div class="banner-propiedad" style="background-image: url('{{ $alquiler->ruta_foto ? asset('public/img/' . $alquiler->ruta_foto) : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80' }}'); background-size: cover; background-position: center;">
        <span class="badge-estado-inquilino">{{ ucfirst(str_replace('_', ' ', $alquiler->estado_alquiler)) }}</span>
    </div>
    <div class="info-propiedad-gestion">
        <h3>{{ $alquiler->titulo_propiedad }}</h3>
        <p class="ubicacion-gestion"><i class="bi bi-geo-alt"></i> {{ $alquiler->ciudad_propiedad }}, {{ $alquiler->calle_propiedad }} {{ $alquiler->numero_propiedad }}</p>

        <div class="meta-gestion">
            <div class="item-meta">
                <span class="label-meta">RENTA MENSUAL</span>
                <span class="valor-meta">{{ number_format($alquiler->precio_propiedad, 0, ',', '.') }} €</span>
            </div>
            <div class="item-meta">
                <span class="label-meta">FIN CONTRATO</span>
                <span class="valor-meta">{{ $alquiler->fecha_fin_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->format('d/m/Y') : 'Indefinido' }}</span>
            </div>
            <div class="item-meta">
                <span class="label-meta">INCIDENCIAS EN PROCESO</span>
                <span class="valor-meta">{{ $alquiler->total_incidencias_propiedad ?? 0 }}</span>
            </div>
        </div>

        @if(($alquiler->pago_atrasado ?? 0) > 0)
        <div class="alerta-pago-atrasado">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>El plazo del pago ha expirado, paga lo antes posible.</span>
        </div>
        @endif

        @php
        $mostrarAlertaFin = false;
        $diasFinContrato = null;
        if (!empty($alquiler->fecha_fin_alquiler)) {
        $hoy = \Carbon\Carbon::today();
        $fin = \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->startOfDay();
        // Solo mostramos alerta si el contrato aún no ha vencido y quedan <= 30 días
            if ($fin->gte($hoy)) {
            $diasFinContrato = (int) $hoy->diffInDays($fin); // siempre positivo
            $mostrarAlertaFin = $diasFinContrato <= 30;
                }
                }
                @endphp

                @if ($mostrarAlertaFin)
                <div class="alerta-fin-contrato">
                <i class="bi bi-clock-history"></i>
                <span>El contrato finaliza en <strong>{{ $diasFinContrato }} días</strong></span>
    </div>
    @endif

    <div class="acciones-gestion">
        <a href="{{ route('inquilino.ver_propiedad', $alquiler->id_propiedad) }}" class="btn-inquilino btn-secundario">Ver Detalles</a>
        @if ($mostrarAlertaFin || $alquiler->estado_alquiler != 'activo')
        <a href="mailto:" class="btn-inquilino btn-secundario" style="color: var(--primario); border-color: var(--borde);"><i class="bi bi-envelope" style="margin-right: 5px;"></i> Contactar</a>
        @else
        <button class="btn-inquilino btn-primario">Pagar Recibo</button>
        @endif
    </div>
    </div>
</article>
@empty
<div class="estado-vacio-inquilino">
    <p>No se han encontrado alquileres activos con los filtros aplicados.</p>
</div>
@endforelse