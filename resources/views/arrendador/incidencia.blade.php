@extends('layouts.arrendador')

@section('titulo', 'Incidencia #' . $incidencia->id_incidencia . ' - Arrendador')

@section('css')
<link rel="stylesheet" href="{{ asset('css/arrendador/incidencias.css') }}?v=1" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>{{ $incidencia->titulo_incidencia }}</h1>
            <p class="subtitulo">{{ $incidencia->titulo_propiedad }} · {{ $incidencia->direccion_propiedad }}, {{ $incidencia->ciudad_propiedad }}</p>
        </div>
        <div class="acciones-cabecera">
            <a class="btn-volver" href="{{ route('arrendador.incidencias', ['arrendador_id' => $arrendadorId]) }}">Volver a incidencias</a>
        </div>
    </header>

    @if(session('ok'))
    <div class="alerta ok">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
    <div class="alerta error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alerta error">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="incidencia-grid">
        <article class="card card-main">
            <div class="cabecera-card">
                <div>
                    <p class="kicker">Incidencia #{{ $incidencia->id_incidencia }}</p>
                    <h2>{{ $incidencia->titulo_incidencia }}</h2>
                </div>
                <div class="estado-stack">
                    <span class="estado estado-{{ str_replace('_', '-', $incidencia->estado_incidencia) }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
                    <span class="estado prioridad">Prioridad {{ ucfirst(strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia)) }}</span>
                </div>
            </div>

            <div class="datos-grid">
                <div>
                    <span class="dato-label">Reporta</span>
                    <span class="dato-valor">{{ $incidencia->nombre_reporta }}</span>
                </div>
                <div>
                    <span class="dato-label">Asignado a</span>
                    <span class="dato-valor">{{ $incidencia->nombre_asignado ?: 'Sin asignar' }}</span>
                </div>
                <div>
                    <span class="dato-label">Creada</span>
                    <span class="dato-valor">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="dato-label">Presupuesto</span>
                    <span class="dato-valor">{{ !is_null($incidencia->presupuesto_importe_incidencia) ? number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') . ' €' : 'Sin presupuesto' }}</span>
                </div>
            </div>

            <div class="bloque-descripcion">
                <span class="dato-label">Descripción</span>
                <p>{{ $incidencia->descripcion_incidencia }}</p>
            </div>

            @if(!is_null($incidencia->presupuesto_importe_incidencia))
            <div class="bloque-presupuesto">
                <h3>Presupuesto de reparación</h3>
                <p class="presupuesto-importe">{{ number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') }} €</p>
                <p>{{ $incidencia->detalle_presupuesto_incidencia ?: 'Sin detalle adicional.' }}</p>
            </div>
            @endif

            @if($accionActual === 'presupuesto')
            <div class="bloque-accion">
                <h3>Generar presupuesto de reparación</h3>
                <p>Introduce el coste estimado para enviarlo al gestor y continuar con la gestión.</p>
                <form method="POST" action="{{ route('arrendador.incidencias.presupuesto', ['id' => $incidencia->id_incidencia]) }}">
                    @csrf
                    <input type="number" step="0.01" min="0" name="importe" placeholder="Importe (EUR)" required>
                    <textarea name="detalle_presupuesto" required placeholder="Detalle del presupuesto"></textarea>
                    <button type="submit" class="btn-accion btn-primario">Confirmar presupuesto</button>
                </form>
            </div>
            @elseif($accionActual === 'esperando_decision')
            <div class="bloque-accion">
                <h3>Decidir quién paga</h3>
                <p>La incidencia está esperando tu decisión. Indica si la paga el arrendador o el inquilino para pasar al siguiente estado.</p>
                <form method="POST" action="{{ route('arrendador.incidencias.decision', ['id' => $incidencia->id_incidencia, 'arrendador_id' => $arrendadorId]) }}" class="form-decision">
                    @csrf
                    <button type="submit" name="responsable_pago" value="arrendador" class="btn-accion btn-primario">La pago yo</button>
                    <button type="submit" name="responsable_pago" value="inquilino" class="btn-accion btn-secundario">La paga el inquilino</button>
                </form>
            </div>
            @elseif($accionActual === 'esperando_pago')
            <div class="bloque-accion">
                <h3>Esperando pago</h3>
                <p>Responsable del pago: <strong>{{ ucfirst($incidencia->responsable_pago_incidencia ?: 'sin definir') }}</strong>.</p>
                @if($incidencia->responsable_pago_incidencia === 'arrendador')
                <form method="POST" action="{{ route('arrendador.incidencias.pagar', ['id' => $incidencia->id_incidencia, 'arrendador_id' => $arrendadorId]) }}" class="form-decision">
                    @csrf
                    <button type="submit" class="btn-accion btn-primario">Pagar</button>
                </form>
                @elseif($incidencia->responsable_pago_incidencia === 'inquilino')
                <p class="nota-pago">El inquilino es responsable del pago de esta incidencia.</p>
                @else
                <p class="nota-pago" style="background-color: rgba(255, 193, 7, 0.15); color: #856404; border: 1px solid #ffeeba; padding: 12px; border-radius: 8px;">El responsable del pago aún no ha sido definido.</p>
                @endif
            </div>
            @elseif($accionActual === 'solucionada')
            <div class="bloque-accion info">
                <h3>Incidencia solucionada</h3>
                <p>El presupuesto ya se pagó. El inquilino puede revisarla y marcarla como resuelta cuando confirme que está correcta.</p>
            </div>
            @elseif($accionActual === 'resuelta')
            <div class="bloque-accion info">
                <h3>Incidencia resuelta</h3>
                <p>El presupuesto ya se pagó y la incidencia está pendiente de cierre.</p>
            </div>
            @elseif($accionActual === 'cerrada')
            <div class="bloque-accion info">
                <h3>Incidencia cerrada</h3>
                <p>La incidencia quedó cerrada definitivamente.</p>
            </div>
            @else
            <div class="bloque-accion info">
                <h3>Seguimiento</h3>
                <p>La incidencia sigue su curso normal. Aquí podrás ver el presupuesto cuando el gestor lo emita.</p>
            </div>
            @endif
        </article>

        <aside class="columna-lateral">
            <article class="card">
                <h3>Historial</h3>
                <div class="timeline">
                    @forelse($historial as $item)
                    <div class="timeline-item">
                        <p>{{ $item->comentario_historial ?: 'Sin comentario' }}</p>
                        <span>{{ $item->nombre_usuario }} · {{ \Carbon\Carbon::parse($item->creado_historial)->format('d/m/Y H:i') }}</span>
                        @if($item->cambio_estado_historial)
                        <small>{{ ucfirst(str_replace('_', ' ', $item->cambio_estado_historial)) }}</small>
                        @endif
                    </div>
                    @empty
                    <p class="muted">Aún no hay historial.</p>
                    @endforelse
                </div>
            </article>

            <article class="card">
                <h3>Documentación</h3>
                <div class="docs-lista">
                    @forelse($documentos as $doc)
                    <div class="doc-item" style="border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 12px;">
                        <p style="font-weight: 600; margin-bottom: 6px;">{{ $doc->nombre_documento }}</p>

                        @php
                        $extension = strtolower(pathinfo($doc->url_documento, PATHINFO_EXTENSION));
                        $esImagen = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                        @endphp

                        @if($esImagen && $doc->url_documento && $doc->url_documento !== 'sin-archivo')
                        <div class="doc-preview-img" style="margin-top: 8px; margin-bottom: 12px;">
                            <a href="{{ $doc->url_documento }}" target="_blank" rel="noopener">
                                <img src="{{ $doc->url_documento }}" alt="{{ $doc->nombre_documento }}" style="max-width: 100%; max-height: 220px; border-radius: 8px; border: 1px solid #e2e8f0; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);" />
                            </a>
                        </div>
                        @endif

                        <div class="doc-meta" style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                            <span style="font-size: 12px; color: #718096; text-transform: uppercase;">{{ str_replace('_', ' ', $doc->tipo_documento) }}</span>
                            @if($doc->url_documento && $doc->url_documento !== 'sin-archivo')
                            <a href="{{ $doc->url_documento }}" target="_blank" rel="noopener" class="btn-abrir-doc" style="font-weight: 600; color: #0f4c81; text-decoration: none; font-size: 13px;">
                                <i class="bi bi-box-arrow-up-right"></i> Abrir
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="muted">Sin documentos vinculados.</p>
                    @endforelse
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection