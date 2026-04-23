@forelse ($alquileres as $alquiler)
<article class="tarjeta-propiedad-gestion">
    @php
        $bannerFoto = $alquiler->ruta_foto ? asset('public/img/' . $alquiler->ruta_foto) : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
    @endphp
    <div class="banner-propiedad" style="<?php echo 'background-image: url(\'' . e($bannerFoto) . '\'); background-size: cover; background-position: center;'; ?>">
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
            <span>
                Tienes <strong>{{ $alquiler->pago_atrasado }} meses</strong> de retraso.
            </span>
        </div>
        @endif

        @if ($alquiler->mostrarAlertaFin)
        <div class="alerta-fin-contrato {{ $alquiler->haExpirado ? 'estado-expirado' : '' }}">
            <i class="bi bi-clock-history"></i>
            @if ($alquiler->haExpirado)
            <span>El contrato ha expirado (hace <strong>{{ $alquiler->diasExpirado }} días</strong>). Tienes una semana para contactar al propietario y solucionar el inconveniente en el caso que quieras renovar el contrato.</span>
            @elseif ($alquiler->diasFinContrato === 0)
            <span class="contenedor-alerta-js">
                El contrato finaliza <strong>hoy</strong> (quedan <strong class="js-tiempo-restante" data-fecha-fin="{{ $alquiler->fecha_fin_alquiler }}">calculando...</strong>)
            </span>
            @elseif ($alquiler->diasFinContrato > 0)
            <span class="contenedor-alerta-js">
                El contrato finaliza en <strong>{{ $alquiler->diasFinContrato }} días</strong>
            </span>
            @else
            <span class="contenedor-alerta-js">
                El contrato finaliza en <strong>{{ $alquiler->diasFinContrato }} días</strong>
            </span>
            @endif
        </div>
        @endif

        <div class="acciones-gestion">
            <a href="{{ route('inquilino.ver_propiedad', $alquiler->id_propiedad) }}" class="btn-inquilino btn-secundario">Ver Detalles</a>
            @if ($alquiler->mostrarAlertaFin || $alquiler->estado_alquiler != 'activo')
            <a href="mailto:" class="btn-inquilino btn-secundario" style="color: var(--primario); border-color: var(--borde);"><i class="bi bi-envelope" style="margin-right: 5px;"></i> Contactar</a>
            @elseif(!empty($alquiler->cuota_pendiente_id))
            <form method="POST" action="{{ route('inquilino.pagar_cuota', $alquiler->cuota_pendiente_id) }}" style="display:inline; margin:0;">
                @csrf
                <button type="submit" class="btn-inquilino btn-primario">Pagar Recibo</button>
            </form>
            @else
            <button class="btn-inquilino btn-secundario" type="button" disabled>Sin recibos pendientes</button>
            @endif
        </div>
    </div>
</article>
@empty
<div class="estado-vacio-inquilino">
    <p>No se han encontrado alquileres activos con los filtros aplicados.</p>
</div>
@endforelse