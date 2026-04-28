@forelse ($alquileres as $alquiler)
<article class="tarjeta-propiedad-gestion">
    <div class="banner-propiedad">
        <img src="{{ data_get($alquiler, 'banner_foto_url', 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80') }}" alt="Imagen de {{ $alquiler->titulo_propiedad }}" class="banner-propiedad-imagen">
        <span class="badge-estado-inquilino">{{ ucfirst(str_replace('_', ' ', $alquiler->estado_alquiler)) }}</span>
    </div>
    <div class="info-propiedad-gestion">
        <h3>{{ $alquiler->titulo_propiedad }}</h3>
        <p class="propiedad-direccion"><i class="bi bi-geo-alt"></i> {{ $alquiler->direccion_propiedad }}</p>
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
            <span>El contrato ha expirado (hace <strong>{{ $alquiler->diasExpirado }} días</strong>).</span>
            @elseif (($alquiler->diasFinContrato ?? -1) === 0)
            <span class="contenedor-alerta-js">
                El contrato finaliza <strong>hoy</strong> (quedan <strong class="js-tiempo-restante" data-fecha-fin="{{ $alquiler->fecha_fin_alquiler }}">calculando...</strong>)
            </span>
            @else
            <span class="contenedor-alerta-js">
                El contrato finaliza en <strong>{{ $alquiler->diasFinContrato }} días</strong>
            </span>
            @endif
        </div>
        @endif

        <div class="acciones-gestion" style="display: flex; gap: 10px; margin-top: 15px;">
            <a href="{{ route('inquilino.ver_propiedad', $alquiler->id_propiedad) }}" class="btn-inquilino btn-secundario" style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center; text-decoration: none;">Ver Detalles</a>
            <form method="POST" action="{{ route('miembro.chat.iniciar', $alquiler->id_propiedad) }}" class="m-0" style="display: contents;">
                @csrf
                <button type="submit" class="btn-inquilino btn-primario" style="flex: 1; background-color: var(--primario); color: white; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border-radius: var(--radio); height: 44px; font-weight: 600;">
                    <i class="bi bi-chat-dots"></i> Contactar
                </button>
            </form>
        </div>
    </div>
</article>
@empty
<div class="estado-vacio-inquilino">
    <p>No se han encontrado alquileres activos con los filtros aplicados.</p>
</div>
@endforelse